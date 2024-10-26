FROM phpdockerio/php:8.3-fpm
WORKDIR "/app"

RUN apt-get update \
    && apt-get -y --no-install-recommends install \
        php8.3-http \
        php8.3-memcached \
        php8.3-mysql \
        php8.3-oauth \
        php8.3-odbc \
        php8.3-redis \
        php8.3-ssh2 \
        php8.3-uuid \
        php8.3-xdebug \
        php8.3-yaml \
        libcurl4-openssl-dev \
        php-pgsql \
        php8.3-pgsql \
        php8.3-mbstring \
        pkg-config \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
