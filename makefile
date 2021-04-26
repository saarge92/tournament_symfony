up:
	docker exec -ti tournament-php composer install
	docker exec -ti tournament-php php bin/console doctrine:schema:update --force
	docker exec -ti tournament-php php bin/console doctrine:fixtures:load --append
