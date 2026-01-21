# Colores
COLOR_RESET=\033[0m
COLOR_DEV_BG=\033[0;37;44m
COLOR_PRO_BG=\033[0;30;42m

# Mensajes coloreados
MSG_DEV=$(COLOR_DEV_BG)DEV$(COLOR_RESET)\n
MSG_PRO=$(COLOR_PRO_BG)PRO$(COLOR_RESET)\n

COMPOSE=docker compose
APP_DIR=./app

reload: down up
reload-pro: down-pro up-pro

up:
	@echo "$(MSG_DEV)"
	$(COMPOSE) --profile dev up -d

up-pro:
	@echo "$(MSG_PRO)"
	$(COMPOSE) --profile pro up -d --build

down:
	@echo "$(MSG_DEV)"
	$(COMPOSE) --profile dev down

down-pro:
	@echo "$(MSG_PRO)"
	$(COMPOSE) --profile pro down

angular-bash:
	$(COMPOSE) exec angular bash

angular-logs:
	$(COMPOSE) logs -f angular

fix-permissions: fix-dirs fix-files fix-owner

fix-dirs:
	sudo find $(APP_DIR) -type d -exec chmod 775 {} \;

fix-files:
	sudo find $(APP_DIR) -type f -exec chmod 664 {} \;

fix-owner:
	sudo chown $(USER):$(USER) $(APP_DIR) -R