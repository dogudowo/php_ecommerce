.PHONY: config

config:
	@if [ ! -f config.php ]; then \
		cp config.template.php config.php; \
		echo "config.php created from template"; \
	else \
		echo "config.php already exists, skipping"; \
		echo "Check backend orderMenager.php image url"; \
	fi