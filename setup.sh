#!/bin/bash
set -e

# Rileva UID e GID dell'utente corrente
HOST_UID=$(id -u)
HOST_GID=$(id -g)

echo "============================================"
echo "  Pimcore 10.6 - Docker Setup (dev)"
echo "  Host user: $(whoami) (${HOST_UID}:${HOST_GID})"
echo "============================================"
echo ""

# Step 1: Build dei container
echo "[1/4] Build dei container..."
docker compose build

# Step 2: Crea il progetto Pimcore nella cartella locale ./app
if [ ! -f "./app/composer.json" ]; then
    echo ""
    echo "[2/4] Creazione progetto Pimcore in ./app ..."
    mkdir -p app
    docker compose run --rm --no-deps -u root php bash -c "\
        COMPOSER_MEMORY_LIMIT=-1 composer create-project pimcore/skeleton:v10.2.6 /tmp/pimcore --no-interaction \
        && cp -a /tmp/pimcore/. /var/www/html/ \
        && rm -rf /tmp/pimcore \
        && COMPOSER_MEMORY_LIMIT=-1 composer require pimcore/pimcore:~10.6.0 --no-interaction --working-dir=/var/www/html \
        && chown -R ${HOST_UID}:${HOST_GID} /var/www/html"
else
    echo ""
    echo "[2/4] Progetto Pimcore già presente in ./app, skip."
fi

# Step 3: Avvio dei container
echo ""
echo "[3/4] Avvio dei container..."
docker compose up -d

echo ""
echo "Attendo che MySQL sia pronto..."
until docker compose exec db mysqladmin ping -h localhost -u root -proot --silent 2>/dev/null; do
    printf "."
    sleep 2
done
# Attendo che MySQL accetti connessioni TCP reali
until docker compose exec db mysql -u pimcore -ppimcore -e "SELECT 1" pimcore >/dev/null 2>&1; do
    printf "."
    sleep 2
done
echo " OK!"

# Step 4: Installazione Pimcore (crea tabelle DB e utente admin)
echo ""
echo "[4/4] Installazione Pimcore..."
docker compose exec php ./vendor/bin/pimcore-install --no-interaction

# Fix permessi: l'installer crea alcune cartelle come root
docker compose exec -u root php chown -R www-data:www-data /var/www/html/var

echo ""
echo "============================================"
echo "  Installazione completata!"
echo "============================================"
echo ""
echo "  Frontend:  http://localhost:8480"
echo "  Admin:     http://localhost:8480/admin"
echo ""
echo "  Credenziali admin:"
echo "    Username: admin"
echo "    Password: admin"
echo ""
echo "  Il codice sorgente è in ./app"
echo "  Scrivi le tue classi in ./app/src/"
echo ""
echo "  IMPORTANTE: Cambia la password admin!"
echo "============================================"
