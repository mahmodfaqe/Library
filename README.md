# College of Science Electronic Library

The electronic library of the College of Science, University of Raparin —
a Laravel application serving a public catalogue of scientific resources in
eight languages, with a small admin panel for staff.

Production: **https://library.uor.edu.krd**

---

## Requirements

| | Version |
|---|---|
| PHP | 8.3 or newer (`sqlite3`, `pdo_sqlite`, `dom`, `mbstring`) |
| Composer | 2.x |
| Node.js | 22 or newer (build only — not needed at runtime) |
| Web server | Nginx or Apache with the document root at `public/` |

The database is SQLite; no database server is required.

### Why the PHP version is pinned in `composer.json`

`config.platform.php` is set to `8.3.0`. Composer resolves dependencies as if
it were running on PHP 8.3 even when the machine has something newer, so the
lock file always installs on the oldest version the project claims to support.

Without it, a developer on PHP 8.5 locks Symfony packages that require 8.4+,
and the install then fails on any 8.3 server — including CI. If the university
server is confirmed to run a newer PHP, raise this value and run
`composer update`; do not simply delete it.

---

## First install

```bash
git clone <repository> library && cd library

composer install --no-dev --optimize-autoloader
npm ci && npm run build          # writes public/build — required, see below

cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --class=DepartmentSeeder   # optional starter departments

php artisan admin:create         # creates the first administrator account
```

`public/build` is **not** in version control. Without `npm run build` the site
loads with no stylesheet at all.

Writable by the web server: `storage/` and `bootstrap/cache/`.

---

## Configuration

Beyond the standard Laravel keys, `.env` carries the project's own settings.
They exist so the site can be moved onto university-owned accounts without
touching code — see `config/library.php`.

| Key | What it is |
|---|---|
| `APP_URL` | `https://library.uor.edu.krd` |
| `APP_FALLBACK_LOCALE` | `ku-sorani` — the Kurdish variants fall back to it |
| `LIBRARY_DRIVE_MAIN` | Google Drive folder behind "General Library 1" |
| `LIBRARY_DRIVE_SECONDARY` | Folder behind "General Library 2" |
| `LIBRARY_QR_URL` | Where the QR-code link points |
| `LIBRARY_UNIVERSITY_URL` | The university site linked in the footer |
| `LIBRARY_ANALYTICS_HOST` | Visitor counter host. **Leave empty to disable** — no third-party request is then made, and the counter disappears from the page |
| `LIBRARY_FEEDBACK_RETENTION_DAYS` | How long visitor messages are kept. The privacy notice quotes this number |

After changing `.env` on the server:

```bash
php artisan config:clear
```

---

## Deploying an update

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:clear && php artisan view:clear
```

The home page cache invalidates itself: its filename carries a stamp of the
template, the translation file and the asset build, so a deploy is picked up
without a manual clear.

---

## Scheduled work

One cron entry runs everything:

```cron
* * * * * cd /path/to/library && php artisan schedule:run >> /dev/null 2>&1
```

| Task | When | What it does |
|---|---|---|
| `backup:database` | 02:30 daily | Copies the database into `storage/backups`, keeping 14 days |
| `feedback:prune` | 03:00 daily | Deletes visitor messages past the retention period |

`storage/backups` is excluded from version control. **Copy it off the server**
— a backup that lives only on the machine it protects is not a backup.

---

## Languages

Eight locales, each at its own URL so search engines can index all of them:

```
/                 کوردی سۆرانی   (the default; /ku-sorani redirects here)
/ku-badini        کوردی بادینی
/ku-badini-lat    Kurmancî (Latînî)
/ku-hawrami       کوردی هەورامی
/en /ar /fa /tr
```

Text lives in `lang/<locale>/messages.php` and carries **no markup** — layout
belongs to the Blade templates. Where a sentence needs a link or emphasis
inside it, the translation holds a `:placeholder` and the template supplies
the markup through `App\Support\RichText`.

Adding a locale: add it to `App\Support\Locale::SUPPORTED`, give it a BCP 47
tag in `LANGUAGE_TAGS`, and add `lang/<locale>/messages.php`. The test suite
will tell you if any key is missing.

> The Hawrami translation was drafted without a Hawrami speaker and is marked
> for review. Treat it as provisional.

---

## Admin panel

`/admin` — sign in with an email and password.

| Role | May do |
|---|---|
| `admin` | Everything, including managing accounts and reading the audit trail |
| `staff` | Manage departments and read feedback |

Every change is written to the `activity_log` table and shown at
`/admin/activity`, including sign-ins and failed attempts. The record keeps the
actor's name, so it still reads correctly after an account is removed.

Accounts are created from `/admin/users`, or on the command line:

```bash
php artisan admin:create
```

---

## Tests

```bash
php artisan test
```

The suite covers all eight locales, the page cache, the admin panel and roles,
the feedback form, and the translation files themselves — including a guard
that no personal contact details reach the public page. CI runs it on every
push (`.github/workflows/ci.yml`).

---

## How the pieces fit

| Path | What lives there |
|---|---|
| `app/Support/Locale.php` | Locale list, text direction, hreflang tags, per-locale URLs |
| `app/Support/RichText.php` | Splices trusted markup into a translated sentence |
| `app/Support/Asset.php` | Public asset URLs stamped with the file's mtime |
| `app/Http/Middleware/SetLocale.php` | URL decides the language; session remembers it for `/admin` |
| `app/Http/Middleware/CachePage.php` | Home-page cache, keyed by locale, host and source stamp |
| `app/Http/Middleware/SecurityHeaders.php` | CSP and the rest, driven by `config/library.php` |
| `config/library.php` | Every third-party URL the site depends on |
| `resources/css/app.css` | All site styling; compiled by Vite with Tailwind |

---

## Credits

Built by the **BioNova** team — students of the Biology Department, College of
Science, University of Raparin.
