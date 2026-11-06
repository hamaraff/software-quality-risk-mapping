# Copilot instructions for gestionRendezVous

## Repository shape

This repository is a small PHP + Vue.js appointment-management app. The backend lives at the project root and the frontend lives in `vue/`.

- PHP API entry point: `route.php`
- MVC-style controllers: `controller/*.php`
- PDO model classes: `model/*.php`
- Database schema dump: `rdv.sql`
- Vue SPA: `vue/src/`

The app is not split into a modern framework stack like Laravel or Express; it is a lightweight custom MVC app with raw JSON endpoints and a Vue router in the client.

## Build, lint, and validation commands

There are no automated tests or a dedicated test runner configured in this repository.

Frontend commands (run from `vue/`):

```bash
cd vue
npm install
npm run lint
```

Production build:

```bash
cd vue
npm run build
```

On modern Node versions (Node 17+ / 24), the Vue CLI + webpack 4 build may fail with OpenSSL errors. If that happens, use:

```bash
cd vue
NODE_OPTIONS=--openssl-legacy-provider npm run build
```

If you are using an older Node LTS version such as 16 or 18, the plain `npm run build` command is usually sufficient.

There is no `npm test` script to run a single unit test; the repo currently relies on linting and manual verification of the PHP/Vue app.

## High-level architecture

### Backend

`route.php` is the central request dispatcher. It autoloads classes from `model/`, reads `$_GET['p']`, resolves the controller name, and invokes a method on that controller.

Examples from the codebase:

- `?p=ApiUser/create` calls `ApiUser::create()`
- `?p=ApiAppointement/showMyAppointments/12` calls `ApiAppointement::showMyAppointments(12)`

The controllers in `controller/` are thin HTTP handlers. They set JSON headers, read JSON from `php://input`, instantiate the corresponding model, and return JSON responses. The backend does not use a framework router or REST abstraction layer.

The model layer in `model/` contains the database access classes:

- `Database.php` creates the PDO connection
- `Users.php` handles user creation/lookup
- `Appointements.php` handles appointment creation, update, delete, and availability checks
- `Timeslots.php` is present in the repository but not fully inspected here; keep it aligned with the `timeslots` table in `rdv.sql`

### Frontend

The Vue app is a simple SPA mounted in `vue/src/main.js`:

- `vue/src/router/index.js` defines the routes for Home, About, SignUp, Authentication, Appointement, and Dashboard
- `vue/src/views/*.vue` holds the page-level components
- `vue/src/components/*.vue` contains reusable form widgets used by those views
- `vue/src/App.vue` is the global nav shell

The frontend uses `axios`-style fetches to the PHP API endpoints; typical flow is:

1. user fills in a form in Vue
2. client POSTs JSON to a PHP endpoint
3. controller validates input and writes/reads via the model layer
4. backend returns JSON, and the Vue page updates the UI

### Database model

`rdv.sql` defines the core schema:

- `users`: user profile and generated `Reference`
- `timeslots`: fixed appointment slots such as `10:00-10:30`, `11:00-11:30`, etc.
- `appointements`: links a user to a timeslot, subject, and date

This is an appointment-management app with a fixed five-slot daily schedule and a user reference system rather than a full auth system.

## Conventions specific to this codebase

- The backend is URL-driven and controller-method-based; keep new routes consistent with `route.php` dispatch rules.
- API handlers are manually written and return JSON with explicit CORS headers (`Access-Control-Allow-Origin:*` and JSON `Content-Type`).
- Models use raw PDO prepared statements and `bindParam()`. Do not introduce an ORM or a different database abstraction layer unless the repo is intentionally migrated.
- Business logic is embedded in model methods, not in a separate service layer.
- The app uses `htmlspecialchars(strip_tags(...))` on incoming values in some model methods. Preserve that normalization pattern when touching user-controlled fields.
- User signup generates a `Reference` value based on parts of the first name, last name, and CIN, then appends a random hex token. Keep that format if you modify registration logic.
- API input is read from `php://input` as JSON (`json_decode(file_get_contents("php://input"))`), so any new endpoint should follow that pattern.
- Frontend route names and URL paths are not fully REST-style; they are simple Vue router paths such as `/SignUp`, `/Authentification`, `/Appointement`, and `/Dashbord`.
- The database connection in `model/Database.php` uses MySQL credentials for `localhost` and the database name `gestionrendezvous`; the SQL dump file is named `rdv.sql` and should be imported into a matching database before local runs.

## Practical guidance for future Copilot sessions

- When changing a PHP endpoint, check the matching controller and the model class it instantiates; this repo does not hide database logic behind services.
- When changing a Vue screen, inspect the corresponding route and component together; the app is composed of small view/component modules rather than a large design system.
- Keep backend JSON contracts stable; the frontend consumes them directly and often assumes specific field names such as `userId`, `Reference`, `user_subject`, `c_date`, `start_at`, and `end_at`.
- Prefer surgical edits that align with the existing MVC organization instead of introducing new abstractions.
