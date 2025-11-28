# NebulaDesk

**Multi-Tenant SaaS Helpdesk & Ticketing System**

NebulaDesk is an enterprise-grade, multi-tenant helpdesk solution built with modern web technologies. It provides comprehensive ticket management, SLA tracking, role-based access control, and audit logging for organizations of all sizes.

---

## Table of Contents

- [Features](#features)
- [Technology Stack](#technology-stack)
- [Architecture](#architecture)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Running the Application](#running-the-application)
- [Default Credentials](#default-credentials)
- [API Documentation](#api-documentation)
- [Testing](#testing)
- [Deployment](#deployment)
- [License](#license)

---

## Features

### Core Functionality

- **Multi-Tenant Architecture**: Complete data isolation between organizations
- **Ticket Management**: Create, assign, track, and resolve support tickets
- **SLA Management**: Define and monitor Service Level Agreements with breach detection
- **Role-Based Access Control (RBAC)**: Four distinct roles (Admin, Owner, Agent, Customer)
- **Audit Logging**: Comprehensive tracking of all system actions
- **Comments & Attachments**: Rich ticket collaboration with file support
- **Email Notifications**: Automated notifications for ticket events
- **Dashboard & Analytics**: Real-time metrics and reporting

### Security Features

- **Email Verification**: Mandatory email verification for new users
- **Password Reset**: Secure password recovery flow with token-based reset
- **Multi-Tenant Security**: Organization-level data isolation with strict validation
- **API Authentication**: Laravel Sanctum token-based authentication
- **CORS Protection**: Configured cross-origin resource sharing
- **Input Validation**: Comprehensive request validation on all endpoints

### User Experience

- **Responsive Design**: Mobile-first, fully responsive interface
- **Dark Mode Support**: System-wide dark theme support
- **Modern UI**: Clean, professional interface built with Tailwind CSS
- **Animated Transitions**: Smooth animations using Framer Motion
- **Toast Notifications**: Real-time user feedback for all actions
- **Error Boundaries**: Graceful error handling with fallback UI

---

## Technology Stack

### Backend

- **Framework**: Laravel 12 (PHP 8.2+)
- **Architecture**: Domain-Driven Design (DDD) with Clean Architecture principles
- **Authentication**: Laravel Sanctum (API Token Authentication)
- **Database**: PostgreSQL (production) / SQLite (development)
- **Caching**: Redis (sessions, cache, queue)
- **Storage**: S3-compatible storage (DigitalOcean Spaces / AWS S3)
- **Email**: SMTP / Mailgun integration
- **Queue**: Redis-backed job queue for async processing

### Frontend

- **Framework**: Next.js 16 with App Router
- **Language**: TypeScript 5
- **Styling**: Tailwind CSS 4
- **State Management**: React Context API
- **HTTP Client**: Axios
- **Animations**: Framer Motion
- **Icons**: Heroicons
- **Notifications**: React Hot Toast

### Development Tools

- **Code Quality**: Laravel Pint (PHP) / ESLint (TypeScript)
- **Testing**: PHPUnit (backend) / Jest (frontend - planned)
- **API Testing**: Postman / Insomnia
- **Version Control**: Git

---

## Architecture

NebulaDesk follows a **Domain-Driven Design (DDD)** approach with clear separation of concerns:

### Backend Structure

```
backend/
├── app/                      # Laravel application layer
│   ├── Models/              # Eloquent ORM models
│   ├── Policies/            # Authorization policies
│   └── Http/Middleware/     # HTTP middleware
├── src/                     # DDD architecture
│   ├── Domain/             # Business logic & entities
│   │   ├── Entities/       # Domain entities
│   │   └── Repositories/   # Repository interfaces
│   ├── Application/        # Use cases & DTOs
│   │   ├── UseCases/      # Application use cases
│   │   ├── DTOs/          # Data Transfer Objects
│   │   └── Services/      # Application services
│   └── Infrastructure/     # Implementation details
│       ├── Persistence/   # Repository implementations
│       └── Http/          # Controllers & middleware
├── database/
│   ├── migrations/        # Database migrations
│   └── seeders/          # Database seeders
└── routes/
    └── api.php           # API routes definition
```

### Frontend Structure

```
frontend/
├── src/
│   ├── app/                 # Next.js app router
│   │   ├── dashboard/      # Dashboard pages
│   │   ├── login/          # Authentication pages
│   │   └── register/
│   ├── components/          # Reusable React components
│   ├── contexts/           # React Context providers
│   └── lib/                # Utilities & configurations
│       └── axios.ts        # API client configuration
└── public/                  # Static assets
```

---

## Prerequisites

Ensure you have the following installed:

- **PHP**: 8.2 or higher
- **Composer**: 2.x
- **Node.js**: 18.x or higher
- **npm**: 9.x or higher
- **Database**: PostgreSQL 14+ (recommended) or SQLite (development)
- **Redis**: 6.x or higher (optional for development, required for production)
- **Web Server**: Apache or Nginx (production)

---

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/NebulaDesk.git
cd NebulaDesk
```

### 2. Backend Setup

```bash
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env
# Edit DB_CONNECTION, DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# Run migrations
php artisan migrate

# Seed the database (creates default admin user and test data)
php artisan db:seed

# Create symbolic link for storage
php artisan storage:link
```

### 3. Frontend Setup

```bash
cd ../frontend

# Install Node dependencies
npm install

# Copy environment file (if you have one)
cp .env.example .env.local

# Configure API URL in .env.local
# NEXT_PUBLIC_API_URL=http://localhost:8000
```

---

## Configuration

### Backend Configuration (.env)

```env
# Application
APP_NAME=NebulaDesk
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file

# Frontend URL (for CORS)
FRONTEND_URL=http://localhost:3000

# Database (PostgreSQL for production)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nebuladesk
DB_USERNAME=postgres
DB_PASSWORD=yourpassword

# Or use SQLite for local development
# DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite

# Redis (required for production)
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache & Session
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@nebuladesk.com"
MAIL_FROM_NAME="${APP_NAME}"

# Storage (S3 / DigitalOcean Spaces)
FILESYSTEM_DISK=local
# For production:
# FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=
# AWS_SECRET_ACCESS_KEY=
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=
# AWS_ENDPOINT=https://nyc3.digitaloceanspaces.com
# AWS_USE_PATH_STYLE_ENDPOINT=false

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:3000
```

### Frontend Configuration (.env.local)

```env
NEXT_PUBLIC_API_URL=http://localhost:8000
```

---

## Running the Application

### Development Mode

#### Option 1: Run Separately

**Terminal 1 - Backend:**

```bash
cd backend
php artisan serve
# Server runs on http://localhost:8000
```

**Terminal 2 - Frontend:**

```bash
cd frontend
npm run dev
# Server runs on http://localhost:3000
```

#### Option 2: Concurrent (Recommended)

If you have `concurrently` installed, you can run both from the backend:

```bash
cd backend
composer dev
# Runs backend server, queue worker, logs, and frontend dev server
```

### Production Mode

#### Backend

```bash
cd backend
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run with supervisor or systemd for queue workers
php artisan queue:work --tries=3 --timeout=90
```

#### Frontend

```bash
cd frontend
npm run build
npm run start
```

For production deployment, consider using:

- **Backend**: Apache/Nginx with PHP-FPM
- **Frontend**: Vercel, Netlify, or Docker container
- **Process Management**: Supervisor (for Laravel queues)
- **Database**: Managed PostgreSQL (AWS RDS, DigitalOcean Managed DB)
- **Cache**: Managed Redis

---

## Default Credentials

After running `php artisan db:seed`, you can log in with these test accounts:

### Administrator

- **Email**: `admin@nebuladesk.com`
- **Password**: `Admin123!`
- **Role**: Admin (full system access)

### Agent

- **Email**: `agent@nebuladesk.com`
- **Password**: `Agent123!`
- **Role**: Agent (can manage tickets, view organization data)

### Customer

- **Email**: `customer@nebuladesk.com`
- **Password**: `Customer123!`
- **Role**: Customer (can create and view own tickets)

**Note**: All accounts belong to the "NebulaDesk Inc" organization created during seeding.

---

## API Documentation

### Authentication

All API endpoints (except `/register`, `/login`, `/password/*`) require authentication via Bearer token.

**Login:**

```http
POST /api/login
Content-Type: application/json

{
  "email": "admin@nebuladesk.com",
  "password": "Admin123!"
}
```

**Response:**

```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@nebuladesk.com",
    "role": "admin",
    "organization_id": 1
  }
}
```

### Key Endpoints

#### Tickets

- `GET /api/tickets` - List tickets (filtered by role)
- `POST /api/tickets` - Create ticket
- `GET /api/tickets/{id}` - View ticket details
- `PUT /api/tickets/{id}` - Update ticket
- `DELETE /api/tickets/{id}` - Delete ticket
- `PUT /api/tickets/{id}/assign` - Assign ticket to user
- `PUT /api/tickets/{id}/status` - Update ticket status

#### Dashboard

- `GET /api/dashboard/metrics` - Dashboard metrics (Admin/Owner only)

#### SLA

- `GET /api/sla/dashboard` - SLA dashboard (Admin/Owner only)

#### Organizations

- `GET /api/organizations` - List organizations
- `GET /api/organizations/{id}` - View organization
- `GET /api/organizations/users` - List users in organization
- `POST /api/organizations/users` - Invite user to organization

#### Audit Logs

- `GET /api/audit-logs` - View audit logs (Admin/Owner only)

For complete API documentation, import the Postman collection or refer to `routes/api.php`.

---

## Testing

### Backend Tests

```bash
cd backend

# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

### Frontend Tests

```bash
cd frontend

# Run tests (when implemented)
npm test

# Run with watch mode
npm test -- --watch
```

---

## Deployment

### Production Checklist

**Backend:**

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure production database (PostgreSQL)
- [ ] Set up Redis for cache, sessions, and queue
- [ ] Configure S3/Spaces for file storage
- [ ] Set up email provider (Mailgun, SendGrid)
- [ ] Configure proper `CORS` and `SANCTUM_STATEFUL_DOMAINS`
- [ ] Run `php artisan optimize`
- [ ] Set up SSL certificate (Let's Encrypt)
- [ ] Configure supervisor for queue workers
- [ ] Set up automated backups

**Frontend:**

- [ ] Update `NEXT_PUBLIC_API_URL` to production API
- [ ] Build with `npm run build`
- [ ] Deploy to Vercel/Netlify or container
- [ ] Configure CDN for static assets
- [ ] Set up monitoring (Sentry, LogRocket)

### Recommended Hosting

- **Backend**: DigitalOcean App Platform, AWS EC2, Laravel Forge
- **Frontend**: Vercel, Netlify, Cloudflare Pages
- **Database**: DigitalOcean Managed PostgreSQL, AWS RDS
- **Storage**: DigitalOcean Spaces, AWS S3
- **Cache**: DigitalOcean Managed Redis, AWS ElastiCache

---

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Standards

- Follow PSR-12 for PHP code
- Use Laravel Pint for code formatting: `./vendor/bin/pint`
- Follow TypeScript best practices for frontend
- Write tests for new features
- Update documentation as needed

---

## License

This project is licensed under the **MIT License**.

---

## Support

For questions, issues, or feature requests:

- **Issues**: [GitHub Issues](https://github.com/yourusername/NebulaDesk/issues)
- **Email**: support@nebuladesk.com
- **Documentation**: [Wiki](https://github.com/yourusername/NebulaDesk/wiki)

---

**Built with Laravel, Next.js, and modern web technologies.**
