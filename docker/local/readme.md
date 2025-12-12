## Сборка/запуск docker-окружения:

- cd .docker
- создаем .env из .env.example
    - замените COMPOSE_PROJECT_NAME(префикс названий контейнеров), если проектов несколько
    - замените UID/GID, если необходимо
    - замените др.данные, если необходимо
- остановите др.контейнеры, если необходимо из-за конфликтов названий контейнеров, портов и др. (скрипт _docker_stop_all.sh)
- выполните ```docker-compose up -d``` или скрипт _docker_start.sh

## Установка проекта:

### бэкенд
- установите/настройте проект, предварительно изменив название php-контейнера('app_php') на релевантное
    - ```docker exec -it -u "$(id -u):$(id -g)" <префикс>_php``` или скрипт _terminal_php.sh
    - composer install
    - cp .env.example .env
    - php artisan key:generate
    - php artisan migrate
    - php artisan passport:install
- настройте nginx, если необходимо
    - .docker/local/nginx/local.conf
- для использования Планировщика/Обработчика очереди раскомментируйте соответствующие сервисы в docker-compose и запустите пересборку (скрипт _docker_start_build.sh)
    - для разработки. Нежелательно использовать на бою.
- в docker-compose присутствуют доп.сервисы. Раскомментить при необходимости.


## Импорт/экспорт данных БД
- импорт
    - делаем дамп pg_dump
    - кладем дамп([sqlDumpName]) в docker/local/data/postgres/_data
    - удаляем все таблицы в схеме
    - docker exec -it pinscher_postgres sh
    - ```psql -U postgres postgres < /var/lib/postgresql/data/_data/dump.sql```
    - переносим файл
- экспорт
    - docker exec -it vko-partners-mysql sh
    - ```pg-dump -U postgres postgres > /var/lib/postgresql/data/_data/dump.sql```
    - переносим файл
