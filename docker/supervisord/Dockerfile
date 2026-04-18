FROM php:8.1-fpm-bullseye

ARG HOST_UID=1000
ARG HOST_GID=1000

# Locale UTF-8
RUN apt-get update && apt-get install -y --no-install-recommends locales \
    && sed -i '/en_US.UTF-8/s/^# //g' /etc/locale.gen \
    && locale-gen en_US.UTF-8 \
    && rm -rf /var/lib/apt/lists/*

ENV LANG=en_US.UTF-8 \
    LANGUAGE=en_US:en \
    LC_ALL=en_US.UTF-8

# Dipendenze di sistema
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    curl \
    wget \
    git \
    supervisor \
    cron \
    libzip-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxml2-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    libreoffice \
    pngquant \
    optipng \
    jpegoptim \
    webp \
    ghostscript \
    ffmpeg \
    exiftool \
    poppler-utils \
    libimage-exiftool-perl \
    && rm -rf /var/lib/apt/lists/*

# Estensioni PHP
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        gd intl pdo_mysql mysqli zip opcache \
        mbstring xml dom simplexml fileinfo \
        exif curl bcmath soap

RUN pecl install redis && docker-php-ext-enable redis

RUN apt-get update && apt-get install -y --no-install-recommends \
    libmagickwand-dev \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/php.ini /usr/local/etc/php/conf.d/pimcore.ini

# Remap www-data all'UID host
RUN usermod -u ${HOST_UID} www-data \
    && groupmod -g ${HOST_GID} www-data

# Prepara cron: crea directory necessarie e assicura che il demone funzioni
RUN mkdir -p /var/run /var/log \
    && touch /var/log/cron.log \
    && chmod 0644 /var/log/cron.log

# Copia il crontab con i permessi corretti (root:root, 644)
COPY --chown=root:root --chmod=644 docker/cron/pimcore-crontab /etc/cron.d/pimcore-crontab

WORKDIR /var/www/html

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
