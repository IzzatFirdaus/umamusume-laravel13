# Reproducible queries for 38 table_only rows (B2)

## File export_ids source
The file export_ids are read from `database/seeders/data/skills.609afe88.json`.

## Query template
```php
$fileExportIds = collect(json_decode(file_get_contents(database_path('seeders/data/skills.609afe88.json')), true))
    ->where('id', '!=', null)
    ->pluck('id')
    ->all();

$ids = Skill::query()
    ->whereNotNull('export_id')
    ->whereNotIn('export_id', $fileExportIds)
    ->where('rarity', $rarity)
    ->pluck('export_id')
    ->all();
```

## Category 1: rarity=6 (evolved, no pre_evo) — 17 rows
```bash
php artisan tinker --execute="
\$fileIds = collect(json_decode(file_get_contents(database_path('seeders/data/skills.609afe88.json')), true))->where('id', '!=', null)->pluck('id')->all();
\$ids = \App\Models\Skill::query()->whereNotNull('export_id')->whereNotIn('export_id', \$fileIds)->where('rarity', 6)->pluck('export_id')->all();
echo implode(',', \$ids) . PHP_EOL;
echo 'Count: ' . count(\$ids) . PHP_EOL;
"
```
**Output:** 100903111,100903211,101301311,101703111,101703211,101803111,101803211,104503121,110602121,110802111,110802211,415011,415021,415031,415041,415051,415061 (17)

## Category 2: rarity=5 (unique variant) — 4 rows
```bash
php artisan tinker --execute="
\$fileIds = collect(json_decode(file_get_contents(database_path('seeders/data/skills.609afe88.json')), true))->where('id', '!=', null)->pluck('id')->all();
\$ids = \App\Models\Skill::query()->whereNotNull('export_id')->whereNotIn('export_id', \$fileIds)->where('rarity', 5)->pluck('export_id')->all();
echo implode(',', \$ids) . PHP_EOL;
echo 'Count: ' . count(\$ids) . PHP_EOL;
"
```
**Output:** 111081,120091,120171,120181 (4)

## Category 3: rarity=2 (evolvable) — 7 rows
```bash
php artisan tinker --execute="
\$fileIds = collect(json_decode(file_get_contents(database_path('seeders/data/skills.609afe88.json')), true))->where('id', '!=', null)->pluck('id')->all();
\$ids = \App\Models\Skill::query()->whereNotNull('export_id')->whereNotIn('export_id', \$fileIds)->where('rarity', 2)->pluck('export_id')->all();
echo implode(',', \$ids) . PHP_EOL;
echo 'Count: ' . count(\$ids) . PHP_EOL;
"
```
**Output:** 204721,204731,204741,204751,204801,204811,210401 (7)

## Category 4: rarity=1 (learnable) — 10 rows
```bash
php artisan tinker --execute="
\$fileIds = collect(json_decode(file_get_contents(database_path('seeders/data/skills.609afe88.json')), true))->where('id', '!=', null)->pluck('id')->all();
\$ids = \App\Models\Skill::query()->whereNotNull('export_id')->whereNotIn('export_id', \$fileIds)->where('rarity', 1)->pluck('export_id')->all();
echo implode(',', \$ids) . PHP_EOL;
echo 'Count: ' . count(\$ids) . PHP_EOL;
"
```
**Output:** 204722,204732,204742,204752,204762,204792,204802,204812,204892,210402 (10)

## Verification
- 17 + 4 + 7 + 10 = 38 ✓
- No duplicates in rarity=6 list (415051 appeared twice in earlier report - typo, fixed here)

## NULL-export_id rows (6 rows, separate bucket)
```bash
php artisan tinker --execute="
\App\Models\Skill::whereNull('export_id')->get(['id', 'name', 'rarity'])->each(function(\$s) { echo \$s->id . ': ' . \$s->name . ' (rarity=' . (\$s->rarity ?? 'null') . ')' . PHP_EOL; });
"
```
**Output:** 1949: Skill A (rarity=null), 1950: Skill B (rarity=null), 1951: Skill A (rarity=null), 1952: Skill B (rarity=null), 1953: Skill A Test (rarity=null), 1954: Skill B Test (rarity=null) — all from test fixtures.
