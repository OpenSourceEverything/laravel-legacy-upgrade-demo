# Verification record

Keep failed attempts visible and distinguish automated HTTP/kernel tests,
real HTTP checks and database continuity tests.

## Verified checkpoints

| Framework | PHP | PHPUnit | HTTP regression result | Same-file data check |
| --- | --- | --- | --- | --- |
| 5.3.8 | 7.1.33 | 5.7.27 | 16 tests, 71 assertions pass | pass |
| 5.3.31 | 7.1.33 | 5.7.27 | 16 tests, 71 assertions pass | pass |
| 5.4.36 | 7.1.33 | 5.7.27 | 16 tests, 71 assertions pass | pass |
| 5.5.50 | 7.1.33 | 6.5.14 | 16 tests, 71 assertions pass | pass |
| 5.6.40 | 7.1.33 | 7.5.20 | 16 tests, 71 assertions pass | pass |
| 5.7.29 | 7.1.33 | 7.5.20 | 16 tests, 71 assertions pass | pass |
| 5.8.38 | 7.1.33 | 7.5.20 | 16 tests, 71 assertions pass | pass |
| 6.20.45 | 7.4.33 | 8.5.56 | 16 tests, 71 assertions pass | pass |
| 7.30.7 | 7.4.33 | 8.5.56 | 16 tests, 71 assertions pass | pass |
| 8.83.29 | 7.4.33 | 9.6.38 | 16 tests, 71 assertions pass | pass |
| 9.52.22 | 8.3.36 | 9.6.38 | 19 tests, 77 assertions pass | pass |
| 10.50.3 | 8.3.36 | 10.5.66 | 19 tests, 77 assertions pass | pass |
| 11.57.0 | 8.3.36 | 11.5.57 | 19 tests, 77 assertions pass | pass |
| 12.70.1 | 8.3.36 | 11.5.57 | 19 tests, 77 assertions pass | pass |

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

## Bridge runtime build

The first PHP 7.4 runtime build failed before Composer: bullseye-security
metadata referenced packages returning HTTP 404. The identical package was
available from Debian's official archive (HTTP 200). Applied the archive
mapping to this historical image as well as stretch. The rebuild and Laravel 6
checkpoint passed. Package signatures remain enabled; the expiry exception is
only for archived metadata, never the host's apt configuration.

## Laravel 9 dependency and data checks

Composer 2.10.3 refused the EOL Laravel 9 constraint because all matching
versions were affected by advisories. Temporarily disabled **advisory blocking**
in this demonstration's project configuration, not TLS, malware policy,
platform checks or advisory reporting. `composer audit` still returned a
failure with five advisories affecting Laravel. Remove this exception at the
supported target; the historical tag is deliberately not safe for deployment.

The initial 9.52.22 run passed 16 HTTP tests but failed the exact data comparison.
Field-by-field diagnosis found only known integer columns changing from PHP
strings to native integers on the new database/runtime path; their string
values were identical. No rows, timestamps, passwords or stock quantities
changed. Added narrowly scoped integer representation normalization to the
verification tool, with three unit tests proving real changes, floats and nulls
are not hidden. Did not edit the saved baseline. The rerun passed 19 tests /
77 assertions and reproduced the original canonical snapshot digest.

## PHPUnit 10 helper collision

The first Laravel 10 checkpoint stopped before tests ran: our private HTTP
helper `status()` collided with PHPUnit 10's final `TestCase::status()`.
Renamed the helper and its call sites to `assertHttpStatus`, preserving every
HTTP expectation. The rerun passed all 19 tests / 77 assertions with no warning
and unchanged saved data. This was a test-infrastructure compatibility failure,
not a change to the app's expected HTTP behavior.

## Supported checkpoint security gate

Removed the temporary advisory-blocking exception before resolving Laravel 12.
Resolution succeeded with default security enforcement and `composer audit`
reported no known advisories. This is a dependency audit, not a penetration test
or proof that the app is production-ready. The historical exception remains
visible in its Git checkpoints, not in supported-head configuration.
