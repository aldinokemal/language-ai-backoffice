# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**dk-elearning** is an enterprise Laravel 12 modular application designed for multi-organization e-learning environments. Built with security-first principles and scalable modular architecture using nwidart/laravel-modules.

## Development Commands

**Development Environment:**

```bash
# Start full development stack (server, queue, logs, vite)
composer dev

# Alternative individual commands:
php artisan serve           # Start Laravel development server
php artisan queue:listen    # Start queue worker
php artisan pail           # Real-time log monitoring
npm run dev                # Start Vite development server
```

**Testing:**

```bash
composer test              # Run full test suite with config clear
php artisan test          # Run tests directly
php artisan test --filter TestName  # Run specific test
```

**Code Quality:**

```bash
php artisan pint          # Code formatting (Laravel Pint)
```

**Database:**

```bash
php artisan migrate       # Run migrations
php artisan db:seed       # Run seeders
php artisan migrate:fresh --seed  # Fresh migration with seeding
```

**Module Management:**

```bash
php artisan module:list   # List all modules
php artisan module:make ModuleName  # Create new module
php artisan module:enable ModuleName   # Enable module
php artisan module:disable ModuleName  # Disable module
```

## Architecture Overview

### Modular Laravel Architecture

This application uses **nwidart/laravel-modules** for modular architecture. Each module is self-contained with its own:

- Controllers, Models, Views
- Routes (web.php, api.php)
- Migrations, Seeders, Factories
- Service Providers
- Assets (JS/CSS) with Vite compilation

**Current Modules:**

```bash
Modules/
    Auth/                 # Authentication module
        app/
            Http/Controllers/  # LoginController, ForgotPasswordController
            Providers/        # AuthServiceProvider, EventServiceProvider, RouteServiceProvider
            Traits/           # SessionTrait (org/role switching, menu building)
        resources/views/      # login, forgot-password, reset-password forms
        routes/              # Authentication routes (login, logout, password reset)
        module.json          # Module configuration
        
    Home/                 # Main dashboard and user interface
        app/
            Http/Controllers/  # DashboardController, MyAccountController, NotificationController, SwitchController
            Providers/        # HomeServiceProvider, EventServiceProvider, RouteServiceProvider
        resources/views/      # dashboard, my-account, notification views
        routes/              # Protected dashboard and user management routes
        module.json          # Module configuration
        
    System/               # System administration module
        app/
            Http/Controllers/  # ManageUserController, ManageRoleController, ManagePermissionController, 
                              # ManageOrganizationController, ManageMenuController
            Providers/        # SystemServiceProvider, EventServiceProvider, RouteServiceProvider
        resources/views/      # user, role, permission, organization, menu management views
        routes/              # System administration routes
        module.json          # Module configuration
```

### Core System Architecture

**Authentication & Authorization:**

- Uses **Spatie Laravel Permission** for role-based access control with multi-organization support
- Granular permission system with dedicated enum structure (`App\Enums\Permission`)
- Multi-organization support with session-based org/role switching via `SessionTrait`
- Custom session management for dynamic menu building and permission synchronization
- Database-driven hierarchical menu system with permission-based visibility
- Team-based permission scoping for multi-tenant security

**Security Implementation:**

- Custom `SecurityHeaders` middleware with comprehensive headers (CSP, HSTS, etc.)
- Enhanced rate limiting with logging for security monitoring
- Session security: encryption enabled, strict SameSite, secure cookies
- CSRF protection on all state-changing operations including logout

**Database Design:**

