.PHONY: tests
tests: vendor ## Run all tests
	vendor/bin/simple-phpunit
	ENCODER=lcobucci vendor/bin/simple-phpunit
	ENCODER=lcobucci ALGORITHM=HS256 vendor/bin/simple-phpunit
	ENCODER=user_id_claim vendor/bin/simple-phpunit
	PROVIDER=lexik_jwt vendor/bin/simple-phpunit

.PHONY: cs
ci-cs: vendor ## Check all files using defined ECS rules (for CI/CD only)
	XDEBUG_MODE=off vendor/bin/ecs check

.PHONY: rector
rector: vendor ## Check all files using Rector
	XDEBUG_MODE=off vendor/bin/rector process --ansi --dry-run --xdebug

vendor: composer.json
	composer validate
	composer install

.DEFAULT_GOAL := help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help
