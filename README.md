# Laravel Legacy Upgrade Demonstration

A small inventory application, beginning at **Laravel 5.3.8**
and upgraded incrementally while preserving behavior and SQLite data. This is
a migration exercise, not a new modern application presented as an upgrade.

The 5.3.8 baseline has 16 passing regression tests. Migration is in progress;
actual checkpoints are recorded in [the verification log](docs/VERIFICATION.md).

## Local setup

Requires Git, Docker, curl, Make and sha256sum on Linux. PHP runs inside a
version-selected container; nothing is installed into the host PHP environment.

```sh
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

The baseline uses the official Laravel application scaffold at v5.3.0
(f5dfa2057e800e15ecc1f5609016dfbc08fc643e), with framework **5.3.8** pinned
separately. Laravel and its application scaffold are MIT licensed; attribution
is retained in [LICENSE](LICENSE).

## Scope

Staff login, per-user categories and items, CRUD, stock-adjustment history,
relationships, validation, authorization, Blade views and regression tests.
Quantities are whole units; adjustments must not make stock negative.
No payments, calendar service,
email delivery, Node build or hosted infrastructure. SQLite is the verified
database; MySQL parity is outside the initial scope.

## Evidence and boundaries

- [Upgrade decisions and sequence](docs/UPGRADE.md)
- [Verification and failures](docs/VERIFICATION.md)

The goal is to demonstrate engineering judgment and learning through a real
framework upgrade. It does not establish years of Laravel/Symfony experience,
production readiness, or an estimate for an unseen client application.