- `sys_` prefixed system tables for core functionality
- **DB1 Namespace:** All models organized under `App\Models\DB1\` namespace for separation
- Multi-tenancy support via organization relationships with pivot tables
- Soft deletes enabled on user models for data retention
- Enhanced user-organization-role relationship model
- Custom password reset tokens and notification tables
- Firebase token storage for push notifications (`sys_user_fbtokens` table)

**Session & Menu System:**
The enhanced `SessionTrait` provides dynamic session management:

1. Sets default organization and role from user relationships
2. Configures team-based permissions using `setPermissionsTeamId()`
3. Syncs Spatie permissions with current role via `syncRoles()`
4. Builds hierarchical menu tree with parent-child relationships and ordering
5. Union queries for efficient permission-based menu filtering
6. Stores optimized menu structure in session for performance
7. Supports nested menu structures with unlimited depth

**Logging Strategy:**
Comprehensive security event logging for:

- Login attempts (success/failure)
- Password reset requests
- Rate limit violations
- User status changes (banned, etc.)

**Notification System:**
Multi-channel notification architecture:

- **Database Notifications** - Laravel's built-in database notification storage
- **Email Notifications** - Mail notifications via Laravel's mail system
- **FCM Push Notifications** - Custom Firebase Cloud Messaging integration:
  - Custom notification channel (`App\Notifications\Channels\FcmChannel`)
  - FCM HTTP v1 API with OAuth2 JWT authentication (`App\Services\FcmService`)
  - Fluent message builder (`App\Notifications\Messages\FcmMessage`)
  - Multi-device token broadcasting from `sys_user_fbtokens` table
  - Queue support for async notification delivery
  - Comprehensive error handling and logging

### System Module Administration

The **System module** provides comprehensive administration capabilities:

**User Management:**

- Complete CRUD operations for users with granular permissions
- Multi-organization role assignment with default role selection
- Avatar upload/management with storage source tracking
- Bulk user ban/unban functionality with selectAll support
- Advanced filtering and search with Kendo UI DataGrid
- Encrypted ID handling for security
- Password management with optional updates

**Role Management:**

- Organization-scoped role creation and management
- Permission grid with menu grouping and checkbox selection
- Role hierarchy and inheritance support
- Default role designation per organization

**Permission Management:**

- Enum-based permission structure for type safety
- CRUD permissions for each administrative function:
  - `system.users.*` - User management
  - `system.roles.*` - Role management
  - `system.permissions.*` - Permission management
  - `system.organizations.*` - Organization management
  - `system.menus.*` - Menu management

**Organization Management:**

- Multi-tenant organization structure
- Organization logo upload and management
- Default organization selection
- Organization-scoped data isolation

**Menu Management:**

- Hierarchical menu structure with unlimited nesting
- Permission-based menu visibility
- Dynamic menu ordering and organization
- Real-time menu updates based on user permissions

### Key Dependencies

- `nwidart/laravel-modules` - Modular architecture foundation
- `spatie/laravel-permission` - Role-based access control
- `spatie/laravel-activitylog` - Activity logging
- `intervention/image` - Image processing and avatar management
- `league/flysystem-aws-s3-v3` - S3 storage integration

### Key Components

**Models (App\Models\DB1\ namespace):**

- `SysUser` - Main user model with multi-organization support, soft deletes, avatar management
- `SysRole` - Organization-scoped roles with Spatie Permission integration
- `SysOrganization` - Multi-tenant organization structure with logo support
- `SysMenu` - Hierarchical menu system with permission-based visibility
- `SysPermission` - Permission model integrated with Spatie Permission
- `SysNotification` - User notification system
- `SysUserOrganization` - User-organization pivot model
- `SysUserOrganizationRole` - User-organization-role relationship model
- `SysUserFbToken` - Firebase token storage for push notifications

**Enums:**

- `Permission` - Comprehensive permission constants with EnumConcern trait
- `BuiltInRole` - Built-in system roles
- `StorageSource` - Storage source enumeration (PUBLIC, etc.)

**Classes:**

- `Breadcrumbs` - Breadcrumb navigation helper
- `EnumConcern` - Powerful enum trait with collection methods and utilities

**Services:**

- `FcmService` - Firebase Cloud Messaging service with OAuth2 authentication

**Helpers:**

- `GlobalHelper` - Global utility functions
- `ResponseHelper` - Standardized response formatting
- `SecurityHelper` - Security-related utilities
- Auto-loaded helper functions via `helpers.php`

**Middleware:**

- `SecurityHeaders` - Comprehensive security headers (CSP, HSTS, etc.)
- `LogoutIfBanned` - Automatic logout for banned users
- `TeamPermission` - Team-based permission middleware

**Traits:**

- `SessionTrait` - Enhanced org/role switching and menu building with team permissions

**Notification Components:**

- `App\Notifications\Channels\FcmChannel` - Custom FCM notification channel
- `App\Notifications\Messages\FcmMessage` - Fluent FCM message builder

### Frontend Integration

- **Vite** build system with Tailwind CSS 4.0
- **KTUI** for frontend components
- **Kendo UI for jQuery** for advanced UI widgets and components
- Module-specific asset compilation
- Theme switching functionality in auth views

**UI Development Guidelines:**

When updating UI components, always reference the MCP context7 for:

**KTUI Components:**

- Component usage patterns and best practices
- Available KTUI components and their configurations
- Styling conventions and theme integration
- Consistent design system implementation

**Kendo UI for jQuery:**

- Advanced data grids and tables with sorting, filtering, and pagination
- Rich UI widgets (DatePicker, DropDownList, MultiSelect, etc.)
- Charts and data visualization components
- Dialog and notification systems
- Form validation and input components
- Layout and navigation components (Menu, TabStrip, Splitter)
- Integration with jQuery and modern JavaScript frameworks

### Environment Considerations

- Development uses database sessions with encryption
- Production should enable HSTS and secure cookie flags
- Rate limiting and security logging are environment-aware

### Architecture Patterns

**Permission System Design:**

- All permissions follow the `{module}.{resource}.{action}` naming convention
- Use `App\Enums\Permission` enum for type-safe permission constants
- Gate authorization with `Gate::authorize()` in controllers
- Team-based permission scoping for multi-tenant isolation

**Model Relationships:**

- User → Organizations (Many-to-Many via `SysUserOrganization`)
- User → Roles (Many-to-Many via `SysUserOrganizationRole`)
- Roles → Permissions (Many-to-Many via Spatie Permission)
- Organizations → Roles (One-to-Many)
- Menus → Permissions (Many-to-Many for visibility)

**Security Patterns:**

- Encrypted IDs using `customEncrypt()`/`customDecrypt()` for public exposure
- Avatar protection with storage source tracking
- Bulk operations with selectAll/toggledNodes pattern
- Transaction-wrapped operations for data consistency

**Notification Patterns:**

- Multi-channel notifications via `via()` method returning array of channels
- Custom notification channels registered in `AppServiceProvider`
- Fluent message builders for each channel type (Mail, Database, FCM)
- Queue support via `Queueable` trait for async notification delivery
- FCM token management: store device tokens in `sys_user_fbtokens` with `user_id` foreign key
- Multi-device broadcasting: FCM channel retrieves all user tokens and broadcasts to each
- Firebase OAuth2 JWT authentication: service account credentials in `config/credentials.json`
- Access token caching: tokens cached until expiration for performance

### Module Development Guidelines

When creating new modules:

1. Follow the System module structure as the most comprehensive template
2. Register module providers in `module.json`
3. Use database migrations for module-specific tables with `sys_` prefix
4. Implement proper permission checks using `App\Enums\Permission` constants
5. Add module status to `modules_statuses.json`
6. Use `App\Models\DB1\` namespace for database models
7. Implement breadcrumb navigation using `App\Classes\Breadcrumbs`
8. Follow the encrypted ID pattern for security

### Security Monitoring

The application logs security events to Laravel logs. Monitor for:

- Failed login patterns from same IP
- Password reset abuse
- Rate limit violations
- Banned user login attempts

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.14
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4


## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== phpunit/core rules ===

## PHPUnit Core

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit <name>` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests
- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>


### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |
</laravel-boost-guidelines>
