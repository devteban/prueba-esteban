# Variables
DOCKER_COMPOSE = docker compose

# Inicia el proyecto
init-project:
	$(DOCKER_COMPOSE) up -d

# Actualiza el esquema de la base de datos
update-database-schema:
	php bin/console doctrine:schema:update --force

# Carga los datos de prueba (si tienes fixtures cargados)
load-fixtures-data:
	php bin/console doctrine:fixtures:load --no-interaction