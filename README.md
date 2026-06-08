# ClubManager API

![Laravel](https://img.shields.io/badge/laravel-323330.svg?style=for-the-badge&logo=laravel&logoColor=23FF2D20)
![PHP](https://img.shields.io/badge/php-323330.svg?style=for-the-badge&logo=php&logoColor=23777BB4)
![MySQL](https://img.shields.io/badge/mysql-323330.svg?style=for-the-badge&logo=mysql&logoColor=20BEFF)

## 📖 Overview

ClubManager API is a SaaS platform designed to manage sports clubs, courts, availability schedules and pricing rules.

The platform provides a multi-role architecture that separates responsibilities between platform administrators, club administrators and end users, allowing sports organizations to manage their facilities through a centralized system.

### Current Status

🚧 Active Development

Implemented modules focus on platform administration and club management. End-user reservation features are currently under development.

---

## ✨ Implemented Features

###  Authentication

* User registration
* Login
* Email verification
* Password recovery
* Laravel Sanctum authentication

### Platform Administration

* Manage administrators
* Manage sport types
* Manage court features
* Activate and deactivate resources

### Club Administration

* Manage clubs
* Manage courts
* Configure availability schedules
* Configure pricing rules
* Manage club users

### Scheduling

* Court availability management powered by Laravel Zap

### Profile Management

* View profile information
* Account deletion

---

## 👥  System Roles

### SaaS Administrator

Responsible for:

* Platform configuration
* Administrator management
* Sport type management
* Court feature management

### Club Administrator

Responsible for:

* Club management
* Court management
* Pricing management
* Availability management
* Club user management

### Application User

Under development.

Future releases will include reservation management, booking history and player-oriented features.

---

## 🏗️ Architecture

The project follows an API-first architecture with clear separation of responsibilities.

### Architectural Principles

* RESTful API Design
* Service Layer Pattern
* Thin Controllers
* Request Validation through Form Requests
* Policy Based Authorization
* Resource Responses
* Feature-Oriented Development
* Automated Quality Enforcement

### Application Structure

* Controllers handle HTTP concerns only
* Business logic is encapsulated in Services
* Validation is handled by Form Requests
* Authorization is handled through Policies
* Responses are standardized using API Resources

---

## 🌐 API Design

The API follows REST principles and provides:

* JSON responses
* Consistent error handling
* HTTP status code standards
* Token authentication using Sanctum
* Request validation
* Resource serialization

### Example Endpoints

The project organizes API routes by domain prefixes mounted in `bootstrap/app.php`:

* `/api/admin`  — SaaS administration
* `/api/club`   — Club administration
* `/api/app`    — Application (end) users

Below are representative endpoints that match the current implementation and the HTTP examples under `phpstorm_request/`.

Administration (SaaS admin)
```text
POST  /api/admin/login
GET   /api/admin/profile
GET   /api/admin/users
PUT   /api/admin/users/{user}
```

Club administration (club users)
```text
POST    /api/club/register
POST    /api/club/login
GET     /api/club/clubs
POST    /api/club/clubs
PUT     /api/club/clubs/{club}
DELETE  /api/club/clubs/{club}
POST    /api/club/clubs/{club}/courts
PATCH   /api/club/clubs/{club}/courts/{court}/toggle-availability
POST    /api/club/clubs/{club}/courts/{court}/price-rules
GET     /api/club/clubs/{club}/courts/{court}/price-rules
```
Application users
```text
POST  /api/app/register
POST  /api/app/login
```

Notes:
- Many endpoints require Sanctum authentication and domain-specific middleware (e.g. `ensure_is_admin_user`, `ensure_is_club_user`).
- Courts are nested under clubs (`/api/club/clubs/{club}/courts`) rather than exposed at a top-level `/api/courts` path.
- The repo contains executable HTTP request examples under `phpstorm_request/Application/` that you can import into PHPStorm or run with compatible HTTP clients.

---

## ⚙️ Technology Stack

### Backend

* PHP 8.5
* Laravel 12
* Laravel Sanctum
* MySQL

### Scheduling

* Laravel Zap

### Development

* Docker
* Laravel Sail

### Continuous Integration

* GitHub Actions

---

## ✅ Quality Standards

The project enforces multiple quality gates to maintain consistency and reliability.

### Automated Quality Gates

* Pest Test Suite
* Architecture Tests
* PHPStan / Larastan
* Laravel Pint
* Rector
* Type Coverage Validation

### Architecture Validation

Architecture tests live under `tests/Architecture` and validate a set of conventions and static rules used by the project. Current tests include (non-exhaustive):

- `RouteArchitectureTest.php` — route URI naming conventions (kebab-case)
- `ModelArchitectureTest.php` — model naming, relationship naming, snake_case attributes, existence of `$fillable`, and prohibition of `scope*` methods
- `FormRequestArchitectureTest.php` — FormRequest file naming and strict snake_case validation keys in `rules()`
- `JsonResourceArchitectureTest.php` — resource naming and snake_case response attributes
- `MigrationsStaticAnalysisTest.php`, `ConfigArchitectureTest.php`, `EnvironmentUsageTest.php`, `TranslationIntegrityTest.php`, `PestPresetTest.php` — various static checks for migrations, config, environment usage and translations

These tests focus on naming, structure and static analysis. If you need stricter "layer boundary" or dependency restriction checks (for example enforcing that controllers never import services from certain namespaces), we should add explicit architecture tests for those rules — they are not fully enforced by the current suite.

### Static Analysis

Static analysis is executed on every Pull Request to detect:

* Type issues
* Dead code
* Invalid dependencies
* Potential runtime errors

---

## 🧪 Testing Strategy

The project includes automated testing for:

* Feature Tests
* Authentication Flows
* Authorization Rules
* Request Validation
* API Endpoints
* Architecture Constraints

---

## 🚀 Continuous Integration

Every Pull Request automatically executes:

* Pest Tests
* Architecture Tests
* PHPStan
* Type Coverage Validation
* Laravel Pint
* Rector

Pull requests should pass all quality gates before being merged.

---


## 🛠️  Useful Commands


### Run Tests

```bash
composer run test
```

### Static Analysis

```bash
composer run test:types
```

### Code Formatting

```bash
composer run lint
```

---
