# Project Structure

```
app/
├── Console/Commands/     # Artisan commands (scheduled tasks)
├── Events/               # Broadcast events (StudentScanned, TeacherLoggedIn, etc.)
├── Http/
│   ├── Controllers/      # Request handlers, organized by domain
│   │   └── Auth/         # Authentication controllers
│   └── Middleware/       # Custom middleware (CheckRole)
├── Models/               # Eloquent models with relationships
├── Policies/             # Authorization policies (per-model)
├── Providers/            # Service providers
└── Services/             # Business logic layer (domain services)

database/
├── factories/            # Model factories for testing
├── migrations/           # Database schema migrations
└── seeders/              # Database seeders

resources/views/
├── components/           # Reusable Blade components
├── layouts/              # Page layouts
└── [domain]/             # Domain-specific views (attendance, students, etc.)

routes/
├── web.php               # Web routes with middleware groups
├── channels.php          # Broadcast channel authorization
└── console.php           # Console command scheduling

tests/
├── Feature/              # Feature/integration tests
├── Property/             # Property-based tests (Eris)
└── Unit/                 # Unit tests
```

## Architecture Patterns

- **Service Layer**: Business logic in `app/Services/` (e.g., `StudentAttendanceService`)
- **Policy Authorization**: Model-level authorization via `app/Policies/`
- **Event Broadcasting**: Real-time updates via `app/Events/` implementing `ShouldBroadcast`
- **Blade Components**: Reusable UI in `resources/views/components/`

## Naming Conventions

- Models: Singular PascalCase (`Student`, `ClassRoom`)
- Controllers: PascalCase with `Controller` suffix
- Services: PascalCase with `Service` suffix
- Migrations: Timestamped snake_case
- Views: kebab-case directories, snake_case files
- Routes: kebab-case URIs, dot-notation names

## Role-Based Middleware

Routes use `role:admin,principal` middleware pattern for access control. Teachers have implicit access to their own class data via policy checks.
