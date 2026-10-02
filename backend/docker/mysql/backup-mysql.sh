#!/usr/bin/env bash
set -euo pipefail

# Backup diario de la base de datos de VetTrack.
# Usa las credenciales ya presentes dentro del propio contenedor MySQL,
# así no hace falta leer secretos del host.

BACKUP_DIR="${BACKUP_DIR:-/opt/vettrack-backups}"
COMPOSE_FILE="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)/docker-compose.yml"

mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"

TIMESTAMP=$(date +%Y%m%d-%H%M%S)
FILE="$BACKUP_DIR/guno-${TIMESTAMP}.sql.gz"

docker compose -f "$COMPOSE_FILE" exec -T mysql sh -c \
  'exec mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines --triggers "$MYSQL_DATABASE"' \
  | gzip > "$FILE"

chmod 600 "$FILE"

# Retención: conservar solo los últimos 14 días
find "$BACKUP_DIR" -name "guno-*.sql.gz" -mtime +14 -delete
