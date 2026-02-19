APP_CONTAINER = taskflow_app
ARTISAN       = docker exec --user www-data $(APP_CONTAINER) php artisan

.PHONY: up down restart build rebuild migrate migration

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
	$(ARTISAN) make:migration $(name)