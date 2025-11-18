# Language AI Backoffice

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

A modular Laravel application for Language AI backoffice administration with multi-organization support, role-based access control, and comprehensive management features.

## 🏗️ Application Overview

### Purpose

This backoffice application serves as the administration panel for Language AI. It provides a comprehensive management interface for:

- User and organization management
- Language AI service management
- Public service and event management
- Role-based access control
- Menu and permission management
- System configuration

### Modular Architecture

Built with **nwidart/laravel-modules** for scalable, maintainable architecture:

- **Auth Module**: Authentication and user management
- **Home Module**: Dashboard and user profile management
- **System Module**: System administration (users, roles, permissions, organizations, menus)
- **Public Module**: Public service and event management with face recognition
- **LanguageAI Module**: Language AI service management and administration

## 🚀 Key Features

- ✅ **Multi-Organization Support** - Manage multiple organizations with scoped data access
- ✅ **Role-Based Access Control** - Comprehensive RBAC with dynamic permission assignment
- ✅ **User Management** - Complete user administration with status management
- ✅ **Public Service Management** - Public-facing service and event management with face recognition
- ✅ **Language AI Management** - Language AI service administration and configuration
- ✅ **Event Management** - Event registration and participant management
- ✅ **Face Recognition** - Face recognition integration for attendance and security
- ✅ **Menu Management** - Dynamic menu system with permission-based visibility
- ✅ **Organization Administration** - Full organization management capabilities
- ✅ **Role & Permission Management** - Granular access control with role-permission mapping
- ✅ **Dashboard** - Informative dashboard with key metrics
- ✅ **Notification System** - Multi-channel notifications (Database, Email, FCM Push)
- ✅ **Push Notifications** - Firebase Cloud Messaging for real-time push notifications
- ✅ **Profile Management** - User profile and account settings

## 🛠️ Tech Stack

- **Backend:** Laravel 12.x, PHP 8.4+
- **Frontend:** AdminLTE theme with Bootstrap
- **Database:** PostgreSQL with custom `sys_` prefixed schema
- **Authentication:** Multi-organization RBAC with Spatie Permission
- **Modules:** nwidart/laravel-modules for modular architecture
- **UI Components:** Kendo UI Grid for data tables
- **Push Notifications:** Firebase Cloud Messaging (FCM) HTTP v1 API
- **Image Processing:** Intervention Image
- **Storage:** AWS S3 integration via League Flysystem
- **Activity Logging:** Spatie Laravel Activity Log
- **Development Tools:** Laravel Pint (code formatting), Laravel Pail (log monitoring), Laravel Boost (MCP tools)

## 📦 Installation

### Prerequisites

- PHP 8.4 or higher
- Composer
- Node.js & npm
- PostgreSQL 16+

### Setup Steps

1. **Clone the repository**

   ```bash
   git clone <repository-url>
   cd language-ai-backoffice
   ```

2. **Install dependencies**

   ```bash
   composer install
   npm install
   ```

3. **Environment configuration**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**

   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5417
   DB_DATABASE=language_ai_backoffice
   DB_USERNAME=postgres
   DB_PASSWORD=postgres
   ```

5. **Run migrations and seeders**

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build assets**

   ```bash
   npm run build
   ```

## 🏃‍♂️ Development

### Start Development Environment

```bash
# Start full stack (server, queue, logs, vite)
composer dev

# Or start services individually:
php artisan serve           # Laravel server
php artisan queue:listen    # Queue worker  
php artisan pail           # Real-time logs
npm run dev                # Vite dev server
```

### Module Development

```bash
# Create new module
php artisan module:make ModuleName

# List modules
php artisan module:list

# Enable/disable modules
php artisan module:enable ModuleName
php artisan module:disable ModuleName
```

### Testing

```bash
# Run full test suite
composer test

