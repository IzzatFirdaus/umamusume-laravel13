# Local-only trainer tool. Windows: run through Git Bash.
.PHONY: install dev test lint stan migrate seed fetch reparse backup lore lore-code audit

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

# Extended gate over shipped code, additive to `lore` rather than a replacement.
#
# Two gaps it closes. First, plain `git grep` searches tracked files only, so a
# brand new Blade template passes `lore` by being invisible to it; --untracked fixes
# that, which matters most at the exact moment a file is new enough to be unreviewed.
# Second, `lore` checks equine vocabulary but not the Global client terminology, so
# "Wisdom" for Wit and "Motivation" for Mood, which the source wikis use throughout,
# would sail through. They are banned here.
#
# Scoped to app code on purpose. docs/ legitimately quotes the wiki English it is
# warning against, and grepping it would bury real hits in quoted noise.
#
# Each hit still needs a context ruling, same as `lore`: "dam" inside "damaged",
# "stable" as an adjective, "friend" inside "friendship" are all allowed senses.
lore-code:
	git grep --untracked -inwE "horse|horses|sire|sires|foal|foals|mare|mares|filly|jockey|saddle|bridle|hoof|hooves|mane|paddock|tack|reins|herd|mount" -- 'app/**' 'config/**' 'resources/**' 'routes/**' 'database/**' 'tests/**' || true
	git grep --untracked -inwE "dam|stable|wisdom|motivation|strength|endurance|luck|agility|charisma|gacha|jewel|factor|grass|sand|friend|planned|archived|account|login" -- 'app/**' 'config/**' 'resources/**' 'routes/**' 'database/**' 'tests/**' || true
	git grep --untracked -inE "condition gauge|pick-?up banner|share link" -- 'app/**' 'config/**' 'resources/**' 'routes/**' 'database/**' 'tests/**' || true

audit:
	composer audit
	npm audit --omit=dev
