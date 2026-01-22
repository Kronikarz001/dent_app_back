up:
	docker-compose up -d
down:
	docker-compose down
up-build:
	docker-compose up -d --build
down-remove:
	docker-compose down -v


createdb:
	docker-compose exec dent_app_back_db psql -U pskudlik -d postgres -c "CREATE DATABASE dent_db_back;"

createdb-role:
	docker-compose exec dent_app_back_db psql -U pskudlik -d postgres -c "CREATE ROLE pskudlik WITH LOGIN PASSWORD 'Troll001#';"

createdb-user:
	docker-compose exec dent_app_back_db psql -U pskudlik -d postgres -c "ALTER USER pskudlik WITH SUPERUSER;"

create-local-user:
	docker-compose exec dent_app_back_db psql -U pskudlik -d postgres -c "CREATE ROLE pskudlik WITH LOGIN PASSWORD 'Troll001#';"


migrate:
	docker-compose exec dent_app_back_app php artisan migrate
migrate-fresh:
	docker-compose exec dent_app_back_app php artisan migrate:fresh
optimize:
	docker-compose exec dent_app_back_app php artisan optimize
	docker-compose exec dent_app_back_app php artisan cache:clear
	docker-compose exec dent_app_back_app php artisan config:clear
	docker-compose exec dent_app_back_app php artisan cache:clear

