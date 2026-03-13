FROM node:22-alpine AS vite_builder

WORKDIR /app

COPY package.json package-lock.json* pnpm-lock.yaml* bun.lockb* .npmrc* ./ 2>/dev/null || true
RUN npm install

COPY resources ./resources
COPY vite.config.js ./

RUN npm run build

FROM php:8.2-fpm-alpine AS app

RUN apk add --no-cache \
    bash \
    shadow \
    curl \
    libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    zlib-dev \
    mysql-client \
    git

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
       pdo \
       pdo_mysql \
       gd \
       zip \
       intl

WORKDIR /var/www/html

COPY --from=vite_builder /app/resources ./resources
COPY --from=vite_builder /app/public ./public 2>/dev/null || true

COPY composer.json composer.lock* ./
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php \
    && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

COPY . .

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]

