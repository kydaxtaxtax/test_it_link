# Car Advertisement Service

REST API сервис для управления объявлениями автомобилей.

## Стек

- **PHP 8.3+**, **Yii2** (framework)
- **PostgreSQL** (база данных)
- **Nginx** (web-сервер)
- **Docker / Docker Compose** (контейнеризация)

## Архитектура

Многослойная архитектура с применением паттернов:

- **Entity** — доменные объекты (`CarEntity`, `CarOptionEntity`)
- **DataMapper** — ActiveRecord модели (`Car`, `CarOption`)
- **Repository** — инкапсуляция доступа к данным (`CarRepository`)
- **Service** — бизнес-логика (`CarService`)
- **Validator** — стандартизированная валидация входных данных
- **BaseController** — общая обёртка REST-ответов (200/201/400/422/404/500)
- **Dependency Injection** — контейнер Yii2

## Запуск через Docker

> :warning: **.env в репозитории — ДЛЯ ДЕМО.** В реальном проекте .env должен быть в .gitignore и генерироваться через secrets CI/CD. Никогда не комитьте реальные секреты!

```bash
# Сборка и запуск всех сервисов
docker-compose up -d --build

# Проверка статуса (все сервисы должны быть running/healthy)
docker-compose ps

# Логи
docker-compose logs -f app

# Остановка
docker-compose down
```

Приложение доступно по адресу: `http://localhost:80`

**При первом запуске автоматически:**
- Создаётся база данных `car_ad_db`
- Выполняются миграции (`php yii migrate/up --interactive=0`)

## Локальный запуск (без Docker)

### Требования

- PHP 8.3+ с расширениями `pdo`, `pdo_pgsql`, `mbstring`, `json`
- Composer 2.x
- PostgreSQL 14+
- Nginx (опционально)

### Установка

```bash
# Клонирование и установка зависимостей
git clone <url>
cd car-ad-service
composer install

# Настройка .env (DB_DSN, DB_USER, DB_PASSWORD)
cp .env.example .env  # или отредактируйте .env вручную

# Создание базы данных
createdb car_ad_db

# Миграции
php yii migrate/up

# Запуск dev-сервера
php yii serve --host=0.0.0.0 --port=8080
```

## API

### POST /car/create

Создание объявления.

**Request body:**
```json
{
  "title": "BMW X5 2020",
  "description": "Отличное авто в отличном состоянии",
  "price": 5500000.00,
  "photo_url": "https://example.com/bmw.jpg",
  "contacts": "+7 999 123-45-67",
  "options": {
    "brand": "BMW",
    "model": "X5",
    "year": 2020,
    "body": "кроссовер",
    "mileage": 50000
  }
}
```

**Response:** `201 Created`

```json
{
  "id": 1,
  "title": "BMW X5 2020",
  "description": "Отличное авто в отличном состоянии",
  "price": 5500000.0,
  "photo_url": "https://example.com/bmw.jpg",
  "contacts": "+7 999 123-45-67",
  "created_at": "2026-08-26 14:00:00",
  "options": {
    "brand": "BMW",
    "model": "X5",
    "year": 2020,
    "body": "кроссовер",
    "mileage": 50000
  }
}
```

### GET /car/{id}

Получение объявления по ID.

**Response:** `200 OK` с данными объявления или `404 Not Found`.

### GET /car/list?page=1

Список объявлений с пагинацией.

**Response:**
```json
{
  "items": [...],
  "pagination": {
    "page": 1,
    "pageSize": 20,
    "totalCount": 50,
    "pageCount": 3
  }
}
```

## Тесты

```bash
# Все тесты
./vendor/bin/phpunit

# Только unit
./vendor/bin/phpunit tests/unit

# Только API
./vendor/bin/phpunit tests/api
```

## Структура проекта

```
├── config/          # Конфигурация приложения
│   ├── common.php   # Общая конфигурация
│   ├── db.php       # Подключение к БД
│   ├── di.php       # DI-контейнер
│   ├── routes.php   # REST маршруты
│   ├── web.php      # Web-конфигурация
│   └── console.php  # Console-конфигурация
├── controllers/     # REST контроллеры
│   ├── BaseController.php
│   └── CarController.php
├── entities/        # Доменные сущности
│   ├── CarEntity.php
│   └── CarOptionEntity.php
├── exceptions/      # Кастомные исключения
│   └── ValidationException.php
├── mappers/         # DataMapper (AR <-> Entity)
│   └── CarMapper.php
├── migrations/      # Миграции БД
├── models/          # ActiveRecord модели
│   ├── Car.php
│   └── CarOption.php
├── repositories/    # Репозитории
│   ├── CarRepository.php
│   └── CarRepositoryInterface.php
├── services/        # Бизнес-логика
│   └── CarService.php
├── validation/      # Валидаторы входных данных
│   ├── ValidatorInterface.php
│   ├── AbstractValidator.php
│   ├── CarCreateValidator.php
│   └── CarOptionsValidator.php
├── tests/           # Тесты
├── docker/          # Docker файлы
├── web/             # Точка входа web
└── yii              # Console entry point
```