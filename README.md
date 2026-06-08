# ClubManager API

![Laravel](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge\&logo=laravel\&logoColor=white)
![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge\&logo=php\&logoColor=white)
![MySQL](https://img.shields.io/badge/mysql-%2300f.svg?style=for-the-badge\&logo=mysql\&logoColor=white)
![Laravel Sanctum](https://img.shields.io/badge/Sanctum-Authentication-red?style=for-the-badge)
![Pest](https://img.shields.io/badge/Pest-Testing-green?style=for-the-badge)
![Laravel Sail](https://img.shields.io/badge/Laravel-Sail-blue?style=for-the-badge)
![GitHub Actions](https://img.shields.io/badge/GitHub-Actions-black?style=for-the-badge)

## Overview

ClubManager API is a SaaS platform designed to manage sports clubs, courts, availability schedules and pricing rules.

The platform provides a multi-role architecture that separates responsibilities between platform administrators, club administrators and end users, allowing sports organizations to manage their facilities through a centralized system.

### Current Status

🚧 Active Development

Implemented modules focus on platform administration and club management. End-user reservation features are currently under development.

---

## Domain Overview

Platform
├── Sport Types
├── Court Features
├── Clubs
│   ├── Courts
│   ├── Availability Rules
│   ├── Pricing Rules
│   └── Club Users
└── Reservations (Planned)

---

## Implemented Features

### Authentication

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

## System Roles

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

## Architecture

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

## API Design

The API follows REST principles and provides:

* JSON responses
* Consistent error handling
* HTTP status code standards
* Token authentication using Sanctum
* Request validation
* Resource serialization

### Example Endpoints

Authentication

POST /api/auth/register
POST /api/auth/login
POST /api/auth/logout

Clubs

GET /api/clubs
POST /api/clubs
PUT /api/clubs/{id}
DELETE /api/clubs/{id}

Courts

GET /api/courts
POST /api/courts

Availability

GET /api/availabilities
POST /api/availabilities

---

## Technology Stack

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

## Quality Standards

The project enforces multiple quality gates to maintain consistency and reliability.

### Automated Quality Gates

* Pest Test Suite
* Architecture Tests
* PHPStan / Larastan
* Laravel Pint
* Rector
* Type Coverage Validation

### Architecture Validation

Architecture tests enforce:

* Layer boundaries
* Dependency restrictions
* Naming conventions
* Framework usage rules

### Static Analysis

Static analysis is executed on every Pull Request to detect:

* Type issues
* Dead code
* Invalid dependencies
* Potential runtime errors

---

## Testing Strategy

The project includes automated testing for:

* Feature Tests
* Authentication Flows
* Authorization Rules
* Request Validation
* API Endpoints
* Architecture Constraints

Example:

./vendor/bin/pest

---

## Continuous Integration

Every Pull Request automatically executes:

* Pest Tests
* Architecture Tests
* PHPStan
* Type Coverage Validation
* Laravel Pint
* Rector

Pull requests should pass all quality gates before being merged.

---

## Installation

### Clone Repository

```bash
git clone <repository-url>
cd club-manager
```

### Environment Configuration

```bash
cp .env.example .env
```

### Install Dependencies

```bash
composer install
```

### Start Containers

```bash
./vendor/bin/sail up -d
```

### Generate Application Key

```bash
./vendor/bin/sail artisan key:generate
```

### Run Database Migrations

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

---

## Useful Commands

### Development Environment

```bash
composer dev
```

### Run Tests

```bash
composer test
```

### Static Analysis

```bash
composer test:types
```

### Code Formatting

```bash
composer lint
```

---

## Roadmap

### In Progress

* Application User Module
* Reservation Workflow

### Planned

* Court Reservations
* Booking Management
* Reservation History
* Notifications
* Analytics Dashboard
* Advanced Pricing Rules
* Advanced Availability Rules

---

## License

MIT
