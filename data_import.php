<?php

  /**
   * Загрузка данных из внешнего источника в базу данных.
   * Использует учетную запись администратора базы данных.
   */

  declare(strict_types=1);

  require 'vendor/autoload.php';

  const DB_DSN = 'pgsql:dbname=publications;host=localhost;port=5433';
  const ADMIN_LOGIN = 'pub_admin';
  const ADMIN_PASSWORD = 'adm4321';

  const POSTS_URL = 'https://jsonplaceholder.typicode.com/posts';
  const COMMENTS_URL = 'https://jsonplaceholder.typicode.com/comments';


  try {
      // Слить два отдельных списка в один - посты с комментариями
      $postsWithComments = combinePostsWithComments(
          getRecordsFromJSONFile(POSTS_URL),
          getRecordsFromJSONFile(COMMENTS_URL)
      );

      $db = new \PDO(DB_DSN, ADMIN_LOGIN, ADMIN_PASSWORD);

      $options = [
          'db' => $db,
          'importPostQuery' => $db->prepare('
              INSERT INTO posts (title, body)
                   VALUES (:title, :body)
                RETURNING id
          '),
          'importCommentQuery' => $db->prepare('
              INSERT INTO comments (post_id, name, email, body)
                   VALUES (:postId, :name, :email, :body)
          ')
      ];

      [ $postsCount, $commentsCount ] = importPostsWithComments($postsWithComments, $options);

      echo "Загружен(о,ы) {$postsCount} пост(а,ов) и {$commentsCount} комментарий(я,ев)", PHP_EOL;
  } catch(\Exception $e) {
      echo "Ошибка: " . $e->getMessage(), PHP_EOL;
  }


  /**
   * Загружает JSON-файл из сети и возвращает список его записей
   */
  function getRecordsFromJSONFile(string $url): array
  {
      return fromJSON(
          getFileContents($url)
      );
  }

  /**
   * Загружает файл по ссылке, возвращает его текстовое содержание
   */
  function getFileContents(string $url): string
  {
      $handle = curl_init();

      $options = [
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
      ];

      curl_setopt_array($handle, $options);

      $result = curl_exec($handle);
      $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

      curl_close($handle);

      if ($result === false) {
          throw new \Exception('cURL error: ' . curl_error($handle));
      }

      if ($httpCode !== 200) {
          throw new \Exception("HTTP request failed with code {$httpCode}");
      }

      return (string) $result;
  }

  /**
   * Преобразует входную JSON-строку в ассоциативный массив
   */
  function fromJSON(string $json): array
  {
      $result = json_decode($json, $assoc = true);

      if (!is_array($result)) {
          throw new \Exception('fromJSON: argument is not a valid JSON string');
      }

      return $result;
  }

  /**
   * Возвращает один список - посты с комментариями вида
   * [
   *    [
   *       'title' => 'post title',
   *       'body' => 'post body',
   *       'comments' => [
   *          [
   *             'name' => 'comment name',
   *             'email'  => 'email',
   *             'body' => 'comment body'
   *          ],
   *          [
   *             'name' => 'comment name',
   *             'email'  => 'email',
   *             'body' => 'comment body'
   *          ],
   *          ...
   *       ]
   *    ],
   *    [],
   *    ...
   * ]
   */
  function combinePostsWithComments(array $posts, array $comments): array
  {
      /**
       * Преобразует плоский список постов в ассоциативный вида
       * [
       *    оригинальный_идентификатор_1  => пост_1,
       *    оригинальный_идентификатор_2  => пост_2,
       * ]
       *
       * для удобства дальнейшего поиска
       */
      $postsByOriginKeys = array_reduce(
          $posts,
          fn (array $result, array $post) => $result + [ $post['id']  => [ 'title' => $post['title'], 'body' => $post['body']]],
          []
      );

      /**
       * Возвращает список значений ассоциативного массива без оригинальных идентификаторов
       */
      return array_values(
          /**
           *  Возвращает список постов с комментариями, соединенные по своим оригинальным идентификаторам
           */
          array_reduce(
              $comments,
              function (array $result, array $comment) use ($postsByOriginKeys): array {
                  $postId = $comment['postId'];

                  $newComment = [
                      'name' => $comment['name'],
                      'body' => $comment['body'],
                      'email' => $comment['email']
                  ];

                  if (!array_key_exists($postId, $result)) {
                      $result[$postId] = [
                          'title' => $postsByOriginKeys[$postId]['title'],
                          'body' => $postsByOriginKeys[$postId]['body'],
                          'comments' => [ $newComment ]
                      ];
                  } else {
                      $result[$postId]['comments'][] = $newComment;
                  }

                  return $result;
              },
              []
          )
      );
  }

  /**
   * Импортирует посты и комментарии в базу данных
   */
  function importPostsWithComments(array $postsWithComments, $options): array
  {
      [ 'db' => $db ] = $options;

      $db->beginTransaction();

      try {
          $result =
              array_reduce(
                  $postsWithComments,
                  function (array $result, array $post) use ($options): array {
                      importPost($post, $options);

                      return [
                          $result[0] + 1,
                          $result[1] + count($post['comments'])
                      ];
                  },
                  [0, 0]
              );

          $db->commit();
      } catch (\PDOException $e) {
          $db->rollBack();

          throw new \Exception($e->getMessage());
      }

      return $result;
  }

  /**
   * Импортирует пост и его комментарии в базу
   */
  function importPost(array $post, array $options): void
  {
      [
          'importPostQuery' => $importPostQuery,
          'importCommentQuery' => $importCommentQuery
      ] = $options;

      $importPostQuery->execute([ 'title' => $post['title'], 'body' => $post['body']]);

      $newPostId = $importPostQuery->fetchColumn();

      array_walk(
          $post['comments'],
          fn (array $comment): bool => $importCommentQuery->execute([ 'postId' => $newPostId, ...$comment ])
      );
  }
