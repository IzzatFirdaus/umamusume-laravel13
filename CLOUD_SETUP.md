# Cloud Development Setup Summary

This document describes the cloud development infrastructure added to the Trainer Desk repository.

## Files Created

### 1. Dev Container Configuration
**Location**: `.devcontainer/devcontainer.json`

The primary configuration for cloud IDEs (GitHub Codespaces, Cursor Cloud Agents, VS Code Remote). This file includes:

- **Base image**: `mcr.microsoft.com/vscode/devcontainers/laravel:1-12-bookworm` (PHP 8.3+ with Laravel tooling)
- **Features**: Node.js 20.x and Docker-in-Docker
- **Port forwarding**: 
  - Port 8000 (Laravel development server)
  - Port 5173 (Vite dev server)
- **Post-create commands**: Automatic installation of all dependencies, database migration, and frontend build
- **VS Code extensions**: Common Laravel/PHP development tools (Pint, Blade formatter, Tailwind IntelliSense, ESLint, Prettier)
- **Host requirements**: Minimum 8GB RAM

### 2. Agent Ignore Files

#### `.codexignore`
Excludes large directories from Codex agent indexing:
- `node_modules/`, `vendor/` - Dependencies
- `build/`, `dist/`, `public/build/` - Build artifacts
- `storage/app/private/*` - Private storage (snapshots, artwork, backups)
- Cache files and system files

#### `.claudeignore`
Similar exclusions for Claude code agent to improve performance and reduce noise.

### 3. Cloud Setup Script

**Location**: `tools/cloud-setup.sh` (executable)

A standalone shell script that replicates the dev container's post-create command. Can be used for:
- Manual cloud environment setup
- Alternative CI/CD pipelines
- Custom deployment scripts

**Usage**:
```bash
./tools/cloud-setup.sh
```

### 4. Updated Documentation

#### `.env.example`
Updated to reflect SQLite-only configuration instead of MySQL (the app does not use Sail/MySQL in production).

#### `AGENTS.md`
Added comprehensive "Cloud Development Setup & Secrets Handling" section including:
- Quick start instructions for GitHub Codespaces and Cursor Cloud
- Automatic provisioning details
- Secrets management best practices
- No-commit policy for actual credentials
- Platform-specific secret management guidance

#### `README.md`
Added:
- Cloud development quick start section in Overview
- Enhanced Requirements section with cloud setup notes
- Link to AGENTS.md for detailed cloud instructions

## What Gets Set Up Automatically

When you open this repository in a cloud IDE, the following happens automatically:

1. **Composer dependencies installed** (`composer install --no-interaction`)
2. **Application key generated** (`php artisan key:generate`)
3. **SQLite database initialized** (`database/database.sqlite` created)
4. **Database migrations run** (`php artisan migrate --force`)
5. **npm dependencies installed** (`npm install`)
6. **Frontend assets built** (`npm run build`)
7. **Dev servers ready**: Port 8000 (Laravel) + Port 5173 (Vite HMR)

## Security Notes

⚠️ **Critical**: This application is local-only with no authentication surface. It runs on loopback only and never exposes credentials.

However, when adding new features or working in cloud environments:
- Never commit `.env` files or actual credentials
- Use platform secret management (GitHub Secrets, etc.)
- Keep sensitive data out of config files and documentation

See `AGENTS.md` for complete secrets handling guidelines.

## Testing the Setup

### Option 1: GitHub Codespaces
1. Click **"Code"** → **"Codespaces"** tab → **"+ New codespace"**
2. Wait for provisioning to complete
3. Check that dev containers shows PHP, Node.js versions, and all extensions installed
4. Open terminal and run `composer test` to verify everything works

### Option 2: Cursor Cloud
1. Open repository in Cursor
2. Select "Use Dev Container" when prompted
3. Follow same verification as above

### Option 3: Local Simulation
```bash
# Run the standalone setup script
./tools/cloud-setup.sh

# Start dev server
composer dev
```

## Maintenance

### Updating Dependencies
The dev container uses specific feature versions. When updating:
- Check [Docker Dev Containers registry](https://containers.dev/catalog)
- Update version numbers in `.devcontainer/devcontainer.json` if needed
- Test changes in a fresh codespace before committing

### Adding New Extensions
If you need additional VS Code extensions:
1. Add extension ID to `"extensions"` array in `.devcontainer/devcontainer.json`
2. Get IDs from [VS Code Marketplace](https://marketplace.visualstudio.com/)
3. Format: `"publisher.extension-id"` (e.g., `"esbenp.prettier-vscode"`)

### Post-Creation Commands
Customize automatic setup by editing `postCreateCommand`. For complex setups, consider using `tools/cloud-setup.sh` instead.

## Troubleshooting

### Issue: Slow initial load
- First launch downloads ~2GB Docker image
- Subsequent launches are faster
- Consider pre-building images for team consistency

### Issue: Dependency conflicts
- Clear caches: `rm -rf node_modules vendor public/build`
- Re-run setup: `./tools/cloud-setup.sh`
- Check PHP version matches requirements (8.3+)

### Issue: Database migration failures
- Ensure SQLite extension enabled: `php -m | grep pdo_sqlite`
- Check file permissions on `database/database.sqlite`
- Verify PHP has write access to project directory

## References

- [Dev Container Specification](https://containers.dev/implementors/specification/)
- [VS Code Dev Containers Extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers)
- [GitHub Codespaces Docs](https://docs.github.com/en/codespaces)
- [Laravel Dev Containers](https://github.com/devcontainers/images/tree/main/src/laravel)

---

Last updated: 2026-10-23
