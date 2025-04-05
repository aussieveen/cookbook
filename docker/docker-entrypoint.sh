#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
  set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
    composer install --prefer-dist --no-progress --no-suggest -o --no-interaction --ignore-platform-reqs
    chmod -R 777 var

#    ./bin/console assets:install
    echo "Waiting for db to be ready..."
      until ./bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
        sleep 1
      done
        ./bin/console doctrine:migrations:migrate --no-interaction || echo "Warning: failed to run schema migration"
fi

# Define the directory path
DIR="public/uploads/images"

# Check if the directory exists
if [ ! -d "$DIR" ]; then
    mkdir -p "$DIR"
    chmod 777 "$DIR"
    echo "Directory '$DIR' created with permissions 777."
else
    echo "Directory '$DIR' already exists."
fi

service nginx start

exec docker-php-entrypoint "$@"
