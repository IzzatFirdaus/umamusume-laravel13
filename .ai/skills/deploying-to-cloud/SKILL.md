---
name: deploying-to-cloud
description: "Trigger when deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments."
disable-model-invocation: false
license: MIT
metadata:
  author: laravel
  domain: deployment
---

# Deploying to Cloud

Deploy and manage this Laravel application on Laravel Cloud.

## Trigger Criteria

User requests: "deploy", "ship to Cloud", "create Cloud environment", "configure Cloud resources", "check billing", "set up production", "Laravel Cloud".

## Prerequisites

- A Laravel Cloud account at [cloud.laravel.com](https://cloud.laravel.com).
- The Cloud CLI installed: `npm install -g @laravel/cloud` or via the Laravel installer.
- Application pushed to a Git repository (GitHub, GitLab, or Bitbucket).

## Initial Deployment

1. Log in: `cloud login`
2. Initialize: `cloud init` (creates `cloud.yml` in the project root)
3. Deploy: `cloud deploy`

## Cloud CLI Commands

- **List environments:** `cloud environments`
- **List resources:** `cloud resources`
- **Create a database:** `cloud databases create`
- **Create a cache:** `cloud caches create`
- **Create a queue:** `cloud queues create`
- **Add a domain:** `cloud domains add`
- **View deployment logs:** `cloud deploy logs`
- **Deploy a specific environment:** `cloud deploy production`

## Configuration

The `cloud.yml` file defines environments, resources, and build settings:

<code-snippet name="cloud-yml" lang="yaml">
name: my-app
environments:
  production:
    git_branch: main
    pull_request deployments: true
    build:
      commands:
        - composer install --no-interaction --optimize-autoloader
        - npm install
        - npm run build
    resources:
      - database:
          name: app-db
          size: small
      - cache:
          name: app-cache
      - queue:
          name: app-queue
</code-snippet>

## Environment Variables

- Set production secrets via the Cloud dashboard or `cloud secrets:set`.
- Never commit `.env` — use `cloud secrets:set KEY=value`.
- Cloud automatically injects `APP_KEY`, `APP_ENV`, and database credentials.

## Domain Configuration

- Add a custom domain: `cloud domains add example.com`
- Cloud automatically provisions an SSL certificate via Let's Encrypt.
- For root domains, configure an apex record as documented in the Cloud dashboard.

## Troubleshooting

- **Build failures:** Check `cloud deploy logs` for composer/npm errors.
- **Runtime errors:** Check application logs via the Cloud dashboard.
- **Database migration:** Run `php artisan migrate --force` in a Cloud CLI shell or via a deployment hook.
- **Vite manifest errors:** Ensure `npm run build` runs during the Cloud build phase.