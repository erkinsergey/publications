<?php

  /**
   * Контроллер маршрутов главной страницы.
   * Для простоты контроллер выполняет много функций,
   * в реальном проекте он должен делегировать задачи разным слоям приложения.
   */

  declare(strict_types=1);

  namespace Publications;

  final class Controller
  {
      /**
       * Задание приватного поля коннекта к базе данных
       */
      public function __construct(private \PDO $db)
      {
      }

      /**
       * Возвращает текст - контент главной страницы
       */
      public function getMainPage(): string
      {
          // Подстановка в шаблон данных
          return str_replace(
              [
                  '{{SEARCH_ROUTE}}',
                  '{{MIN_LENGTH}}'
              ],
              [
                  '/search',
                  3 // Лучше прописать к конфиге
              ],
              $this->getMainPageTemplate()
          );
      }

      /**
       * Для простоты шаблон в виде строки возвращается методом.
       * На деле лучше пользоваться шаблонизаторами типа Twig.
       */
      private function getMainPageTemplate(): string
      {
          return <<< TEMPLATE
              <!DOCTYPE html>
              <html lang="ru">
                  <head>
                      <meta charset="UTF-8">
                      <meta name="viewport" content="width=device-width, initial-scale=1.0">
                      <link rel="stylesheet" href="css/style.css">
                      <title>Поиск</title>
                  </head>
                  <body>
                      <div class="search-container">
                          <input
                              id="search_input"
                              type="search"
                              class="search-input"
                              placeholder="Введите запрос..."
                              minlength={{MIN_LENGTH}}
                              required
                          >
                          <button disabled id="search_btn" class="search-button">Найти</button>
                      </div>
                      <div class="search-results">
                          <h2 class="results-title">Результаты поиска</h2>
                          <div id="results_container" class="results-container"></div>
                      </div>
                      <script src="js/main.js"></script>
                      <script>
                          const params = {
                              searchRoute: '{{SEARCH_ROUTE}}',
                              minLength: {{MIN_LENGTH}},
                              searchInputId: 'search_input',
                              searchButtonId: 'search_btn',
                              resultsContainerId: 'results_container'
                          };
                          document.addEventListener('DOMContentLoaded', () => init(params));
                      </script>
                  </body>
              </html>
          TEMPLATE;
      }

      /**
       * Работу с базой данных лучше делегировать другим компонентам, например, репозиториям.
       */
      public function searchPostsAndComments(string $query): array
      {
          // Подготовка для подстановки в LIKE-запрос
          $preparedQuery = str_replace(['%', '_'], '\\', trim($query));

          // Некая оптимизация
          if ('' === $preparedQuery) {
              return [];
          }

          /**
           * Запрос выдает список результатов вида
           * "post title 1" => "[{comment 1}, {comment 2}, ...]",
           * "post title 2" => "[{comment 1}, {comment 2}, ...]",
           * ...
           * где comments является JSONB-строкой
           */
          $preparedStatement = $this->db->prepare(
              'SELECT POST1."title" AS "post_title"
                      , json_agg(
                        jsonb_build_object(
                            \'name\', COMM1."name",
                            \'body\', COMM1."body",
                            \'email\', COMM1."email"
                        )
                      ) AS "comments"
                 FROM "posts" AS POST1
                 JOIN "comments" AS COMM1 ON POST1.id = COMM1.post_id
                -- ищем как в имени, так и в теле комментария
                WHERE CONCAT(COMM1."name", \' \', COMM1."body") ILIKE ?
                GROUP BY POST1."title"'
          );

          // В случае ошибки бросит исключение
          $preparedStatement->execute(["%{$preparedQuery}%"]);

          /**
           * Выбирать все записи сразу при больших данных может быть затратно по памяти!
           * Лучше разбивать на страницы с помощью LIMIT и OFFSET и, конечно, сортировки.
           */
          return $preparedStatement->fetchAll(
              \PDO::FETCH_FUNC,
              // Требуется преобразование данных из строк JSON в массивы PHP
              fn (string $postTitle, string $comments) => ([
                  'postTitle' => $postTitle,
                  'comments' => json_decode($comments, $assoc = true)
              ])
          );
      }
  }
