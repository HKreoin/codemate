# Codemate - Приложение для работы с балансом пользователей

Laravel API приложение для управления балансом пользователей с поддержкой операций зачисления, списания и переводов между пользователями.

## Требования

-   PHP 8.2+
-   Docker и Docker Compose
-   PostgreSQL

## Установка и запуск

1. Клонируйте репозиторий:

```bash
git clone <repository-url>
cd codemate
```

2. Скопируйте `.env.example` в `.env` (если нужно):

```bash
cp .env.example .env
```

3. Запустите Docker контейнеры:

```bash
docker compose up -d
```

4. Установите зависимости:

```bash
docker compose exec app composer install
```

5. Сгенерируйте ключ приложения:

```bash
docker compose exec app php artisan key:generate
```

6. Выполните миграции:

```bash
docker compose exec app php artisan migrate
```

7. Приложение будет доступно по адресу: `http://localhost:8080`

## API Endpoints

### 1. Начисление средств пользователю

**POST** `/api/deposit`

```json
{
    "user_id": 1,
    "amount": 500.0,
    "comment": "Пополнение через карту"
}
```

**Ответ (200):**

```json
{
    "user_id": 1,
    "balance": 500.0
}
```

### 2. Списание средств

**POST** `/api/withdraw`

```json
{
    "user_id": 1,
    "amount": 200.0,
    "comment": "Покупка подписки"
}
```

**Ответ (200):**

```json
{
    "user_id": 1,
    "balance": 300.0
}
```

**Ошибка (409) - недостаточно средств:**

```json
{
    "message": "Insufficient funds"
}
```

### 3. Перевод между пользователями

**POST** `/api/transfer`

```json
{
    "from_user_id": 1,
    "to_user_id": 2,
    "amount": 150.0,
    "comment": "Перевод другу"
}
```

**Ответ (200):**

```json
{
    "from_user_id": 1,
    "to_user_id": 2,
    "from_balance": 150.0,
    "to_balance": 150.0
}
```

**Ошибка (409) - недостаточно средств:**

```json
{
    "message": "Insufficient funds"
}
```

### 4. Получение баланса пользователя

**GET** `/api/balance/{user_id}`

**Ответ (200):**

```json
{
    "user_id": 1,
    "balance": 350.0
}
```

**Ошибка (404) - пользователь не найден:**

```json
{
    "message": "User not found"
}
```

## Коды ошибок

-   **200** - Успешный ответ
-   **400/422** - Ошибки валидации
-   **404** - Пользователь не найден
-   **409** - Конфликт (недостаточно средств)

## База данных

Проект использует PostgreSQL. Структура базы данных:

-   **users** - пользователи системы
-   **balances** - балансы пользователей (создаётся автоматически при первом пополнении)
-   **transactions** - история транзакций с типами: `deposit`, `withdraw`, `transfer_in`, `transfer_out`

## Особенности реализации

-   Все денежные операции выполняются в транзакциях БД
-   Баланс не может быть отрицательным
-   Используется блокировка строк (`lockForUpdate`) для предотвращения race conditions
-   Автоматическое создание записи баланса при первом пополнении
-   Все ответы и ошибки в формате JSON

## Тестирование

Запуск тестов:

```bash
docker compose exec app php artisan test
```

## Структура проекта

```
app/
├── Http/
│   ├── Controllers/
│   │   └── BalanceController.php
│   └── Requests/
│       ├── DepositRequest.php
│       ├── WithdrawRequest.php
│       └── TransferRequest.php
├── Models/
│   ├── Balance.php
│   ├── Transaction.php
│   └── User.php
└── Services/
    └── BalanceService.php
```

## Разработка

Для разработки можно использовать команды:

```bash
# Войти в контейнер PHP
docker compose exec app bash

# Выполнить миграции
docker compose exec app php artisan migrate

# Запустить тесты
docker compose exec app php artisan test

# Очистить кэш
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
```

## Лицензия

MIT License
