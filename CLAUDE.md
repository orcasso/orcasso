# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

OrcAsso — Symfony 7.2 / PHP 8.2 web app for managing members of non-profit associations (members, activities, orders, payments, public order forms). MariaDB 10 via Doctrine ORM 3. UI built on AdminLTE 3 + Symfony AssetMapper (importmap, no webpack). Translations are French-first (`translations/*.fr.yml`).

## Dev environment

All commands run inside the `app` container. The Docker stack defines `app` (PHP/Apache) and `mariadb`; 
Traefik exposes the app at `orcasso.local` via the external `web` network.

```bash
# First-time setup
docker compose up -d
docker compose exec app composer install
docker compose exec app bin/console importmap:install
docker compose exec app composer reset:db   # drop schema, run migrations, load fixtures

# Create an admin user
docker compose exec app bin/console app:user:create
```

`composer reset:db` also wipes `var/member_documents`, so member file uploads are lost when you reset.

## Common commands

```bash
docker compose exec app composer lint        # apply php-cs-fixer (@Symfony + @Symfony:risky)
docker compose exec app composer lint:ci     # dry-run, used for CI
docker compose exec app composer test        # phpunit --testdox
docker compose exec app vendor/bin/phpunit tests/Controller/Admin/MemberControllerTest.php
docker compose exec app vendor/bin/phpunit --filter testCreate tests/Controller/Admin/MemberControllerTest.php
```

The test bootstrap (`tests/bootstrap.php`) drops and recreates the `orcasso_test` database, runs migrations, and reloads fixtures **on every phpunit invocation** — first-run setup requires creating the DB once:

```bash
docker compose exec mariadb mysql -u root -p -e \
  "CREATE DATABASE orcasso_test; GRANT ALL PRIVILEGES ON orcasso_test.* TO 'orca'@'%'; FLUSH PRIVILEGES;"
```

Inside individual tests, `dama/doctrine-test-bundle` wraps each test in a transaction that's rolled back at the end — so DB writes from one test never leak into another, but the bootstrap rebuild gives a clean baseline across runs.

## Architecture notes

### Domain model

Core aggregates (see `src/Entity/`):

- **Member** — the person. Has `MemberDocument`s, `LegalRepresentative`s (for minors), and a `MemberLog` audit trail.
- **Order** → **OrderLine** → linked to **Activity** (priced offerings). Orders have statuses `pending|cancelled|validated`. **Payment** ↔ **Order** is many-to-many through **PaymentOrder** (one payment can settle several orders).
- **OrderForm** + **OrderFormField**(+ `OrderFormFieldChoice`) — admin-defined public registration forms. A public submission produces an **OrderFormReply**, which `App\Transformer\OrderFormReplyToOrder` turns into a real `Order`/`Member`.
- **Configuration** — singleton-ish app settings (association name, etc.) used in PDFs and emails.

Order/Payment totals are kept consistent by Doctrine listeners in `src/Listener/` (`CalculateOrderTotalAmountListener`, `CalculatePaymentAmountListener`) — do not also recalc manually in controllers.

### Audit logging

Member-related changes are captured via Gedmo Loggable. Entities that should be auditable implement `MemberLogObjectInterface` and use `#[Gedmo\Loggable(logEntryClass: MemberLog::class)]` plus `#[Gedmo\Versioned]` on tracked properties. `App\Utils\LoggableListener` (configured in `services.yaml`) overrides Gedmo's listener to attach the log entry to the correct `Member` via `getLogConcernedMember()`. `App\Utils\LogActorProvider` provides the current user as the log actor.

### Authorization model

`App\Entity\User::ROLES` defines granular per-area roles (`ROLE_ADMIN_MEMBER_EDIT`, `ROLE_ADMIN_ORDER_EDIT`, `ROLE_ADMIN_PAYMENT_EDIT`, etc.). Admin controllers gate access with `#[IsGranted(User::ROLE_ADMIN_*_EDIT)]` at the class level. The firewall (`config/packages/security.yaml`) only enforces `ROLE_USER` on `/admin/`; the fine-grained checks are entirely at the controller level — when adding a new admin controller, **explicitly add the role attribute**, the firewall will not catch missing ones.

### Routing convention

`config/routes.yaml` auto-loads `src/Controller/` from attributes; admin controllers in `src/Controller/Admin/` are loaded with a `/admin` route prefix on top of their own `#[Route]` attribute. Public-facing controllers (HomeController, OrderController, OrderFormReplyController) sit at the root level.

### Kilik table abstraction

Every admin list view is paired with a class implementing `App\Table\TableFactoryInterface` (in `src/Table/`). The interface contract: `getTableId()`, `getTable()` returning a configured `Kilik\TableBundle\Components\Table`, and `getExpectedRole()`.

- Factories are auto-tagged `app.table.factory` via `_instanceof` in `services.yaml` and collected into `TableFactoryCollection`.
- Two routes back each list: a non-ajax page (`admin_*_list`) that renders the form, and an ajax endpoint (`admin_*_list_ajax`) used by Kilik for pagination/filtering.
- `App\Table\TableExporter` + the generic `admin_table_export` route (`/admin/table/export-table/{id}`) provide ODS export for any registered factory, role-gated by `getExpectedRole()`.

When adding a new listing, follow the existing pattern in `MemberTableFactory` / `MemberController` — both the standard list route and the `_list_ajax` route must be wired, and the factory must declare the correct role.

### PDFs and external integrations

- `App\Utils\ReceiptPdfGenerator` (TCPDF-based) builds order receipt PDFs.
- `App\Utils\HelloAsso` calls the HelloAsso API (`HELLOASSO_API_URL`, sandbox URL in `.env.test`) — only touch this if working on the HelloAsso payment import flow.

### Member document storage

Uploaded member documents live on disk under `DIRECTORY_MEMBER_DOCUMENT_STORAGE` (defaults to `var/member_documents`, `/tmp/app-test-storage-member-documents` in tests). `RemoveMemberDocumentListener` ensures files are deleted when their entity is removed — never delete the entity's file manually.

## Tests

- `tests/Controller/AbstractWebTestCase` provides the standard helpers: `authenticateUser()`, `assertRedirectToLogin()`, `assertAccessDenied()`, `assertHasFlash()`, `assertHasHtmlTitle()`, `trans()`. Use these rather than rolling your own auth/translation setup.
- Default fixture user is `UserFixtures::USERS[0]`.
- Controller tests are the primary level of testing in this repo — there is no separate unit-test directory.
