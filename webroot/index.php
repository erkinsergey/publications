<?php

  /**
   * Страница демо-сайта
   */

  declare(strict_types=1);

  require '../vendor/autoload.php';

  /**
   * Начать маршрутизацию
   */
  startDemoSite(
      [
          [
              // Результат поиска
              'GET /search',
              function (array $params) {
                  print_r($params);
              }
          ],
          [   // HTML-страница
              'GET /',
              function (array $params) {
                  print_r($params);
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
              $route[1](getRequestParams());
              exit;
          }
      };

      http_response_code(404);
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
