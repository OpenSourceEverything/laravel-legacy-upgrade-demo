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
