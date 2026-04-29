UNAME_S := $(shell uname -s)
ifeq ($(UNAME_S),Darwin)
	SUDO =
else
	SUDO = sudo
endif

include .env
renew:
	$(SUDO) docker compose down
	$(SUDO) docker compose up -d
	$(SUDO) docker exec dent_app_back_app php artisan config:clear
	$(SUDO) docker exec dent_app_back_app php artisan migrate
	$(SUDO) docker exec dent_app_back_app php artisan route:clear
	$(SUDO) docker exec dent_app_back_app php artisan cache:clear
	$(SUDO) docker exec dent_app_back_app php artisan config:clear
	$(SUDO) docker exec dent_app_back_app php artisan cache:clear
	$(SUDO) docker exec dent_app_back_app php scripts/create_user.php

up:
	$(SUDO) docker-compose up -d
down:
	$(SUDO) docker-compose down
up-build:
	$(SUDO) docker-compose up -d --build
down-remove:
	$(SUDO) docker-compose down -v



createdb:
	$(SUDO) docker-compose exec dent_app_back_db psql -U pskudlik -d postgres -c "CREATE DATABASE dent_db_back;"

createdb-role:
	$(SUDO) docker-compose exec dent_app_back_db psql -U pskudlik -d postgres -c "CREATE ROLE pskudlik WITH LOGIN PASSWORD 'Troll001#';"

createdb-user:
	$(SUDO) docker-compose exec dent_app_back_db psql -U pskudlik -d postgres -c "ALTER USER pskudlik WITH SUPERUSER;"

createdb-local-user:
	$(SUDO) docker-compose exec dent_app_back_db psql -U pskudlik -d postgres -c "CREATE ROLE pskudlik WITH LOGIN PASSWORD 'Troll001#';"

create-user:
	$(SUDO) docker exec dent_app_back_app php artisan route:clear
	$(SUDO) docker exec dent_app_back_app php artisan cache:clear
	$(SUDO) docker exec dent_app_back_app php artisan config:clear
	$(SUDO) docker exec dent_app_back_app php artisan cache:clear
	$(SUDO) docker exec dent_app_back_app php scripts/create_user.php

migrate:
	$(SUDO) docker-compose exec dent_app_back_app php artisan migrate

migrate-fresh:
	$(SUDO) docker-compose exec dent_app_back_app php artisan migrate:fresh

optimize:
	$(SUDO) docker-compose exec dent_app_back_app php artisan optimize
	$(SUDO) docker-compose exec dent_app_back_app php artisan cache:clear
	$(SUDO) docker-compose exec dent_app_back_app php artisan config:clear
	$(SUDO) docker-compose exec dent_app_back_app php artisan cache:clear

test:
	$(SUDO) docker-compose exec dent_app_back_app php artisan optimize
	$(SUDO) docker-compose exec dent_app_back_app php artisan cache:clear
	$(SUDO) docker-compose exec dent_app_back_app php artisan config:clear
	$(SUDO) docker-compose exec dent_app_back_app php artisan cache:clear
	$(SUDO) docker-compose exec dent_app_back_app php artisan test --env=testing

logs:
	$(SUDO) docker-compose exec dent_app_back_app tail -f storage/logs/laravel.log

artisan:
	$(SUDO) docker-compose exec dent_app_back_app php artisan $@

seed-users:
	$(SUDO) docker-compose exec dent_app_back_app php artisan tinker --execute="App\Models\User::factory()->count(50)->create();"

seed-patients:
	$(SUDO) docker-compose exec dent_app_back_app php artisan tinker --execute="App\Models\Patient::factory()->count(250)->create();"

route-clear:
	$(SUDO) docker-compose exec dent_app_back_app php artisan route:clear

route-list:
	$(SUDO) docker-compose exec dent_app_back_app php artisan route:list
