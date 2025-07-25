
  /**
   * Точка входа
   */
  function init(params)
  {
      const {
          button,
          inputEl,
          resultContainer
      } = initComponents();

      /**
       * Наблюдаемый объект - строка запроса
       */
      const observableQuery = (function (params) {
          const { onChange } = params;

          let val = '';

          return {
              get value() {
                  return val;
              },

              set value(newValue) {
                  val = String(newValue).trim();
                  onChange(val);
              }
          };
      })({
          // Обработчик события изменения строки поиска
          onChange(value) {
              if (value.length < params.minLength) {
                  button.disable();
              } else {
                  button.enable();
              }
          }
      });

      // Источник данных
      inputEl.addEventListener('input', (e) => observableQuery.value = e.target.value);

      // Запуск поиска
      button.on('click', () => serverSearch(observableQuery.value));

      /**
       * Возвращает объект с ключами - объектными обертками над DOM-элементами,
       * с которыми удобней работать.
       */
      function initComponents()
      {
          return {
              button: (
                  function (el) {
                      const wrapper = Object.freeze({
                          enable,
                          disable,
                          on: el.addEventListener.bind(el)
                      });

                      return wrapper;

                      function enable()
                      {
                          el.removeAttribute('disabled');
                          return wrapper;
                      }

                      function disable()
                      {
                          el.setAttribute('disabled', '');
                          return wrapper;
                      }
                  }
              )(document.getElementById(params.searchButtonId)),
              resultContainer: (
                  function (el) {
                      const wrapper = Object.freeze({
                          update
                      });

                      return wrapper;

                      function update(posts, query)
                      {
                          el.innerHTML =
                              posts.length ?
                                  posts
                                      .map(post =>
                                          `<div class="result-item">
                                              <h3 class="result-post-title">${escapeHtml(post.postTitle)}</h3>` +
                                              (post.comments
                                                  .map(comment =>
                                                      `<h4 class="result-comment-title">${markQuery(query, escapeHtml(comment.name))}</h4>
                                                       <p class="result-comment-body">${markQuery(query, escapeHtml(comment.body))}</p>
                                                       <div class="result-comment-meta">${escapeHtml(comment.email)}</div>`
                                                  )
                                                  .join('')) +
                                        '</div>'
                                      ).join('')
                                  : 'Ничего не найдено';
                      }

                      function escapeHtml(text)
                      {
                          const map = {
                            '&': '&amp;',
                            '<': '&lt;',
                            '>': '&gt;',
                            '"': '&quot;',
                            "'": '&#039;'
                          };

                          return text.replace(/[&<>"']/g, (match) => map[match]);
                      }

                      function markQuery(query, text)
                      {
                          return text.replace(
                              new RegExp(query, 'gi'),
                              match => `<span class="highlight">${match}</span>`
                          );
                      }
                  }
              )(document.getElementById(params.resultsContainerId)),
              inputEl: document.getElementById(params.searchInputId),
          };
      }

      /**
       * Выполняет запрос на сервер и обрабатывает результат
       */
      async function serverSearch(query)
      {
          try {
              const response = await fetch(encodeURI(`${params.searchRoute}?q=${query}`));

              if (!response.ok) {
                  throw new Error(`Ошибка HTTP: ${response.status}`);
              }

              resultContainer.update(await response.json(), query);
          } catch (error) {
              console.error(error);
          }
      }

      // Начальное значение, кнопка неактивна
      observableQuery.value = '';
  }
