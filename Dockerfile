FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    netcat-openbsd \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --optimize-autoloader --no-interaction

RUN useradd -m -u 1000 -s /bin/bash appuser || true
RUN chown -R appuser:appuser /app

USER appuser

EXPOSE 8080

ENTRYPOINT ["bash", "/app/docker/entrypoint.sh"]
CMD ["php", "-S", "0.0.0.0:8080", "router.php"]