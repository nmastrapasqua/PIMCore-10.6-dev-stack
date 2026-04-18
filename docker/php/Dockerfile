FROM php:8.1-fpm-bullseye

ARG HOST_UID=1000
ARG HOST_GID=1000

# Locale UTF-8 (richiesto da Pimcore per escapeshellarg)
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
    # Strumenti aggiuntivi raccomandati da Pimcore
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

# Estensioni PHP richieste da Pimcore 10.6
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        gd intl pdo_mysql mysqli zip opcache \
        mbstring xml dom simplexml fileinfo \
        exif curl bcmath soap

# Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Imagick
RUN apt-get update && apt-get install -y --no-install-recommends \
    libmagickwand-dev \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && rm -rf /var/lib/apt/lists/*

# Configurazione PHP ottimizzata per Pimcore
COPY docker/php/php.ini /usr/local/etc/php/conf.d/pimcore.ini

# Composer
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

# Remap www-data UID/GID all'utente host (per bind mount senza conflitti)
RUN usermod -u ${HOST_UID} www-data \
    && groupmod -g ${HOST_GID} www-data

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
