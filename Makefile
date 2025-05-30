.PHONY: config set_permissions all update help

.DEFAULT_GOAL := all

all: config set_permissions

help:
	@echo "可用指令:"
	@echo "  make help      - 顯示此幫助訊息"
	@echo "  make all       - 執行config與set_permissions指令"
	@echo "  make config    - 檢查並創建config.php"
	@echo "  make set_permissions - 設置檔案權限(777)與error_log權限(666)"
	@echo "  make update    - 從遠端倉庫更新代碼"

update:
	@git pull
	@echo "已從遠端倉庫更新最新代碼"

config:
	@if [ ! -f config.php ]; then \
		cp config.template.php config.php; \
		chmod 666 config.php; \
		echo "config.php created from template"; \
	else \
		echo "config.php already exists, skipping"; \
	fi

set_permissions:
	@find . -type f -exec chmod 777 {} \;
	@find . -type d -exec chmod 777 {} \;
	@mkdir -p .logs
	@if [ ! -f .logs/error_log ]; then touch .logs/error_log; fi
	@chmod 666 .logs/error_log
	@echo "所有檔案權限已設置為777，error_log設置為666"