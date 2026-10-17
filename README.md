## MyCare

### Pré-requisitos

- Docker e Docker Compose
- Git

### 1. Clonar o repositório

```bash
git clone https://github.com/aderaldoneto/MyCare.git
cd MyCare
```

### 2. Configurar o backend Laravel

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

Configure as variáveis de ambiente e inicie os containers:

```bash
cd php-laravel
cp .env.example .env
docker compose up -d --build
```

Gere a chave da aplicação e execute as migrations do banco de dados:

```bash
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate
```

### 3. Configurar o frontend Vue.js

Abra um novo terminal na raiz do repositório e instale as dependências do frontend:

```bash
docker run --rm \
  --user "$(id -u):$(id -g)" \
  -e npm_config_cache=/tmp/.npm \
  -v "$PWD/front-vuejs:/app" \
  -w /app \
  node:18-alpine \
  npm ci
```

Inicie o servidor de desenvolvimento:

```bash
docker run --rm -it \
  --user "$(id -u):$(id -g)" \
  -e npm_config_cache=/tmp/.npm \
  -p 5174:5173 \
  -v "$PWD/front-vuejs:/app" \
  -w /app \
  node:18-alpine \
  npm run dev -- --host 0.0.0.0
```

### 4. Acessar a aplicação

- **Frontend (Vue.js):** http://localhost:5174/
- **Backend (Laravel):** http://localhost:8000/

Mantenha os containers do Docker e o servidor de desenvolvimento do frontend em execução enquanto utilizar a aplicação.

### 5. Encerrar a aplicação

No diretório `php-laravel`, execute:

```bash
docker compose down
```

Para encerrar os containers e também excluir o volume do banco de dados, apagando os dados armazenados nele, execute:

```bash
docker compose down -v
```

**Atenção:** o último comando remove permanentemente os dados do banco armazenados nesse volume.