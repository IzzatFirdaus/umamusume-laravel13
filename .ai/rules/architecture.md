# Architecture Rules

## Enums

- Enums live in `app/Enums/`.

## Authorization

- There is no authorization layer: no `app/Policies/` and no custom middleware. Do not add a policy or gate for a route unless the work asks for one.

## HTTP surface

- Browser routes are declared in `routes/web.php`; JSON endpoints live under `app/Http/Controllers/Api/V1/` and are declared in `routes/api.php`.
