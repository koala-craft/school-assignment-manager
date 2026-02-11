FROM php:8.2-fpm-alpine

# 必要なパッケージのインストール
RUN apk add --no-cache \
    postgresql-client \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    su-exec \
    $PHPIZE_DEPS

# PHP拡張機能のインストール
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    zip \
    pcntl \
    bcmath

# Redis拡張機能のインストール
RUN pecl install redis \
    && docker-php-ext-enable redis

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリの設定
WORKDIR /var/www

# PHP-FPM設定ファイルのカスタマイズ
# エラーログを標準エラー出力に設定し、ワーカーの出力もキャッチ
RUN sed -i 's/;error_log = log\/php-fpm.log/error_log = \/proc\/self\/fd\/2/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;catch_workers_output = yes/catch_workers_output = yes/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/user = nobody/user = www-data/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/group = nobody/group = www-data/' /usr/local/etc/php-fpm.d/www.conf

# エントリーポイントスクリプトのコピーと改行コード修正
COPY entrypoint.sh /tmp/entrypoint.sh
RUN sed 's/\r$//' /tmp/entrypoint.sh > /usr/local/bin/entrypoint.sh && \
    chmod +x /usr/local/bin/entrypoint.sh && \
    rm /tmp/entrypoint.sh

# 権限設定
RUN chown -R www-data:www-data /var/www

ENTRYPOINT ["/bin/sh", "/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

# デフォルトユーザーをrootに設定（エントリーポイントでwww-dataに切り替える）
# USER www-data
