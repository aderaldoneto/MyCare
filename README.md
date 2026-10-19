# MyCare

Aplicação de cadastro e gerenciamento de pacientes, com frontend Vue.js e backend Laravel.

## Pré-requisitos

- Git
- Docker com Docker Compose v2.17 ou superior

## Iniciar pela primeira vez

```bash
git clone https://github.com/aderaldoneto/MyCare.git
cd MyCare
./start.sh
```

O script prepara o `.env`, instala as dependências PHP na primeira execução, gera a chave do Laravel, inicia os containers, aguarda o MySQL e o Redis ficarem prontos e aplica as migrations e seeders. O frontend instala suas dependências automaticamente.

## Acessar

- Frontend: http://localhost:5174/
- Backend: http://localhost:8000/

Usuário de demonstração:

- E-mail: `aderaldo@mycare.com`
- Senha: `123456789`

## Comandos úteis

Execute na raiz do repositório:

```bash
docker compose up -d       # iniciar os serviços depois da primeira configuração
docker compose logs -f     # acompanhar os logs
docker compose down        # parar os serviços, preservando os dados
```

Para apagar também os dados do banco e demais volumes:

```bash
docker compose down -v
```

Esse último comando remove permanentemente os dados locais do projeto.
