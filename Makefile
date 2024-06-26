up:
	docker compose up -d

down:
	docker compose down

pint:
	${CURDIR}/vendor/bin/pint

static-analysis:
	${CURDIR}/vendor/bin/phpstan analyse --memory-limit=2G

models:
	php artisan ide-helper:models

test:
	php artisan test

coverage:
	php artisan test --coverage-html=build/coverage