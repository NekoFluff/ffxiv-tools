FROM php:8.3-apache

RUN apt update && apt install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev
RUN apt clean && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
RUN pecl install xdebug redis pcov
RUN docker-php-ext-enable xdebug redis pcov

COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer
COPY --from=node:20 /usr/local/bin/node /usr/local/bin/node
COPY --from=node:20 /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm
RUN ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

RUN useradd --create-home --shell /bin/bash ffxivtools
USER ffxivtools:ffxivtools

CMD php artisan serve --host=0.0.0.0 --port=8000
EXPOSE 8000
