# Upgrade plan and technical decisions

Target verified on 2026-10-23: Laravel 13 is the latest stable major; Packagist
lists 13.38.0, released 2026-10-20. Its supported PHP range is 8.3–8.5.
The original framework is exactly 5.3.8, not the last 5.3 patch.

Smallest credible application: login plus categories, items and stock movements, with
ownership checks, validation and a database that can be carried between versions.
SQLite avoids operating a separate database server. Plain Blade/CSS avoids
introducing a Node migration unrelated to the PHP upgrade.

Planned checkpoints: 5.3.8 → final 5.3 patch → 5.4 → 5.5 → 5.6 → 5.7 → 5.8 →
6 → 7 → 8 → 9 → 10 → 11 → 12 → 13. Every checkpoint must record the actual
resolved framework/PHP/PHPUnit versions, test outcome and data-preservation
result. A skipped version must be explicitly justified, not silently omitted.

Use the official guide for each version. Preserve application controllers,
models, views and migration history; replacing the app with a modern starter
would not demonstrate an upgrade. Keep the existing application structure when
the framework supports it. Commit lockfiles and tag verified checkpoints.

Primary sources:

- https://laravel.com/docs/releases
- https://laravel.com/docs/5.3/installation
- https://laravel.com/docs/5.4/upgrade (and the corresponding versioned guide)
- https://laravel.com/docs/13.x/upgrade
- https://repo.packagist.org/p2/laravel/framework.json
- https://getcomposer.org/download/

Composer 2.2 LTS can run on old PHP; Composer 1 is not a reproducible dependency
resolution strategy today. Composer PHARs are checksum-verified. Legacy
dependencies can contain known vulnerabilities; any security-policy exception
must be restricted to the historical demonstration and documented explicitly.

## 5.3 → 5.4

Updated within 5.3 first, then to 5.4.36. Cleared the compiled service cache
before changing dependencies and removed the obsolete database `fetch` setting;
5.4 consistently returns objects. The tests use the HTTP kernel rather than
removed BrowserKit helpers, so no compatibility testing package was needed.
Both checkpoints passed the unchanged regression and same-file data checks.

## 5.4 → 5.5

Moved Composer hooks from deprecated `optimize` to `postAutoloadDump` and
`package:discover`. Updated PHPUnit to 6 and Mockery to a PHP-compatible 1.x.
Kept cookie serialization disabled (the patched framework default). Whoops is
an optional development renderer, not needed with debug disabled. No mail,
Passport, broadcasting or BrowserKit package is used, so their guide sections
do not require application changes.

## 5.5 → 5.6

Added separate hashing and logging configuration, preserving bcrypt and the
existing log file. Upgraded PHPUnit to 7.5 with Symfony 4 framework components.
No custom Symfony classes, proxy package, mail customization or pagination is
used. Kept Blade's safer default escaping rather than disabling double encoding.

## 5.6 → 5.7

Disabled console-output mocking in the test fixture so `artisan('db:seed')`
continues to execute commands. Existing routes, templates and migrations do
not override changed framework methods, use Blade's removed `or` operator or
drop SQLite foreign keys. Error responses do not require the new starter SVGs.

## 5.7 → 5.8

Kept existing integer primary/foreign keys instead of rewriting old migrations
to match new `bigIncrements` stubs. There are no numeric cache TTL calls or
password-reset flows affected by changed defaults. Environment values contain
no unquoted `#`. PHPUnit 7 remains supported on the legacy PHP runtime; the
PHPUnit 8 jump is deferred until PHP is raised for Laravel 6.

## 5.8 → 6

Raised the container and Composer platform to PHP 7.4.33, then PHPUnit to 8.5.
Composer moved Carbon 1 to 2. Searched application/configuration for removed
string/array helpers and Input/Auth scaffolding calls; none required a helpers
or UI compatibility package. Kept existing owner-scoped queries. The same
SQLite file, including timestamps and password hashes, compared unchanged.

## 6 → 7

Changed the exception handler's report/render arguments from `Exception` to
`Throwable` for Symfony 5 compatibility. Updated the secure-cookie fallback to
null so request security can be inferred. No generated authentication package,
date JSON API or custom Symfony implementation needs conversion. Existing
HTML login, CSRF failures and owner-denial HTTP contracts still pass.

## 7 → 8

Moved DatabaseSeeder to `database/seeders` with its `Database\Seeders` namespace
and a PSR-4 mapping. Existing migrations retain their identities. There are no
legacy model factories to replace. The existing RouteServiceProvider explicitly
sets its controller namespace, so the optional new-project routing convention
does not require rewriting routes. Upgraded PHPUnit to 9.6 and replaced its
deprecated XML coverage schema; the warning disappeared on the rerun.

## 8 → 9

Raised PHP to the pinned 8.3 runtime (actual 8.3.36) and Composer to 2.10.3.
Laravel replaced SwiftMailer/Flysystem 1 with Symfony Mailer/Flysystem 3 through
dependency resolution. No application mail sending, uploads or custom storage
adapter is used; those integrations are not claimed as tested. Existing PHP
overrides already satisfy the framework contracts. See the verification log
for the explicit temporary security-policy exception and PDO representation
diagnosis; neither is hidden by resetting the database.

## 9 → 10

Upgraded PHPUnit to 10.5 and moved XML coverage inclusion to `source`. Resolved
a real helper-name collision (see verification log). Monolog 3 is provided by
the framework; the application's standard single-file channel needs no custom
handler adaptation. The project already uses stable dependencies and has no
legacy `$dates` model property or custom database expressions.
