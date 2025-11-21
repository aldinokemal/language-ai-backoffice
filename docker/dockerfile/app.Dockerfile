FROM registry.hydrogendioxide.net/languageai/backoffice:base AS composer_deps

WORKDIR /var/www/

# Install PHP dependencies (cached by composer files)
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --no-autoloader

FROM registry.hydrogendioxide.net/languageai/backoffice:base AS assets_builder

WORKDIR /var/www/

# Install Node dependencies (cached by lockfile) and build assets if present
COPY package.json package-lock.json* yarn.lock* pnpm-lock.yaml* ./
RUN mkdir -p public/build && if [ -f package.json ]; then npm ci --no-audit --no-fund; fi
COPY resources/ resources/
COPY Modules/ Modules/
COPY vite.config.js vite.config.js
RUN if [ -f package.json ]; then npm run build; fi

FROM registry.hydrogendioxide.net/languageai/backoffice:base

WORKDIR /var/www/

# Setup Nginx
RUN rm /etc/nginx/sites-enabled/default
COPY /docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy Logrotate Configuration
COPY /docker/logrotate/* /etc/logrotate.d/

# Set PHP ini
COPY /docker/php/php.ini "$PHP_INI_DIR/conf.d/application.ini"

# Supervisor configuration
COPY /docker/supervisor /etc/

# Copy application source
COPY . .

# Bring in built dependencies/artifacts from builders
COPY --from=composer_deps /var/www/vendor /var/www/vendor
COPY --from=assets_builder /var/www/public/build /var/www/public/build

# Remove any stale Laravel caches that may reference dev-only providers
RUN rm -rf bootstrap/cache/*.php || true

# Ensure writable directories BEFORE running composer scripts/artisan
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# Generate optimized autoloader and re-run package discovery (excludes dev packages)
RUN composer dump-autoload -o

# Ensure public/storage symlink for Laravel file uploads
RUN mkdir -p /var/www/storage/app/public \
    && rm -rf /var/www/public/storage \
    && ln -s /var/www/storage/app/public /var/www/public/storage

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

USER root
