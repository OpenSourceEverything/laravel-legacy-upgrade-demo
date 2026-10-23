# Verification record

Keep failed attempts visible and distinguish automated HTTP/kernel tests,
real HTTP checks and database continuity tests.

## Verified checkpoints

| Framework | PHP | PHPUnit | HTTP regression result | Same-file data check |
| --- | --- | --- | --- | --- |
| 5.3.8 | 7.1.33 | 5.7.27 | 16 tests, 71 assertions pass | pass |

Baseline data SHA-256:
`a3621af78d0a46339c962c83e40903b0be7b6008de0f5357342ede165c1172bb`.
The digest covers actual user/category/item/movement rows, IDs, timestamps and
password hashes in a synthetic local database. Raw rows are not published.
`tools/snapshot.php` also checks SQLite integrity, foreign-key references and
quantity = sum(stock movements). The nonempty snapshot is retained locally
and compared after each upgrade; it is not re-seeded or replaced to hide drift.

`make test` runs PHP's actual HTTP kernel and real in-memory SQLite migrations.
It includes an explicit CSRF test with Laravel's test-mode bypass switched off.
Browser, concurrency, MySQL and production/load qualification remain separate.

## Baseline bootstrap

The first Composer install resolved framework 5.3.8 and PHPUnit 5.7.27 but
failed extracting archives: the official PHP CLI image has neither ext-zip nor
an unzip executable, and source fallback also lacked Git. Added a small runtime
image installing unzip/Git. No application compatibility result was claimed
from successful dependency resolution alone.

The first HTTP regression run passed 14 tests and failed the seed-idempotency
test: Laravel 5.3's relationship `firstOrCreate` ignored the second values
argument, leaving required item fields unset. The Builder method accepts that
argument, but HasOneOrMany does not in this version. The seed now queries the
relationship first and creates with the complete attribute set. The failed
pre-inventory bootstrap database is retained locally, not called a baseline.

The next run passed 15 tests, but the persistent data check rejected an empty
movement ledger. A freshly created Eloquent instance did not hydrate the
database's quantity default, so the seed's strict zero check skipped opening
stock until its second invocation. Added explicit model defaults and strengthened
the test to assert opening stock after the **first** seed. This is why an
independent data check is required alongside a passing test suite.
