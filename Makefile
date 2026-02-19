APP_CONTAINER = taskflow_app
ARTISAN       = docker exec --user www-data $(APP_CONTAINER) php artisan

.PHONY: up down restart build rebuild migrate migration ide-helper

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

build:
	docker compose build

rebuild:
	docker compose up -d --build

migrate:
	$(ARTISAN) migrate

migration:
ifndef name
	$(error Usage: make migration name=create_something_table)
endif
	docker exec $(APP_CONTAINER) php artisan make:migration $(name)

ide-helper:
	docker exec $(APP_CONTAINER) php artisan ide-helper:generate --no-interaction
	docker exec $(APP_CONTAINER) php artisan ide-helper:models --write --no-interaction
	docker exec $(APP_CONTAINER) php artisan ide-helper:meta --no-interaction