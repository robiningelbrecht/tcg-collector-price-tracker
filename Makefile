compose=docker compose

dc:
	@${compose} -f docker-compose.yml $(cmd)

dcr:
	@make dc cmd="run --rm php-cli $(cmd)"

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

# Code quality tools.
phpstan:
	@make dcr cmd="vendor/bin/phpstan --memory-limit=1G $(arg)"

csfix:
	@make dcr cmd="vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php"

rector:
	@make dcr cmd="vendor/bin/rector"