# Run specific tests
php artisan test --filter TestName
```

### Code Quality

```bash
# Format code
php artisan pint
```

## 🏢 System Modules

### Authentication Module (Auth)

Handles user authentication, registration, password reset, and email verification.

### Home Module (Home)

Provides the main dashboard interface and user profile management:

- Dashboard with key metrics
- Profile settings
- Password update
- Notification management
- Organization and role switching

### System Module (System)

Comprehensive system administration:

- **User Management**: Create, update, ban/unban users
- **Role Management**: Define roles and assign permissions
- **Permission Management**: Manage system permissions
- **Organization Management**: Create and manage organizations
- **Menu Management**: Configure navigation menus with permission-based visibility

### Public Module (Public)

Public service and event management:

- Event registration and management
- Participant management with photo uploads
- Face recognition data collection
- Event attendance tracking via face recognition
- Public API endpoints for event details and registration

### LanguageAI Module (LanguageAI)

Language AI service management:

- Language AI service administration
- Service configuration and management
- AI-related functionality and interfaces

## 🔒 Security Features

### Implemented Protections

- **Rate Limiting** - IP-based attempt throttling
- **Session Security** - Encryption and secure cookie configuration
- **CSRF Protection** - On all state-changing operations
- **Input Validation** - Comprehensive request validation
- **SQL Injection Prevention** - Parameterized queries throughout
- **User Status Management** - Ban/unban functionality
- **Email Verification** - Required for account activation

## 📂 Project Structure

```text
├── app/                          # Core application
│   ├── Classes/                 # Application classes (Breadcrumbs, EnumConcern)
│   ├── Enums/                   # Application enumerations (BuiltInRole, Permission, StorageSource)
│   ├── Helpers/                 # Global helper functions
│   ├── Http/
│   │   ├── Controllers/         # Base controllers
│   │   └── Middleware/          # Custom middleware (LogoutIfBanned, TeamPermission)
│   ├── Models/
│   │   └── DB1/                 # Database models (Sys*, Class*)
│   ├── Notifications/
│   │   ├── Channels/            # Custom notification channels (FcmChannel)
│   │   └── Messages/            # Notification message classes (FcmMessage)
│   ├── Providers/               # Service providers
│   └── Services/                # Application services (FcmService)
├── Modules/                     # Modular architecture
│   ├── Auth/                    # Authentication module
│   │   ├── app/Http/Controllers/
│   │   ├── routes/              # web.php, api.php
│   │   └── resources/views/     # Login, password reset views
│   ├── Home/                    # Dashboard and profile module
│   │   ├── app/Http/Controllers/
│   │   ├── app/Notifications/   # Greeting, OtpUpdateEmail
│   │   └── resources/views/     # Dashboard, profile, notifications
│   ├── System/                  # System administration module
│   │   ├── app/Http/Controllers/ # User, Role, Permission, Organization, Menu management
│   │   └── resources/views/     # Management interfaces
│   ├── Public/                  # Public service management module
│   │   ├── app/Http/Controllers/Api/ # Event registration API
│   │   └── resources/views/     # Public-facing views
│   └── LanguageAI/              # Language AI service module
│       ├── app/Http/Controllers/
│       └── resources/views/     # Language AI interfaces
├── bootstrap/
│   ├── app.php                  # Application bootstrap
│   └── providers.php            # Service provider registration
├── config/                      # Configuration files
├── database/
│   ├── migrations/              # Database schema migrations
│   ├── seeders/                 # Data seeders
│   └── factories/               # Model factories
├── docker/                      # Docker configuration
│   ├── dockerfile/              # Dockerfile definitions
│   ├── nginx/                   # Nginx configuration
│   ├── php/                     # PHP configuration
│   └── supervisor/              # Supervisor configuration
├── resources/
│   ├── views/                   # Shared Blade templates (layouts)
│   ├── css/                     # Stylesheets
│   └── js/                      # Frontend JavaScript
├── routes/
│   ├── web.php                  # Global web routes
│   └── console.php              # Console routes
└── storage/                     # Application storage
    ├── app/                     # Application files
    ├── logs/                    # Application logs
    └── framework/               # Framework cache
```

## 🔧 Configuration

### Environment Variables

#### Application Settings

```env
APP_NAME="Language AI Backoffice"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5417
DB_DATABASE=language_ai_backoffice
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

#### Session Configuration

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=false
```

#### Firebase Cloud Messaging (FCM) Configuration

```env
FCM_CREDENTIALS=config/credentials.json
FCM_PROJECT_ID=language-ai-project
FCM_PRIORITY=high
FCM_TTL=3600
FCM_LOGGING_ENABLED=true
FCM_LOGGING_CHANNEL=daily
```

**Setup Firebase:**

1. Obtain Firebase service account credentials JSON from [Firebase Console](https://console.firebase.google.com)
2. Place the credentials file at `config/credentials.json`
3. Update `FCM_PROJECT_ID` in `.env` with your Firebase project ID

## 🔔 Notification System

The application features a comprehensive multi-channel notification system supporting:

### Notification Channels

- **Database** - Store notifications in database for in-app display
- **Email** - Send email notifications via configured mail service
- **FCM (Firebase Cloud Messaging)** - Real-time push notifications to web/mobile devices

### Push Notifications Architecture

The FCM implementation uses a custom notification channel with:

- **Custom FCM Channel** (`App\Notifications\Channels\FcmChannel`) - Laravel notification channel integration
- **FCM Service** (`App\Services\FcmService`) - Firebase HTTP v1 API integration with OAuth2 JWT authentication
- **Fluent Message Builder** (`App\Notifications\Messages\FcmMessage`) - Easy-to-use API for building FCM messages
- **Token Management** - Store and manage device tokens in `sys_user_fbtokens` table
- **Multi-Device Support** - Automatically broadcasts to all user's registered devices
- **Queue Support** - Notifications can be queued for better performance

### Usage Example

```php
use Modules\Home\Notifications\Greeting;

// Send via all channels
$user->notify(new Greeting(
    'Welcome!',
    'Thanks for joining our platform',
    route('dashboard'),
    ['database', 'mail', 'fcm'] // Specify channels
));

// In your notification class
public function toFcm($notifiable): FcmMessage
{
    return FcmMessage::create()
        ->title('Notification Title')
        ->body('Notification body message')
        ->data(['key' => 'value']) // Custom data
        ->clickAction(route('notifications'))
        ->priority('high');
}
```

### Device Token Registration

Frontend applications should register FCM tokens via the API endpoint:

```javascript
POST /notification/store-token
{
    "token": "firebase-device-token"
}
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Follow PSR-12 coding standards
4. Write tests for new functionality
5. Commit changes (`git commit -m 'Add amazing feature'`)
6. Push to branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

### Development Guidelines

- Follow modular architecture patterns
- Implement proper security measures
- Write unit tests for new features
- Use type hints and docblocks
- Follow Laravel conventions

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🔗 Related Documentation

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Modules](https://docs.laravelmodules.com/)
- [Spatie Permission](https://spatie.be/docs/laravel-permission)
