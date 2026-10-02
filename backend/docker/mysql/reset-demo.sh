#!/usr/bin/env bash
set -euo pipefail

# Resetea la demo pública cada N horas (pensado para cron cada 12h):
# restaura la base de datos a la "semilla" creada con create-golden-seed.sh
# (borra todo lo generado por visitantes: mascotas, citas, facturas,
# historiales...) y regenera los slots de citas de los próximos 30 días
# desde la fecha actual.

SEED_FILE="${SEED_FILE:-/opt/vettrack-backups/golden-seed.sql.gz}"
COMPOSE_FILE="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)/docker-compose.yml"

if [ ! -f "$SEED_FILE" ]; then
  echo "$(date -Is) ERROR: no existe $SEED_FILE. Ejecuta primero create-golden-seed.sh." >&2
  exit 1
fi

echo "$(date -Is) Restaurando base de datos desde $SEED_FILE"
gunzip -c "$SEED_FILE" | docker compose -f "$COMPOSE_FILE" exec -T mysql sh -c \
  'exec mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"'

echo "$(date -Is) Regenerando slots de citas (próximos 30 días)"
docker compose -f "$COMPOSE_FILE" exec -T symfony php bin/console dbal:run-sql "DELETE FROM appointment_slots"
docker compose -f "$COMPOSE_FILE" exec -T symfony php bin/console app:appointments:generate-slots --days=30

echo "$(date -Is) Reset de demo completado"
