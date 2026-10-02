#!/usr/bin/env bash
set -euo pipefail

# Vuelca el estado actual de la base de datos como la "semilla" a la que
# vuelve la demo en cada reset (reset-demo.sh). Ejecutar manualmente cada
# vez que quieras actualizar ese estado base (p.ej. tras nuevas migraciones
# o tras ajustar los datos de la clínica/usuarios).

SEED_FILE="${SEED_FILE:-/opt/vettrack-backups/golden-seed.sql.gz}"
COMPOSE_FILE="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)/docker-compose.yml"

mkdir -p "$(dirname "$SEED_FILE")"
chmod 700 "$(dirname "$SEED_FILE")"

docker compose -f "$COMPOSE_FILE" exec -T mysql sh -c \
  'exec mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines --triggers "$MYSQL_DATABASE"' \
  | gzip > "$SEED_FILE"

chmod 600 "$SEED_FILE"
echo "Golden seed guardado en $SEED_FILE"
