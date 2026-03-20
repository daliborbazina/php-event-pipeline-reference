.DEFAULT_GOAL := help

# =========================
# Compose
# =========================
COMPOSE_BASE = -f infra/docker-compose.yaml
COMPOSE_PROJECT = php-event-pipeline-reference
DOCKER_COMPOSE = COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT) docker compose $(COMPOSE_BASE)

# =========================
# Services
# =========================
APP_SVC = app

PHP = $(DOCKER_COMPOSE) run --rm $(APP_SVC) php
COMPOSER = $(DOCKER_COMPOSE) run --rm $(APP_SVC) composer

.PHONY: help init build up down restart ps logs shell install lint fix stan test qa wait-amqp wait-aggregate worker produce produce-invalid inspect reset init-db smoke

help:
	@echo ""
	@echo "PHP Event Pipeline Reference - Makefile"
	@echo "Environment:"
	@echo "  make init             Prepare local environment"
	@echo "  make build            Build containers"
	@echo "  make up               Start infrastructure (RabbitMQ)"
	@echo "  make down             Stop all services"
	@echo "  make restart          Restart services"
	@echo "  make ps               Show running services"
	@echo "  make logs             Follow service logs"
	@echo "  make shell            Open shell in app container"
	@echo ""
	@echo "Development:"
	@echo "  make install          Install Composer dependencies"
	@echo "  make lint             Run coding standard checks"
	@echo "  make fix              Fix coding standard issues"
	@echo "  make stan             Run static analysis"
	@echo "  make test             Run tests"
	@echo "  make qa               Run lint, stan and tests"
	@echo ""
	@echo "Runtime:"
	@echo "  make worker           Run worker process"
	@echo "  make produce          Publish a valid demo event"
	@echo "  make produce-invalid  Publish an invalid event"
	@echo "  make inspect          Inspect persisted aggregate"
	@echo ""
	@echo "Database:"
	@echo "  make init-db          Ensure SQLite schema exists"
	@echo "  make reset            Reset SQLite storage"
	@echo ""
	@echo "Workflows:"
	@echo "  make smoke            Run quick end-to-end evaluation"

init:
	cp -n infra/env/.env.example infra/env/.env || true
	mkdir -p storage/sqlite
	$(COMPOSER) install

build:
	$(DOCKER_COMPOSE) build

up:
	$(DOCKER_COMPOSE) up -d --build rabbitmq

down:
	$(DOCKER_COMPOSE) down

restart:
	$(DOCKER_COMPOSE) restart

ps:
	$(DOCKER_COMPOSE) ps

logs:
	$(DOCKER_COMPOSE) logs -f --tail=200

shell:
	$(DOCKER_COMPOSE) run --rm $(APP_SVC) sh

install:
	$(COMPOSER) install

lint:
	$(PHP) vendor/bin/php-cs-fixer fix --dry-run --diff

fix:
	$(PHP) vendor/bin/php-cs-fixer fix

stan:
	$(PHP) vendor/bin/phpstan analyse

test:
	$(PHP) vendor/bin/phpunit --configuration phpunit.xml

qa: lint stan test

# smoke test: broker ready
wait-amqp:
	@echo "Waiting for RabbitMQ AMQP listener on rabbitmq:5672..."
	@until $(DOCKER_COMPOSE) run --rm $(APP_SVC) php -r '$$s = @fsockopen("rabbitmq", 5672, $$errno, $$errstr, 1); if ($$s) { fclose($$s); exit(0); } exit(1);'; do \
		sleep 1; \
	done
	@echo "RabbitMQ AMQP listener is reachable."

# smoke test: event actually processed
wait-aggregate:
	@echo "Waiting for aggregate to be updated..."
	@attempt=0; \
	until $(PHP) cli.php inspect | grep -qv '\[inspect\] no aggregate rows found'; do \
		attempt=$$((attempt + 1)); \
		if [ $$attempt -ge 10 ]; then \
			echo "Aggregate was not updated in time."; \
			exit 1; \
		fi; \
		sleep 1; \
	done
	@echo "Aggregate updated."

worker:
	$(PHP) cli.php worker

produce:
	$(PHP) cli.php produce

produce-invalid:
	$(PHP) cli.php produce-invalid

inspect:
	$(PHP) cli.php inspect

init-db:
	$(PHP) cli.php init-db

reset:
	rm -f storage/sqlite/*.sqlite

define step
	echo ""
	echo "==> $(1)"
endef

define ok
	echo "✔ $(1)"
endef

smoke:
	@set -e; \
	\
	$(call step,Resetting local state); \
	$(MAKE) reset; \
	$(call ok,Reset done); \
	\
	$(call step,Starting environment); \
	$(DOCKER_COMPOSE) up -d --build rabbitmq worker; \
	$(MAKE) wait-amqp; \
	$(call ok,RabbitMQ ready); \
	\
	$(call step,Checking worker service); \
	if ! $(DOCKER_COMPOSE) ps --status running worker | grep -q worker; then \
		echo "Worker service is not running."; \
		$(DOCKER_COMPOSE) logs --tail=100 worker || true; \
		exit 1; \
	fi; \
	$(call ok,Worker running); \
	\
	$(call step,Publishing valid event); \
	$(MAKE) produce; \
	$(call ok,Valid event published); \
	\
	$(call step,Waiting for aggregate update); \
	if ! $(MAKE) wait-aggregate; then \
		echo ""; \
		echo "Worker logs:"; \
		$(DOCKER_COMPOSE) logs --tail=100 worker || true; \
		echo ""; \
		echo "Current aggregate state:"; \
		$(MAKE) inspect || true; \
		exit 1; \
	fi; \
	$(call ok,Aggregate updated); \
	\
	$(call step,Inspecting aggregate); \
	$(MAKE) inspect; \
	\
	$(call step,Publishing invalid event); \
	$(MAKE) produce-invalid; \
	sleep 2; \
	$(call ok,Invalid event published); \
	\
	$(call step,Done); \
	echo "Run 'make logs' to inspect system logs."
