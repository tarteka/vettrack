# BackEnd (Symfony)

BackEnd para VetTrack desarrollado en Symfony.

## Levantar proyecto

Copiamos el archivo de ejemplo de variables de entorno de Docker:

```bash
cp .env.example .env
```

Copiamos el archivo de ejemplo de variables de entorno de Symfony y lo configuramos con los datos correspondientes:

```bash
cp symfony/.env.dev.local.dist symfony/.env.dev.local
```

Para generar una nueva `JWT_PASSPHRASE` para nuestro `.env` podemos hacerlo así:

```bash
openssl rand -base64 48
```

En modo `dev` añadimos `docker-compose.override.yml` a la raíz del proyecto:

```yml
services:
  mysql:
    ports:
      - "3306:3306"
```

Levantamos contenedores:

```bash
docker-compose up -d
```

Accedemos en nuestro navegador a http://localhost:8080.

Crear las tablas en la base de datos, añadir datos iniciales y generar las llaves SSL para JWT Lexic:

```bash
docker compose exec symfony bash -c "php bin/console doctrine:migrations:migrate --no-interaction"
docker compose exec symfony bash -c "php bin/console doctrine:fixtures:load --no-interaction"
docker compose exec symfony bash -c "php bin/console lexik:jwt:generate-keypair --no-interaction"
```

El proyecto incluye un contenedor dedicado a **cron**, encargado de ejecutar tareas automáticas mediante
comandos de Symfony:
  - Generación automática de slots de citas: `app:appointments:generate-slots`
  - Marcar tratamientos como finalizados: `app:treatments:complete-expired`

En producción, estas tareas se ejecutan automáticamente y no requieren intervención manual.

Ver tareas cron configuradas:
```bash
docker exec -it guno-cron crontab -l
```

Generar slots de manera manual (en desarrollo si queremos forzar manualmente):
```bash
docker compose exec symfony bash -c "php bin/console app:appointments:generate-slots --days=30"
```

Marcar tratamientos como finalizados (en desarrollo si queremos forzar manualmente):
```bash
docker compose exec symfony bash -c "php bin/console app:treatments:complete-expired"
```

### Usuarios iniciales:

- Cliente:
  - email: client@vettrack.com
  - password: password
- Admin (con rol de admin/veterinario):
  - email: admin@vettrack.com
  - password: password

## Documentación general

La documentación general se encuentra [aquí](https://gitlab.com/g-uno).

## Alternativa Makefile en Windows

Permisos

```bash
docker compose exec -u root symfony bash -c "find /var/www/symfony -type d -exec chmod 775 {} \;"
docker compose exec -u root symfony bash -c "find /var/www/symfony -type f -exec chmod 644 {} \;"
docker compose exec -u root symfony bash -c "chown -R guno:www-data /var/www/symfony"
```

Acceder al contenedor como root

```bash
docker exec -it -u root guno-symfony bash
```

symfony-cache-clear

```bash
docker compose exec symfony bash -c "php bin/console cache:clear"
```
