# Variables
DOCKER_COMPOSE = docker compose

# Inicia el proyecto
init-project:
	$(DOCKER_COMPOSE) up -d
	composer install

# Actualiza el esquema de la base de datos
update-database-schema:
	php bin/console doctrine:database:create || true
	php bin/console doctrine:schema:update --force

# Carga los datos de prueba
load-fixtures-data:
	php bin/console doctrine:fixtures:load --no-interaction

# Ejecuta los tests, reseteando la base de datos
test:
	@echo "Reseteando la base de datos y ejecutando los tests..."
	php bin/console doctrine:database:drop --env=test --force --if-exists
	php bin/console doctrine:database:create --env=test || true
	php bin/console doctrine:schema:update --force --env=test
	php bin/console doctrine:fixtures:load --no-interaction --env=test
	php bin/phpunit