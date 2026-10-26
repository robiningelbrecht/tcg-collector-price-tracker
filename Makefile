compose=docker compose

dc:
	@${compose} -f docker-compose.yml $(cmd)

dcr:
	@make dc cmd="run --rm php-cli $(cmd)"

up:
	@make dc cmd="up -d"

stop:
	@make dc cmd="stop"

build-containers:
	@make dc cmd="--profile on-demand build"

down:
	@make dc cmd="down"

console:
	@make dcr cmd="bin/console $(arg)"

composer:
	@make dcr cmd="composer $(arg)"

# Database migration helpers.
migrate-diff:
	@make console arg="doctrine:migrations:diff"

migrate:
	@make console arg="doctrine:migrations:migrate --no-interaction --allow-no-migration"

# Code quality tools.
phpstan:
	@make dcr cmd="vendor/bin/phpstan --memory-limit=1G $(arg)"

csfix:
	@make dcr cmd="vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php"

rector:
	@make dcr cmd="vendor/bin/rector"
