COMPOSE=docker compose
APP_DIR=/var/www/symfony

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

reload: down up

symfony-bash:
	$(COMPOSE) exec symfony bash

symfony-cache-clear:
	$(COMPOSE) exec symfony php bin/console cache:clear

mysql-bash:
	$(COMPOSE) exec mysql bash

fix-permissions: fix-dirs fix-files fix-owner

fix-dirs:
	$(COMPOSE) exec -u root symfony bash -c "find $(APP_DIR) -type d -exec chmod 775 {} \;"

fix-files:
	$(COMPOSE) exec -u root symfony bash -c "find $(APP_DIR) -type f -exec chmod 644 {} \;"

fix-owner:
	$(COMPOSE) exec -u root symfony bash -c "chown -R guno:www-data $(APP_DIR)"

restore-db:
	@echo "🗑️  Restaurando base de datos en contenedor..."
	$(COMPOSE) exec symfony php bin/console doctrine:database:drop --force --if-exists -n || true
	$(COMPOSE) exec symfony php bin/console doctrine:database:create -n
	$(COMPOSE) exec symfony php bin/console doctrine:migrations:migrate -n
	@echo "✅ Base de datos restaurada!"

restore-db-fixtures: restore-db
	$(COMPOSE) exec symfony php bin/console doctrine:fixtures:load -n

migrate:
	$(COMPOSE) exec symfony php bin/console doctrine:migrations:migrate -n

crontab:
	$(COMPOSE) exec -it cron crontab -l

crontab-logs:
	$(COMPOSE) exec -it cron tail /var/log/cron.log


help:
	@echo "Comandos disponibles:"
	@echo "  make up                  # Subir contenedores"
	@echo "  make down                # Bajar contenedores"
	@echo "  make restore-db          # Restaurar DB completa"
	@echo "  make restore-db-fixtures # Restaurar DB + fixtures"
	@echo "  make migrate             # Solo migraciones"
	@echo "  make symfony-bash        # Entrar a contenedor Symfony"
	@echo "  make mysql-bash          # Entrar a MySQL"
	@echo "  make fix-permissions     # Arreglar permisos de archivos y carpetas"
	@echo "  make symfony-cache-clear # Limpiar caché de Symfony"
	@echo "  make crontab             # Ver crontab del contenedor de cron"
	@echo "  make crontab-logs        # Ver logs del contenedor de cron"
	@echo "  make help                # Esta ayuda"