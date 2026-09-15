# TaskHarbor

A personal task manager built with **Vue 3 + Laravel 12 + MySQL**. Vue single-file components use the Composition API, reactive state, computed filters, forms and fetch requests. Laravel validates inputs and provides a token-protected JSON API. Creating or updating a task and its event record happens inside a database transaction.

## Open on Windows

Install Node.js 22+ and Docker Desktop. Start Docker Desktop and wait for its engine to be running. Extract this project and open PowerShell inside the folder containing compose.yaml.

```powershell
node setup.mjs
docker compose up --build -d
```

Open **http://localhost:8082**. This port differs from SitePulse's 8080, so both can run together. Open `.env` in your editor and copy only the value after `API_TOKEN=` into the app. Keep the token private. The first build downloads PHP, Composer dependencies, MySQL and frontend dependencies. If the page loads before the API is ready, wait a few seconds and connect again.

Add a task, edit its title/priority, mark it complete, and try the filters. Reload the page and reconnect: tasks persist in MySQL. Deletion is permanent and requires confirmation.

```powershell
node smoke.mjs
docker compose exec api vendor/bin/phpunit
docker compose down
```

The smoke test creates and deletes only its own temporary task. `docker compose down` preserves the database volume. For problems use `docker compose ps` and `docker compose logs --tail=100 api web db`. Do not share `.env` contents or database credentials.

## Development

```sh
npm ci
npm test
npm run build
```

Rebuild Docker after changes. Backend source is under `backend/`; Vue source is under `src/`. The Docker image installs Composer dependencies; PHP and Composer need not be installed on your Windows computer. Composer dependencies resolve on first build; commit a generated composer.lock for stricter reproducibility. npm dependencies are locked.

## API

Use `Authorization: Bearer <API_TOKEN>` and `Accept: application/json`.

| Method | Endpoint | Purpose |
|---|---|---|
| GET | /api/tasks | List tasks |
| POST | /api/tasks | Create with title and optional priority/status |
| PATCH | /api/tasks/{id} | Change title, priority or status |
| DELETE | /api/tasks/{id} | Delete task and related events |

Priority: low, medium, high. Status: todo, done. Invalid fields return 422; invalid tokens return 401; missing tasks return 404. Events are retained until their task is deleted; this is not a permanent audit log.

## Scope and learning

This is a local single-workspace portfolio demo. The shared token is not a multi-user login system. Host ports bind to localhost; do not publish this setup directly to the internet. Laravel's development server is used for simplicity. There is no third-party API integration, AWS deployment, Jira or Bitbucket integration here.

Before describing Vue experience, run and modify this project yourself: add a due-date field through the migration/API/form; write a test for it; explain ref versus computed, v-model and event handlers; inspect POST/PATCH in browser DevTools; deliberately fail an event insert and explain transaction rollback. AI-assisted code is a starting point for practice, not proof of proficiency or employment duration.

Reference documentation: [Vue](https://vuejs.org/guide/quick-start.html), [Laravel routing](https://laravel.com/docs/12.x/routing).

## Verification

See VALIDATION.md for checks actually run in the authoring environment. GitHub Actions is included for backend and end-to-end verification after upload; it has not been run remotely yet.
