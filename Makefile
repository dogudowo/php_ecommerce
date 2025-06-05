.PHONY: config update all help

.DEFAULT_GOAL := all

all: update config

help:
	@echo "可用指令:"
	@echo "  make help      - 顯示此幫助訊息"
	@echo "  make all       - 執行 update 與 config"
	@echo "  make config    - 檢查並創建 config.php"
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