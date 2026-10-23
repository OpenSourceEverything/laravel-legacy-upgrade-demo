# Laravel Legacy Upgrade Demonstration

A small inventory application upgraded from **Laravel 5.3.8 to 13.38.0**
incrementally while preserving behavior and SQLite data. This is
a migration exercise, not a new modern application presented as an upgrade.

The baseline passes 16 tests / 71 assertions; current head passes
22 tests / 98 assertions (preserved HTTP contracts, stronger transaction/security
checks and three checks of the data-comparison tool). The tagged upgraded
endpoint passed 19 tests / 77 assertions. Every intervening checkpoint was tested.
See [exact results and encountered failures](docs/VERIFICATION.md).
Fresh baseline/current checks also passed in [GitHub Actions](https://github.com/OpenSourceEverything/laravel-legacy-upgrade-demo/actions).

## Local setup

Requires Git, Docker, curl, Make and sha256sum on Linux. PHP runs inside a
version-selected container; nothing is installed into the host PHP environment.

```sh
git clone https://github.com/OpenSourceEverything/laravel-legacy-upgrade-demo.git
cd laravel-legacy-upgrade-demo
make setup
make test
make version
make serve
```

Open http://localhost:8080. The server binds to loopback only. Historical PHP
and Laravel are unsupported; use synthetic data and never expose the legacy app
to a network. Tests run with networking disabled. Dependency installation needs
network access. Do not use this demonstration as a production template.

Local synthetic login: `staff@example.test` / `demo-password`.

Stop the server with Ctrl+C. In a second terminal, `make smoke` checks real
HTTP login, session cookies, inventory rendering and missing-token rejection.

## Try the inventory app

1. Sign in and open the seeded brass hinge: 12 units on hand.
2. Record `5` with reason `Received delivery`; the balance becomes 17.
3. Record `-2` with reason `Used on service job`; it becomes 15.
4. Try `-100`: it must be rejected without changing balance/history.
5. Add a category and an item; edit its name, SKU and unit price in cents.
6. Sign out; inventory access must require login again.

`make data-check` compares rows against a local snapshot recorded on first
setup. It is an upgrade/replay check, not an app health check: after you change
inventory, a mismatch is expected. It never overwrites the baseline to hide drift.

## Inspect or replay the migration

- Original: tag `baseline-5.3.8` (PHP 7.1.33).
- Supported upgrade: tag `upgraded-13.38.0` (PHP 8.3.36).
- Intermediate stages: `upgrade-5.3` through `upgrade-12`.
- [Reproduce the complete upgrade](docs/REPRODUCE.md), carrying the same database.
- [Upgrade decisions](docs/UPGRADE.md), including steps that did not apply.

Use the lockfile (`composer install`) to reproduce a checkpoint. `composer
update` resolves a new dependency set and is an intentional maintenance step,
not routine setup. Composer runs in Docker through `tools/run`; no host PHP,
Composer, Python venv or Node environment is required.

`make replay` automates all historical checkpoints plus current committed head
in disposable non-Git exports. It leaves this checkout, its key and its database
untouched. Tests compare one shared synthetic SQLite file across every stage;
logs and a summary are retained in the reported temporary directory.

The baseline uses the official Laravel application scaffold at v5.3.0
(f5dfa2057e800e15ecc1f5609016dfbc08fc643e), with framework **5.3.8** pinned
separately. Laravel and its application scaffold are MIT licensed; attribution
is retained in [LICENSE](LICENSE).

## Scope

Staff login, per-user categories and items, CRUD, stock-adjustment history,
relationships, validation, authorization, Blade views and regression tests.
Quantities are whole units; adjustments must not make stock negative.
No payments, barcode scanning, suppliers/purchase orders, fractional quantities,
email delivery, Node build or hosted infrastructure. SQLite is the verified
database; MySQL parity is outside the initial scope.

## Evidence and boundaries

- [Upgrade decisions and sequence](docs/UPGRADE.md)
- [Verification and failures](docs/VERIFICATION.md)
- [Reproduction instructions](docs/REPRODUCE.md)

The supported head passes Composer validation and a known-advisory audit.
Historical tags intentionally retain obsolete dependencies; modern Composer's
advisory-blocking exception used for Laravel 9–11 is removed from supported
head. Passing tests/audit do not establish production security or multi-user
concurrency. SQLite does not provide MySQL-style row locks. There is no
public registration, password reset, login rate limiting or production deployment
configuration in this local demonstration. Use only the synthetic fixture.

The goal is to demonstrate engineering judgment and learning through a real
framework upgrade. It does not establish years of Laravel/Symfony experience,
production readiness, or an estimate for an unseen client application.
