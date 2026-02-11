#!/bin/sh
set -e

# storageディレクトリとbootstrap/cacheディレクトリの権限を設定
# rootユーザーで実行されている場合のみ権限を設定
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
    
    # PHP-FPMの場合、rootで起動する（PHP-FPMの設定でワーカープロセスはwww-dataで実行される）
    # それ以外のコマンドはwww-dataユーザーで実行
    if [ $# -eq 0 ] || [ "$1" = "php-fpm" ]; then
        # PHP-FPMをrootで起動（ワーカープロセスは設定ファイルでwww-dataに設定される）
        exec php-fpm
    else
        # その他のコマンドはwww-dataユーザーで実行
        exec su-exec www-data "$@"
    fi
else
    # 既にwww-dataユーザーの場合はそのまま実行
    if [ $# -eq 0 ]; then
        exec php-fpm
    else
        exec "$@"
    fi
fi
