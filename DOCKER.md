# Docker development

## Requirements

Install and start Docker Desktop with Docker Compose support.

## Start the stack

From the repository root:

```sh
docker compose up --build
```

Open the client at <http://localhost:3000>. The Laravel API is available at <http://localhost:8080/api>.

The API runs migrations when it starts. Source directories are mounted into the containers, so changes to Laravel and React files are picked up during development.

## Stop the stack

```sh
docker compose down
```

MySQL data is kept in the `mysql_data` volume. To remove the database and start fresh:

```sh
docker compose down -v
```

The default development database credentials are defined in `docker-compose.yml` and are separate from the existing local `.env` files.
