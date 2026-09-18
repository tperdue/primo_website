# Local Setup

1. Run `composer install`.
2. Copy `env` to `.env` if needed and set `app.baseURL` to the local URL, such as `http://localhost:8080/`.
3. Run `php spark migrate --all`. Without database overrides, the default SQLite database is created in `writable/primo.sqlite`. An existing `.env` database configuration takes precedence; use a database you intend to migrate.
4. Create an admin account interactively with `php spark shield:user create -n owner -e you@example.com -g admin`. The command prompts for a password; do not put it in a script or commit it.
5. Run `php spark serve --host 127.0.0.1 --port 8080` and open `/login` to edit business settings.

Public registration and passwordless login are disabled for this initial admin-only slice. Before production deployment, configure HTTPS and a production `app.baseURL`, back up the configured database, and enter the real business identity/contact email in the settings form. Session cookies are marked Secure in production.
