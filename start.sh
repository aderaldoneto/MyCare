#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

if ! command -v docker >/dev/null 2>&1; then
    echo "Erro: instale o Docker antes de iniciar o MyCare." >&2
    exit 1
fi

if ! docker compose version >/dev/null 2>&1; then
    echo "Erro: este projeto precisa do Docker Compose v2.17 ou superior." >&2
    exit 1
fi

if [ ! -f php-laravel/.env ]; then
    cp php-laravel/.env.example php-laravel/.env
fi

set_env() {
    local key="$1"
    local value="$2"

    if grep -q "^${key}=" php-laravel/.env; then
        sed -i "s/^${key}=.*/${key}=${value}/" php-laravel/.env
    else
        printf '\n%s=%s\n' "$key" "$value" >> php-laravel/.env
    fi
}

set_env WWWUSER "$(id -u)"
set_env WWWGROUP "$(id -g)"

if [ ! -f php-laravel/vendor/autoload.php ]; then
    echo "Instalando dependências do Laravel..."
    docker run --rm \
        -v "$PWD/php-laravel:/app" \
        -w /app \
        php:8.2-cli \
        bash -lc '
            apt-get update -qq &&
            DEBIAN_FRONTEND=noninteractive apt-get install -y -qq git unzip curl libcurl4-openssl-dev libzip-dev libonig-dev libxml2-dev >/dev/null &&
            docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring xml curl zip &&
            curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php &&
            php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer &&
            composer install --no-interaction
        '
fi

if grep -q '^APP_KEY=$' php-laravel/.env; then
    docker compose run --rm --no-deps laravel.test php artisan key:generate --force
fi

docker compose up -d --build --wait
docker compose exec laravel.test php artisan migrate --force
docker compose exec laravel.test php artisan db:seed --force

echo "MyCare está no ar: http://localhost:5174/"
