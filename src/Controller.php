<?php

  /**
   *
   */

  declare(strict_types=1);

  namespace Publications;

  final class Controller
  {
      /**
       * Возвращает текст - контент главной страницы
       */
      public function getMainPage(): string
      {
          return str_replace(
              [
              ],
              [
              ],
              $this->getMainPageTemplate()
          );
      }

      /**
       *
       */
      private function getMainPageTemplate(): string
      {
          return <<< TEMPLATE
              <!DOCTYPE html>
              <html lang="ru">
                  <head>
                      <meta charset="UTF-8">
                      <meta name="viewport" content="width=device-width, initial-scale=1.0">
                      <title>Поиск</title>
                      <style>
                          body {
                              font-family: Arial, sans-serif;
                              display: flex;
                              justify-content: center;
                              align-items: start;
                              height: 100vh;
                              margin: 0;
                              background-color: #f4f4f9;
                          }
                          .search-container {
                              display: flex;
                              box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                              border-radius: 24px;
                              overflow: hidden;
                              margin-top: 2em;
                          }
                          .search-input {
                              padding: 12px 16px;
                              border: 1px solid #ddd;
                              border-right: none;
                              border-radius: 24px 0 0 24px;
                              width: 300px;
                              font-size: 16px;
                              outline: none;
                          }
                          .search-button {
                              padding: 12px 20px;
                              background-color: #4285f4;
                              color: white;
                              border: none;
                              border-radius: 0 24px 24px 0;
                              cursor: pointer;
                              font-size: 16px;
                              transition: background-color 0.3s;
                          }
                          .search-button:hover {
                              background-color: #3367d6;
                          }
                      </style>
                  </head>
                  <body>
                      <div class="search-container">
                          <input
                              type="search"
                              class="search-input"
                              placeholder="Введите запрос..."
                              minlength=3
                              required
                          >
                          <button class="search-button">Найти</button>
                      </div>
                  </body>
              </html>
          TEMPLATE;
      }
  }
