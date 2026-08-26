FROM php:8.3-cli

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN useradd -u $UID -g $GID -m appuser || true
RUN chown -R appuser:appuser /app

USER appuser

EXPOSE 8080

ENTRYPOINT ["bash", "/app/docker/entrypoint.sh"]
CMD ["php", "yii", "serve", "--host=0.0.0.0", "--port=8080"]