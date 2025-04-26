.PHONY: deploy setup-permissions setup-config update

# 主要部署指令，執行所有必要步驟
deploy: setup-permissions setup-config

# 設置目錄權限
setup-permissions:
	chmod -R 755 .

# 複製配置文件（僅當目標文件不存在時）
setup-config:
	@if [ ! -f php/config.php ]; then \
		cp php/config.template.php php/config.php; \
		echo "config.php 已創建"; \
	else \
		echo "config.php 已存在，跳過複製"; \
	fi

# 更新代碼
update:
	git pull --rebase

# 顯示幫助信息
help:
	@echo "可用的指令："
	@echo "  make deploy          - 執行所有部署步驟（權限設置和配置文件複製）"
	@echo "  make setup-permissions - 僅設置目錄權限"
	@echo "  make setup-config    - 僅複製配置文件（如果不存在）"
	@echo "  make update         - 使用 rebase 方式更新代碼"