# Front-end MyCare

Interface do MyCare desenvolvida com **Vue 2.7**, Vue Router, TypeScript e Vite. O front-end consome a API Laravel em `http://localhost:8000/api` por padrão.

Para a configuração completa da aplicação (Laravel, MySQL e front-end em Docker), consulte o [README principal](../README.md). Estas instruções cobrem o front-end isoladamente e o acesso à aplicação.

## Acessar a aplicação com Docker Compose

Na raiz do repositório, inicie os serviços:

```bash
docker compose up -d --build
```

Prepare o banco e crie o usuário inicial:

```bash
docker compose exec laravel.test php artisan migrate --seed
```

Abra <http://localhost:5174/>. Na tela de login, use o usuário criado pelo seeder:

- **E-mail:** `admin@mycare.com`
- **Senha:** `123456789`

Após entrar, acesse **Pacientes** no menu para listar, cadastrar, consultar, editar ou excluir pacientes. A API Laravel precisa estar disponível em `http://localhost:8000`.

## Executar o front-end localmente

Requisitos: Node.js 18 ou superior e npm. Primeiro inicie o Laravel e o MySQL conforme o [README principal](../README.md), aplique as migrations e crie o usuário:

```bash
docker compose up -d mysql laravel.test
docker compose exec laravel.test php artisan migrate --seed
```

Depois, em outro terminal:

```bash
cd front-vuejs
npm ci
npm run dev
```

O Vite disponibiliza a interface em <http://localhost:5173/>. Para usar outra URL de API, crie `front-vuejs/.env.local`:

```dotenv
VITE_API_BASE_URL=http://localhost:8000/api
```

Reinicie o servidor Vite depois de alterar essa variável.

## Comandos de desenvolvimento

```bash
npm run dev        # servidor local com hot reload
npm run type-check # verifica os tipos TypeScript e Vue
npm run build      # verifica os tipos e gera a versão de produção
npm run lint       # executa ESLint (com correção automática)
```

O resultado de produção é gerado em `dist/`.
