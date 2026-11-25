# ThreatMetrics

ThreatMetrics is a Laravel application for collecting, reviewing, and reporting on cyber threat indicators. It combines manually maintained threat records with indicators from TweetFeed in one dashboard.

## Features

- Filter dashboard metrics and charts by date range.
- Search, filter, and browse threat records in a paginated table.
- Create, edit, and delete threat records, and add analysis notes.
- Import CSV or XLSX files with a per-row report for accepted, duplicate, and invalid records.
- Export filtered records as CSV, XLSX, or an SPSS package. Download an individual threat report as a PDF.
- Refresh indicators from TweetFeed.
- View your profile, change your password, and sign out from **Pengaturan Akun** (Account Settings).

## Access

| Action | User | Admin |
| --- | --- | --- |
| View the dashboard, threat list, and record details | Yes | Yes |
| Export filtered records and download threat PDFs | Yes | Yes |
| Create, edit, or delete threat records | | Yes |
| Import records and download import templates | | Yes |
| Refresh TweetFeed | | Yes |
| View your own profile, change your password, and sign out | Yes | Yes |

New registrations receive the `user` role. To provision an admin for a local setup, register the intended account first. Then, using an authorized database account, set the `role` column on that specific row in the `users` table to `admin`.

## Local setup

Use PHP 8.3.33 or a compatible PHP 8.x release, Composer 2, and Git. For the frontend build, use Node.js 20.19+ within the 20.x line or Node.js 22.12+ with npm. Enable the PHP database driver for SQLite or MySQL and the extensions required by Composer, including GD and ZIP.

Clone the project and create a local environment file. These commands use PowerShell.

```powershell
git clone https://github.com/jeniiijen/ThreatMetrics-Kelompok10.git
Set-Location ThreatMetrics-Kelompok10
Copy-Item .env.example .env
```

The default environment uses SQLite. Create the database file if it is missing.

```powershell
if (-not (Test-Path database/database.sqlite)) {
    New-Item -ItemType File database/database.sqlite
}
```

To use MySQL, create an empty database and set `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`.

Install the dependencies, initialize the app, and start the development server.

```powershell
composer install
php artisan key:generate
php artisan migrate
npm ci
npm run build
php artisan serve
```

Open the address printed by Artisan (typically `http://127.0.0.1:8000`) and register an account. To load sample threat records into a fresh local database, run `php artisan db:seed` once. The seeder creates threat records only.

TweetFeed refresh requires outbound access to `api.tweetfeed.live`. The current integration uses the public endpoint and has no API key setting.

## Import format

Admins can download a template from the **Import** dialog in Threat Records. Upload a CSV or XLSX file of up to 5 MB and 5,000 data rows.

- Required columns are `date`, `user`, `type`, and `value`.
- Optional columns are `tags` and `tweet`. The app generates each threat ID.
- Supported indicator types are `MD5`, `SHA256`, `DOMAIN`, `URL`, and `IP`.

```csv
date,user,type,value,tags
2026-11-25 10:00:00,Analyst,DOMAIN,example.com,#phishing
```

## Tests

```powershell
php vendor/bin/pest
```

The test suite uses an in-memory SQLite database. Enable the PHP SQLite driver even if the development app uses MySQL.
