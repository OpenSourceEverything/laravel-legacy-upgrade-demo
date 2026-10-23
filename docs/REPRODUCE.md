# Reproduce the inventory upgrade

## Normal use

On Linux with Git, Docker, curl, Make and sha256sum available:

```sh
git clone https://github.com/OpenSourceEverything/laravel-legacy-upgrade-demo.git
cd laravel-legacy-upgrade-demo
make setup
make test
make data-check
make audit
make serve
```

Open http://localhost:8080 and sign in as `staff@example.test` with
`demo-password`. No real data or credentials. Stop with Ctrl+C. In another
terminal, `make smoke` checks the actual server without modifying stock.

Setup installs the exact lockfile in the selected PHP container, generates a
private local app key, creates/migrates SQLite, seeds synthetic data, and records
a local snapshot only if none exists. Reruns do not reset the database or replace
the snapshot. After normal inventory edits, `make data-check` is expected to
report a difference; it is deliberately not an ordinary uptime check.

## Replay every checkpoint on one database

The simplest option, from a clean checkout with all tags available:

```sh
make replay
```

This exports each committed version into a fresh directory under `/tmp`,
installs each lockfile into its own vendor tree, and runs tests/migrations/data
checks. Each stage hard-links the **same** disposable SQLite file and the first
stage's app key/snapshot. Later stages never reseed or replace it. A device/inode
check enforces file identity; a constant digest enforces row continuity.
The supported current stage must also pass Composer validation/audit.

It does not switch your branch, mount your ordinary `.env` or open your normal
inventory database. It refuses a dirty source checkout instead of pretending
uncommitted changes were tested. It stops at the first failed stage and retains
logs and a `summary.tsv` report for investigation. No automatic cleanup. Allow
several minutes and temporary disk space for historical vendor trees. Package
downloads are reused, not installed dependencies. No server is exposed.

The workflow **Replay every historical upgrade checkpoint** runs the same
procedure manually in Actions. The ordinary push workflow checks the endpoints.

### Manual alternative

Use a **new checkout**, not a working copy with your edits. Keep the app server
stopped while changing versions. These commands leave source at a detached
tag; this is intentional inspection, not a development branch.

```sh
git clone https://github.com/OpenSourceEverything/laravel-legacy-upgrade-demo.git inventory-upgrade-replay
cd inventory-upgrade-replay
git switch --detach baseline-5.3.8
make setup
./tools/run tools/snapshot.php --record
./tools/checkpoint

for checkpoint in upgrade-5.3 upgrade-5.4 upgrade-5.5 upgrade-5.6 upgrade-5.7 upgrade-5.8 upgrade-6 upgrade-7 upgrade-8 upgrade-9 upgrade-10 upgrade-11 upgrade-12 upgraded-13.38.0; do
  git switch --detach "$checkpoint" || break
  ./tools/run composer install --no-interaction --prefer-dist || break
  ./tools/checkpoint || break
done
make audit
```

Do not delete `.env`, `database/demo.sqlite` or `storage/baseline-data.json`
between stages. Do not run migrate:fresh, reset, reseed or restore a snapshot to
force a pass. If a command fails, stop and investigate; do not interpret a later
audit as proving all earlier checkpoints ran. Successful checkpoints must show
tests **and** the data-continuity digest. Initial snapshot hashes differ between
fresh setups because timestamps and salted password hashes are generated;
the hash must stay unchanged *within* a replay.

The historical 5.3–5.8 runtime uses PHP 7.1.33 / Composer 2.2.25. Laravel 6–8
uses PHP 7.4.33 / Composer 2.2.25. Laravel 9 onward uses pinned PHP 8.3 /
Composer 2.10.3. `tools/run` chooses automatically using `.runtime`; host PHP is
not changed. Images and Composer binaries are pinned/checksum-checked. Debian
apt package repositories are external availability dependencies, not a fully
hermetic snapshot of every OS package. Historical images use Debian's archive.

## Interpreting the evidence

- HTTP/kernel tests use real migrations and in-memory SQLite, not a browser.
- The persistent check reads real saved rows, validates database/foreign keys,
  and verifies each balance against its movement ledger.
- PDO numeric representations are normalized only for known integer columns;
  changed quantities, text, timestamps and hashes still fail comparison.
- Real HTTP smoke tests cover a server, cookies and CSRF, not visual rendering,
  concurrency, load, MySQL, deployment or a production security assessment.
- Regular GitHub Actions checks fresh baseline and current-head lockfile installs.
  The separate manual replay workflow runs all checkpoints on one disposable file.
  Local version-by-version results are in `VERIFICATION.md`; check Actions before
  claiming a remote CI pass.
