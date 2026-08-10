# School Management & Portal System

A full-stack Laravel web application designed for Nigerian secondary schools. It replaces disconnected tools such as WhatsApp communication, Excel spreadsheets, paper attendance books, manual report cards, and parent phone calls with a centralized platform where each stakeholder — administrators, teachers, parents, and students — accesses only the information relevant to them.

This is **not** a multi-tenant SaaS platform. It is built for single-school deployment.

---

## Table of Contents

1. [Features](#features)
2. [User Roles](#user-roles)
3. [Technology Stack](#technology-stack)
4. [Project Structure](#project-structure)
5. [Requirements](#requirements)
6. [Installation](#installation)
7. [Configuration](#configuration)
8. [Usage](#usage)
9. [Testing](#testing)
10. [Development](#development)
11. [Documentation](#documentation)
12. [Security](#security)
13. [License](#license)

---

## Features

### Public Website

- Responsive homepage with hero section, school motto, and quick statistics
- About page with school history, vision, and mission
- Academic programs overview
- Image gallery
- News and achievements
- Contact page with contact form
- Admissions information
- Announcements listing

### Authentication & Authorization

- Single role-aware login page
- Role-based dashboard redirect (admin, teacher, parent, student)
- Password reset via email
- First-login password change enforcement
- Session timeout
- Account activation/deactivation
- Remember me functionality

### Administrator Module

- **Student Management** — create, edit, view, deactivate students; assign classes; upload photos; record emergency contacts; bulk import via Excel
- **Staff Management** — create teachers; assign subjects; assign form teachers
- **Class Management** — create, edit, assign form teachers, view class lists
- **Academic Structure** — academic sessions, terms, subjects, class-subjects
- **Teacher Assignments** — assign teachers to class-subjects; manage class assignments
- **Fee Management** — define fee types; record student fees; record payments; generate receipts; track outstanding fees
- **Results Management** — enter CA and exam scores; automatic total and grade calculation; lock results; export results
- **Report Cards** — generate report cards; calculate student positions; publish workflow; approve workflow; return for correction; PDF download; publish all for a term
- **Finance Dashboard** — fee collection summary
- **Account Management** — manage user accounts
- **Audit Log Monitoring**

### Teacher Module

- Dashboard
- View assigned classes and subjects
- Record attendance (daily and class-level)
- Submit Continuous Assessment (CA) and Examination scores
- View submission progress
- Manage report cards and submissions
- View timetable
- View announcements

### Parent Module

- Dashboard
- View enrolled children and their details
- View report cards (published only)
- View attendance records
- View fee status
- View announcements

### Student Module

- Dashboard
- View personal results (published only)
- Download PDF report cards (published only)
- View attendance
- View timetable
- View announcements
- View profile

### Core Features

- **Nigerian Grading Scale** — automatic grade and remark calculation based on the WAEC-aligned scale (A1 through F9)
- **Academic Integrity** — results cannot be edited after term locking; report cards cannot be published unless the term is locked; published report cards remain immutable
- **Audit Logging** — tracks student updates, result changes, fee records, account changes, publish/unpublish, lock/unlock, and login events
- **PDF Generation** — printable report cards via DomPDF
- **Excel Import/Export** — student and result data import/export via Laravel Excel
- **Role-Based Access Control** — enforced at both route (middleware) and resource (policy) levels

---

## User Roles

| Role | Description |
|------|-------------|
| **admin** | Full system access. Manages students, staff, classes, fees, results, report cards, and account settings. |
| **teacher** | Records attendance, submits results, manages report cards, and views timetable for assigned class-subjects. |
| **parent** | Views children's report cards, attendance, fee status, and announcements. |
| **student** | Views personal results, attendance, timetable, report cards, and announcements. |

---

## Technology Stack

| Layer | Technology |
|-------|------------|
| **Backend** | Laravel 13 (PHP 8.3+) |
| **Frontend** | Blade templates |
| **Styling** | Tailwind CSS |
| **Interactivity** | Alpine.js |
| **Database** | MySQL 8+ (SQLite for testing) |
| **Authentication** | Laravel Breeze |
| **Authorization** | Laravel Policies + Middleware |
| **PDF Generation** | barryvdh/laravel-dompdf |
| **Excel** | maatwebsite/laravel-excel (planned) |
| **File Storage** | Laravel Storage (public disk) |
| **Queue** | Laravel Queue (database driver) |
| **Cache** | Redis |
| **Email** | SMTP |
| **Hosting** | cPanel / Shared Hosting compatible |

---

## Project Structure

```
School-LMS/
├── backend/                     # Laravel application
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Admin/       # Admin controllers
│   │   │   │   ├── Teacher/     # Teacher controllers
│   │   │   │   ├── Parent_/     # Parent controllers
│   │   │   │   ├── Student/     # Student controllers
│   │   │   │   ├── Auth/        # Authentication controllers
│   │   │   │   └── PublicController.php
│   │   │   ├── Middleware/      # Role and password-change middleware
│   │   │   ├── Requests/        # Form request validation classes
│   │   │   └── ...
│   │   ├── Models/              # Eloquent models
│   │   ├── Policies/            # Authorization policies
│   │   ├── Services/            # Business logic layer
│   │   │   ├── ResultService.php
│   │   │   ├── FeeService.php
│   │   │   ├── ReportCardService.php
│   │   │   └── ...
│   │   ├── Traits/              # Audit logging trait
│   │   └── View/Components/     # Blade view components
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   │   ├── migrations/
│   │   ├── seeders/
│   │   └── factories/
│   ├── public/                  # Web root
│   ├── resources/
│   │   ├── css/
│   │   ├── js/
│   │   └── views/               # Blade templates (admin, teacher, parent, student, public, auth)
│   ├── routes/
│   │   └── web.php              # All web routes
│   ├── storage/
│   └── tests/                   # Feature and unit tests
│       ├── Feature/
│       │   ├── AdminManagementTest.php
│       │   ├── AuthFlowTest.php
│       │   ├── FinanceManagementTest.php
│       │   ├── FoundationModelsTest.php
│       │   ├── ParentPortalTest.php
│       │   ├── PublicWebsiteTest.php
│       │   ├── ReportCardManagementTest.php
│       │   ├── ResultsManagementTest.php
│       │   ├── StudentPortalTest.php
│       │   └── TeacherPortalTest.php
│       └── Unit/
├── docs/                        # Project documentation
│   ├── PRD.md                   # Product Requirements Document
│   ├── ARCHITECTURE.md          # Architecture overview
│   ├── DATABASE.md              # Database design
│   ├── API.md                   # API standards
│   ├── CODING_STANDARD.md       # Coding standards
│   ├── DECISIONS.md             # Architecture Decision Records
│   ├── ROADMAP.md               # Development roadmap
│   ├── features/                # Feature documentation
│   └── ui/                      # UI documentation
├── .gitignore
├── .kilo/                       # Kilo configuration
│   ├── command/
│   ├── agent/
│   └── worktrees/
└── README.md                    # This file
```

---

## Requirements

### Server Requirements

- **PHP** 8.3 or higher
- **Database**: MySQL 8.0+ or SQLite 3 (for testing/development)
- **Web Server**: Apache or Nginx
- **PHP Extensions**:
  - BCMath
  - Ctype
  - cURL
  - DOM
  - Fileinfo
  - Filter
  - Hash
  - Mbstring
  - OpenSSL
  - PCRE
  - PDO
  - Session
  - Tokenizer
  - XML
- **Optional**: Redis (for caching, as configured in `.env`)

### Node.js Requirements (Frontend Assets)

- Node.js 20+ and npm (for building Tailwind CSS and Alpine.js assets)

---

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd School-LMS
```

### 2. Install Backend Dependencies

```bash
cd backend
composer install
```

### 3. Set Up Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit the `.env` file to configure your database, mail, and application settings:

```env
APP_NAME="Greenfield Academy"
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### 4. Run Migrations and Seed

```bash
php artisan migrate --force
php artisan db:seed
```

The `DemoSeeder` will populate the database with demo data including:

- Default admin account
- Sample students, teachers, and parents
- Sample academic sessions, terms, classes, and subjects
- Sample fee types and records

### 5. Build Frontend Assets

```bash
npm install
npm run build
```

For development, use:

```bash
npm run dev
```

### 6. Set Up Storage Link

```bash
php artisan storage:link
```

### 7. Set Up Scheduler and Queue (Production)

Add the following cron entry for Laravel's task scheduler:

```bash
* * * * * php /path/to/backend/artisan schedule:run >> /dev/null 2>&1
```

Start the queue worker:

```bash
php artisan queue:work --timeout=0 --tries=1
```

---

## Configuration

Key configuration files:

| File | Purpose |
|------|---------|
| `.env` | Application environment variables (database, mail, cache, etc.) |
| `config/app.php` | Application name, locale, timezone |
| `config/database.php` | Database connections |
| `config/auth.php` | Authentication guards and password brokers |
| `config/session.php` | Session driver and lifetime |
| `config/filesystems.php` | File storage disks |
| `config/mail.php` | Mail driver settings |
| `config/cache.php` | Cache driver configuration |

### Demo Credentials

After running the `DemoSeeder`:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | password |
| Teacher | teacher@example.com | password |
| Parent | parent@example.com | password |
| Student | student@example.com | password |

---

## Usage

### Starting the Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`.

### Quick Setup Script

The project includes a one-command setup script:

```bash
composer setup
```

This runs: composer install → copy `.env` → generate key → run migrations → npm install → npm build.

### Running the Full Development Environment

```bash
composer dev
```

This starts the server, queue worker, log listener, and Vite dev server concurrently.

### Accessing the Application

| Environment | URL |
|-------------|-----|
| Local development | `http://localhost:8000` |
| Login page | `http://localhost:8000/login` |
| Public website | `http://localhost:8000/` |
| Admin dashboard | `http://localhost:8000/admin/dashboard` |
| Teacher portal | `http://localhost:8000/teacher/dashboard` |
| Parent portal | `http://localhost:8000/parent/dashboard` |
| Student portal | `http://localhost:8000/student/dashboard` |

---

## Testing

The project includes comprehensive feature and unit tests covering:

- Authentication flows (login, logout, password reset, first-login password change)
- Role-based access control
- Admin management (students, teachers, classes, accounts)
- Academic structure (sessions, terms, subjects)
- Finance management (fee types, student fees, payments)
- Results management (entry, calculation, locking, publishing)
- Report card management (generation, approval, PDF export)
- Teacher portal (attendance, results, report cards, timetable)
- Parent portal (children, results, attendance, fees, report cards)
- Student portal (results, attendance, fees, report cards, timetable)
- Public website (home, about, contact, admissions, announcements)
- Foundation models and relationships

### Run All Tests

```bash
composer test
```

Or directly:

```bash
php artisan test
```

### Run Specific Test Suites

```bash
# Feature tests
php artisan test --testsuite=Feature

# Unit tests
php artisan test --testsuite=Unit

# Specific test file
php artisan test tests/Feature/AuthFlowTest.php
```

### Test Environment

Tests run against an in-memory SQLite database by default (configured in `phpunit.xml`). No external database is required for testing.

---

## Development

### Available Commands

| Command | Description |
|---------|-------------|
| `composer dev` | Start server, queue, logs, and Vite concurrently |
| `composer test` | Run the full test suite |
| `npm run dev` | Start Vite development server |
| `npm run build` | Build production frontend assets |
| `php artisan serve` | Start the Laravel development server |
| `php artisan migrate` | Run database migrations |
| `php artisan db:seed` | Seed demo data |
| `php artisan tinker` | Interactive PHP shell |

### Code Style

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard
- Use Laravel Pint for code formatting: `vendor/bin/pint`
- Keep controllers thin — business logic belongs in services
- Use Form Requests for validation
- Use Policies and Middleware for authorization

### Development Guidelines

- Run `composer test` before submitting changes
- Use `php artisan migrate:fresh --seed` to reset and seed the database during development
- See [docs/CODING_STANDARD.md](docs/CODING_STANDARD.md) for detailed coding standards
- See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for architectural decisions
- See [docs/ROADMAP.md](docs/ROADMAP.md) for the development roadmap

---

## Documentation

| Document | Description |
|----------|-------------|
| [PRD](docs/PRD.md) | Product Requirements Document — the primary source of truth |
| [Architecture](docs/ARCHITECTURE.md) | Architecture overview and principles |
| [Database](docs/DATABASE.md) | Database schema design and constraints |
| [API Standards](docs/API.md) | API response formats and conventions |
| [Coding Standards](docs/CODING_STANDARD.md) | Code style and organization standards |
| [Decisions](docs/DECISIONS.md) | Architecture Decision Records |
| [Roadmap](docs/ROADMAP.md) | Development roadmap and phase sequence |
| [Features](docs/features/) | Per-feature documentation |
| [UI](docs/ui/) | UI design documentation |

---

## Security

Security is a first-class requirement. The application implements:

- **CSRF Protection** — all state-changing forms use Laravel's CSRF tokens
- **Password Hashing** — passwords are hashed using Laravel's secure `bcrypt` helper
- **Role-Based Access Control** — enforced at route (middleware) and resource (policy) levels
- **Rate Limiting** — login and password-reset endpoints are rate-limited
- **Input Validation** — all requests use Form Request validation
- **SQL Injection Protection** — all database operations use Eloquent ORM and Query Builder
- **XSS Protection** — all Blade output is escaped by default
- **File Upload Security** — uploads are restricted by MIME type, file size, and randomized naming; stored outside the public root
- **Audit Logging** — tracks sensitive actions including login events, result changes, fee payments, and report card publication
- **HTTPS Enforcement** — production deployment enforces HTTPS with secure cookies
- **Session Security** — encrypted sessions with configurable timeout

For security vulnerabilities, please report to the development team directly.

---

## License

This project is licensed under the MIT License. See the [backend/LICENSE](backend/LICENSE) file for details.

---

## Acknowledgments

- [Laravel](https://laravel.com) — PHP web application framework
- [Tailwind CSS](https://tailwindcss.com) — CSS framework
- [Alpine.js](https://alpinejs.dev) — minimal JavaScript framework
- [DomPDF](https://github.com/barryvdh/laravel-dompdf) — PDF generation
- [Laravel Excel](https://laravel-excel.com) — Excel import/export
