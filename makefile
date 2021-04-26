up:
	cp .env.example .env
	cp .env.test.example .env.test
	docker-compose up --remove-orphans --build -d
	docker exec -ti tournament-php composer install
	docker exec -ti tournament-php php bin/console doctrine:schema:update --force
	docker exec -ti tournament-php php bin/console doctrine:fixtures:load --no-interaction
