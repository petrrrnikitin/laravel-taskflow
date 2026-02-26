APP_CONTAINER = taskflow_app
ARTISAN       = docker exec --user www-data $(APP_CONTAINER) php artisan
PHP           = docker exec $(APP_CONTAINER)
RUN_ARGS      := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
ARTISAN_ARG   := $(RUN_ARGS)
ARGS          ?=
ifndef ARTISAN_ARG
ARTISAN_ARG   := list
endif

.PHONY: up down restart build rebuild migrate migration ide-helper pint pint-test phpstan artisan

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
ifeq ($(firstword $(MAKECMDGOALS)),artisan)
	@:
else
	$(ARTISAN) migrate
endif

migration:
ifndef name
	$(error Usage: make migration name=create_something_table)
endif
	docker exec $(APP_CONTAINER) php artisan make:migration $(name)

ide-helper:
	docker exec $(APP_CONTAINER) php artisan ide-helper:generate --no-interaction
	docker exec $(APP_CONTAINER) php artisan ide-helper:models --write --no-interaction
	docker exec $(APP_CONTAINER) php artisan ide-helper:meta --no-interaction

artisan:
	$(ARTISAN) $(if $(RUN_ARGS),$(RUN_ARGS),$(ARTISAN_ARG)) $(ARGS)
pint:
	$(PHP) ./vendor/bin/pint

pint-test:
	$(PHP) ./vendor/bin/pint --test

phpstan:
	$(PHP) ./vendor/bin/phpstan analyse --memory-limit=512M

test:
	docker exec --user www-data \
		-e APP_ENV=testing \
		-e DB_CONNECTION=sqlite \
		-e DB_DATABASE=:memory: \
		$(APP_CONTAINER) php artisan test $(RUN_ARGS)
