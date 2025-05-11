.PHONY: config set_permissions all update

.DEFAULT_GOAL := all

all: config set_permissions

update:
	@git pull
	@echo "已從遠端倉庫更新最新代碼"

config:
	@if [ ! -f config.php ]; then \
		cp config.template.php config.php; \
		echo "config.php created from template"; \
	else \
		echo "config.php already exists, skipping"; \
		echo "Check backend orderMenager.php image url"; \
	fi

set_permissions:
	@find . -type f -exec chmod 777 {} \;
	@find . -type d -exec chmod 777 {} \;
	@chmod 666 .logs/error_log
	@echo "所有檔案權限已設置為777，error_log設置為666"