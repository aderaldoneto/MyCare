# MyCare

## Pré-requisitos

- Docker e Docker Compose
- Git

## 1. Clonar o repositório

```bash
git clone https://github.com/aderaldoneto/MyCare.git
cd MyCare
```

## 2. Configurar o backend Laravel

Instale as dependências do PHP utilizando Docker:

```bash
docker run --rm \
  -v "$PWD/php-laravel:/app" \
  -w /app \
  php:8.2-cli \
  bash -lc '
    apt-get update -qq &&
    apt-get install -y -qq git unzip curl libcurl4-openssl-dev libzip-dev libonig-dev libxml2-dev >/dev/null &&
    docker-php-ext-install -j$(nproc) pdo_mysql mbstring xml curl zip &&
    curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php &&
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer &&
    composer install
  '
```

Configure as variáveis de ambiente:

```bash
cd php-laravel
cp .env.example .env
```

Volte para a raiz do repositório:

```bash
cd ..
```

## 3. Configurar a aplicação

Gere a chave da aplicação Laravel:

```bash
cd php-laravel
docker compose run --rm laravel.test php artisan key:generate
cd ..
```

## 4. Iniciar os serviços

Na raiz do repositório, execute:

```bash
docker compose up -d --build
```

Esse comando inicia o backend Laravel, o banco de dados MySQL e o frontend Vue.js.

Na primeira inicialização, o container do frontend instala automaticamente suas dependências com `npm ci`.

Migrations do banco de dados:

```bash
docker compose exec laravel.test php artisan migrate
```

## 5. Acessar a aplicação

- **Frontend (Vue.js):** http://localhost:5174/
- **Backend (Laravel):** http://localhost:8000/

Mantenha os serviços em execução enquanto utilizar a aplicação.

## 6. Encerrar a aplicação

Na raiz do repositório, execute:

```bash
docker compose down
```

Esse comando encerra os containers, mas preserva os dados armazenados no volume do banco de dados.

Para encerrar os containers e também excluir os volumes, execute:

```bash
docker compose down -v
```

**Atenção:** esse último comando remove permanentemente os dados armazenados nos volumes do projeto, incluindo os dados do banco de dados MySQL.
