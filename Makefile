.PHONY: setup test serve version audit data-check
setup:
	bash tools/bootstrap
test:
	./tools/run vendor/bin/phpunit
serve:
	./tools/run serve
version:
	./tools/run --version
	./tools/run artisan --version
audit:
	./tools/run composer audit
data-check:
	./tools/run tools/snapshot.php
