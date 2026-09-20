# Production Deployment

Pushes to `main` run PHPUnit and deploy the exact tested commit to the Primo production server. The workflow can also be started manually from GitHub Actions.

## GitHub Configuration

The repository requires one Actions secret:

- `PRODUCTION_SSH_KEY`: the private half of the deployment-only SSH key installed for the server's `primo` user.

The key is restricted server-side to the deployment entrypoint. It cannot open an interactive shell, forward ports, or run arbitrary commands. The server host key is pinned in `deploy/known_hosts`.

## Release Process

The root-owned `/usr/local/sbin/primo-deploy` command:

1. Locks deployments so only one can run at a time.
2. Clones and verifies the requested commit in `/var/www/primo/releases`.
3. Links the server-owned `.env`, writable data, and public media uploads.
4. Installs production Composer dependencies and checks platform requirements.
5. Creates and validates a compressed MySQL backup.
6. Runs all CodeIgniter migrations.
7. Atomically switches `/var/www/primo/current` to the new release.
8. Reloads PHP-FPM and checks `/` and `/login` over local HTTPS.
9. Restores the previous code symlink if a health check fails. Database migrations are not reversed automatically.

Deployment output is available in the GitHub Actions run and `/var/log/primo-deploy.log` on the server. Failed release directories and database backups are retained for diagnosis and recovery; cleanup is intentionally manual.

## Manual Invocation

An administrator can deploy a known commit from the server with:

```bash
sudo /usr/local/sbin/primo-deploy <40-character-commit-sha>
```
