# PHP-FPM の公式イメージをベースにする
FROM php:8.2-fpm

# 必要なパッケージをインストール
RUN apt update \
  && apt install -y default-mysql-client zlib1g-dev libzip-dev unzip \
  && docker-php-ext-install pdo_mysql zip

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && composer self-update
  
# php.ini を適用
COPY php.ini /usr/local/etc/php/

# 作業ディレクトリを設定
WORKDIR /var/www/

# 権限の変更
RUN chown -R www-data:www-data /var/www/html