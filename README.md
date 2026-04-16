# Pimcore 10.6 - Docker Compose Stack (Dev)

## Stack

| Servizio     | Immagine / Build       | Porta  |
|-------------|------------------------|--------|
| Nginx       | nginx:1.24-alpine      | 8480   |
| PHP-FPM     | php:8.1-fpm (custom)   | 9000   |
| MySQL       | mysql:8.0              | 33060  |
| Redis       | redis:7-alpine         | 63790  |
| Supervisord | (stesso image PHP)     | -      |

## Requisiti

- Docker >= 20.10
- Docker Compose >= 2.0

## Installazione

```bash
chmod +x setup.sh
./setup.sh
```

## Struttura progetto

```
├── app/                    ← Progetto Pimcore (bind mount, editabile da VSCode)
│   ├── src/                ← Le tue classi PHP vanno qui
│   │   └── Command/        ← Comandi Symfony custom (es. import)
│   ├── config/             ← Configurazione Symfony/Pimcore
│   ├── public/             ← Document root Nginx
│   ├── var/                ← Cache, log, tmp
│   └── composer.json
├── docker/
│   ├── nginx/default.conf
│   ├── php/
│   │   ├── Dockerfile
│   │   └── php.ini
│   └── supervisord/pimcore.conf
├── docker-compose.yml
└── setup.sh
```

## Sviluppo

Il codice in `./app` è montato direttamente nei container.
Ogni modifica fatta in VSCode è immediatamente visibile in Pimcore.

```bash
# Console Pimcore
docker compose exec php bin/console

# Eseguire un comando custom
docker compose exec php bin/console app:mio-comando

# Pulire la cache
docker compose exec php bin/console cache:clear
```

## Accesso

- **Frontend:** http://localhost:8480
- **Admin panel:** http://localhost:8480/admin
- **Credenziali:** admin / admin

## Comandi utili

```bash
# Log dei container
docker compose logs -f

# Accesso al container PHP
docker compose exec php bash

# Accesso a MySQL
docker compose exec db mysql -u pimcore -ppimcore pimcore

# Stop / Start
docker compose down
docker compose up -d

# Reset completo (CANCELLA TUTTI I DATI)
docker compose down -v
rm -rf app
./setup.sh
```
