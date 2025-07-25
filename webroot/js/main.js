
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

      console.log(button);

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
       *
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

                      function update(posts)
                      {
                          console.log(posts);
                      }
                  }
              )(document.getElementById(params.resultContainerId)),
              inputEl: document.getElementById(params.searchInputId),
          };
      }

      /**
       *
       */
      async function serverSearch(query)
      {
          try {
              const response = await fetch(encodeURI(`${params.searchRoute}?q=${query}`));

              if (!response.ok) {
                  throw new Error(`Ошибка HTTP: ${response.status}`);
              }

              resultContainer.update(await response.json());
          } catch (error) {
              console.error(error);
          }
      }

      observableQuery.value = '';
  }
