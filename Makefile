up:
	docker compose up -d

down:
	docker compose down

static-analysis:
	${CURDIR}/vendor/bin/phpstan analyse --memory-limit=2G