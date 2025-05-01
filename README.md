# MemeCoin API

A Laravel-based REST API for generating unique, creative MemeCoin names from a user's full name.

---

## 🚀 Features

- **POST `/api/memecoin/generate-name`**: Generates unique, crypto-inspired MemeCoin names using a fun transformation algorithm.
- **Authentication**: Secured endpoints using Laravel's authentication with API tokens.
- **Rate Limiting**: Restricts users to 20 requests per minute.
- **Logging & Auditing**: All attempts—success, duplicate, exhaustion—are logged via events.
- **Tested & Maintainable**: OLID principle, comprehensive feature tests.

---

## 🛠️ Getting Started

### 1. Prerequisites

- Docker
- Docker Compose  
Make sure ports `8080` (Nginx) and `33060` (MySQL) are available.

### 2. Clone the Repository

```bash
git clone <your-repo-url>
cd <repo-folder>
```

### 3. Environment Setup

`.env` is already included on this test app but if you prefer to start from scratch just Copy and configure your `.env` file:

```bash
cp .env.example .env
```

Update the `.env` database config:

```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=memecoin
DB_USERNAME=admin
DB_PASSWORD=admin
```

### 4. Running the Application with Docker

Build and start all containers:

```bash
docker-compose up -d --build
```

Services:

- **php**: Runs the Laravel app
- **nginx**: Serves at [http://localhost:8080](http://localhost:8080)
- **db**: MySQL 8.0 database

### 5. Install Dependencies & Run Migrations

Enter the PHP container:

```bash
docker exec -it memecoin_php bash
```

Then run:

```bash
composer install
php artisan key:generate
php artisan migrate
```

### 6. Create API Test User and Token

**Note**: This project does not include user registration and login endpoints by default. To test the API, create users and tokens manually in Tinker inside the container:

```bash
php artisan tinker
```

Then:

```php
// Create a test user
$user = \App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
]);

// Generate token
$token = $user->createToken('test-token')->plainTextToken;
$token
```

Use this token as your Bearer token in API requests.

### 7. Testing the Endpoint

**Endpoint**: `POST http://localhost:8080/api/memecoin/generate-name`  
**Headers**:
- `Authorization: Bearer <your_token_here>`
- `Accept: application/json`
- `Content-Type: application/json`

**Payload**:

```json
{
  "full_name": "John Michael Doe"
}
```

**Curl Example**:

```bash
curl -X POST http://localhost:8080/api/memecoin/generate-name \
     -H "Authorization: Bearer <your_token_here>" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -d '{"full_name": "John Michael Doe"}'
```

### 8. Run Test Suite

Inside PHP container:

```bash
php artisan test
php artisan test --coverage  # For more detail
```

---

## 📘 API Reference

### `POST /api/memecoin/generate-name`

| Field      | Type   | Required | Description               |
|------------|--------|----------|---------------------------|
| full_name  | string | Yes      | The user's full name input |

#### ✅ Success Response (HTTP 200)

```json
{
  "status": "success",
  "coin_name": "MoonJoMiDoToken",
  "message": "MemeCoin name generated!"
}
```

#### ❌ Error – Exhausted Attempts (HTTP 409)

```json
{
  "status": "error",
  "message": "No unique names available"
}
```

#### ⚠️ Validation Errors (HTTP 422)

```json
{
  "message": "The full name field is required.",
  "errors": {
    "full_name": [
      "The full name field is required."
    ]
  }
}
```
