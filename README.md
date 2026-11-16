# Coding Interview

Symfony 7.4 / Doctrine ORM 3 project with coding exercises. Task descriptions are in [docs/](docs/README.md).

## Requirements

- Docker

The image provides PHP 8.5, Composer 2 and all extensions Symfony 7.4 needs (`intl`, `zip`, `pdo_sqlite`, `pdo_mysql`, `pdo_pgsql`, OPcache).

## Run with Docker Compose

`compose.yaml` builds the image, mounts the repository into `/app` and starts the web server on http://localhost:8000.

Compose runs the container as `UID:GID` and uses `1000:1000` when they are not set. If your user has different IDs, export them first so the files you create belong to you:

```bash
export UID GID=$(id -g)
```

### First-time setup

```bash
docker compose build                                         # build the image
```

The repository mount hides the `vendor/` directory that was installed in the image, so run `composer install` once on the host copy.

### Daily use

```bash
docker compose up -d                  # start the web server in the background
docker compose logs -f app            # follow the server output
docker compose exec coding-interview bash          # open a shell in the running container
docker compose exec coding-interview bin/console about   # run a single command
docker compose down                   # stop and remove the container
```

## Run with plain Docker

### Build the image

```bash
docker build -t coding-interview:php8.5 .
```

Rebuild only when the `Dockerfile` changes. Code and Composer package changes do not need a rebuild.

### Run the container

Run all commands from the repository root. The repo is mounted into `/app`, so files are shared both ways between your machine and the container.

```bash
docker run --rm -it \
  -v "$PWD":/app \
  -u "$(id -u):$(id -g)" \
  -e COMPOSER_HOME=/tmp/composer \
  -p 8000:8000 \
  coding-interview:php8.5 bash
```

| Flag | Purpose |
| --- | --- |
| `-v "$PWD":/app` | Shares the repository with the container (two-way sync). |
| `-u "$(id -u):$(id -g)"` | Files created in the container are owned by your user, not root. |
| `-e COMPOSER_HOME=/tmp/composer` | Gives Composer a writable cache folder when not running as root. |
| `-p 8000:8000` | Makes the app reachable at http://localhost:8000. |

#### First-time setup (inside the container)

```bash
composer install                       # installs vendor/ into the shared repo
```

#### Start the web server

```bash
php -S 0.0.0.0:8000 -t public
```
