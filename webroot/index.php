<?php

  /**
   * Страница демо-сайта
   */

  declare(strict_types=1);

  require_once '../vendor/autoload.php';
  require_once '../config.php';

  /**
   * В публичном интерфейсе подключаемся под обычным пользователем с минимальным для работы набором прав
   */
  $db = new \PDO(DB_DSN, NORMAL_USER_LOGIN, NORMAL_USER_PASSWORD);

  /**
   * Простейшая реализация обработки маршрутов, работает только с параметрами GET-запросов
   */
  startDemoSite(
      [
          [
              // Результат поиска
              'GET /search',
              function (array $params) use ($db): void {
                  header('Content-Type: application/json; charset=utf-8');
                  echo json_encode(
                      (new \Publications\Controller($db))
                          ->searchPostsAndComments($params['q'] ?? '')
                  );
              }
          ],
          [   // HTML-страница
              'GET /',
              function () use ($db): void {
                  echo (new \Publications\Controller($db))
                      ->getMainPage();
              }
          ]
      ]
  );

  /**
   * Обработчик маршрутов
   */
  function startDemoSite(array $routes): never
  {
      foreach ($routes as $route) {
          if (isMatchesRoute($route[0]) && is_callable($route[1])) {
              try {
                  $route[1](getRequestParams());
              } catch (\Exception $e) {
                  http_response_code($serverError = 500);
                  echo $e->getMessage();
              }

              exit;
          }
      };

      http_response_code($notFound = 404);
      exit;
  }

  /**
   * Сравнивает заданный и текущий маршруты
   */
  function isMatchesRoute(string $route): bool
  {
      [ $method, $path ] = explode(' ', $route);

      // Очень наивно, конечно
      return ($method === $_SERVER['REQUEST_METHOD']) and ($path === parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
  }

  /**
   * Возвращает массив параметров и значений из строки запроса
   */
  function getRequestParams(): array
  {
      parse_str($_SERVER['QUERY_STRING'] ?? '', $params);
      return $params;
  }
