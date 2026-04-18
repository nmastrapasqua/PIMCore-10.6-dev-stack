# Pimcore 10.6 - Docker Compose Stack (Dev)

## Stack

| Servizio     | Immagine / Build          | Porta  | Ruolo                              |
|-------------|---------------------------|--------|------------------------------------|
| Nginx       | nginx:1.24-alpine         | 8480   | Reverse proxy                      |
| PHP-FPM     | php:8.1-fpm (custom)      | -      | Applicazione Pimcore               |
| Supervisord | php:8.1-fpm (custom)      | -      | Maintenance, messenger, cron jobs  |
| MySQL       | mysql:8.0                 | 33060  | Database                           |
| Redis       | redis:7-alpine            | 63790  | Cache e sessioni                   |

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
├── app/                          ← Progetto Pimcore (bind mount, editabile da VSCode)
│   ├── src/
│   │   └── Command/              ← Comandi Symfony custom (es. import, job schedulati)
│   ├── config/                   ← Configurazione Symfony/Pimcore
│   ├── public/                   ← Document root Nginx
│   ├── var/                      ← Cache, log, tmp
│   └── composer.json
├── docker/
│   ├── nginx/default.conf        ← Configurazione Nginx per Pimcore
│   ├── php/
│   │   ├── Dockerfile            ← Immagine PHP-FPM
│   │   └── php.ini               ← Tuning PHP
│   ├── supervisord/
│   │   ├── Dockerfile            ← Immagine worker (supervisor + cron)
│   │   ├── supervisord.conf      ← Configurazione supervisord
│   │   └── pimcore.conf          ← Job: maintenance, messenger, cron
│   └── cron/
│       └── pimcore-crontab       ← Job schedulati via cron
├── docker-compose.yml
├── setup.sh
└── .env                          ← UID/GID host
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

## Job schedulati (cron)

I job cron sono definiti in `docker/cron/pimcore-crontab`.
Per aggiungere un nuovo job, aggiungi una riga e rebuilda il container:

```bash
docker compose up -d --build supervisord
```

## Accesso

- **Frontend:** http://localhost:8480
- **Admin panel:** http://localhost:8480/admin
- **Credenziali:** admin / admin

## Comandi utili

```bash
# Log dei container
docker compose logs -f

# Stato dei processi supervisord
docker compose exec supervisord supervisorctl status

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
