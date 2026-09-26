# Local-only trainer tool. Windows: run through Git Bash.
.PHONY: install dev test lint stan migrate seed fetch reparse backup lore audit

install:
	composer install
	npm install

dev:
	composer run dev

test:
	php artisan test --compact

lint:
	vendor/bin/pint --dirty --format agent

stan:
	vendor/bin/phpstan analyse --no-progress

migrate:
	php artisan migrate:fresh --seed

seed:
	php artisan db:seed

fetch:
	php artisan uma:fetch

reparse:
	php artisan uma:reparse $(SOURCE)

backup:
	php artisan uma:backup

# C-4 lore grep (CONTEXT.md CONSTRAINTS.md): violations are hard failures.
# Words appear inside "damaged"/"stable weather"; each hit needs a context ruling.
lore:
	git grep -inE "horse|sire|foal|🏇" -- ':!vendor' ':!node_modules' ':!docs/PRE-MORTEM.md' || true
	git grep -inwE "dam|mare|stable" -- ':!vendor' ':!node_modules' ':!docs/PRE-MORTEM.md' || true

audit:
	composer audit
	npm audit --omit=dev
