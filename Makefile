.PHONY: setup test serve version audit data-check smoke replay
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
smoke:
	bash tools/http-smoke
replay:
	bash tools/replay
