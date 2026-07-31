# Coding Standards

This document defines the coding standards for the Laravel implementation. It is intended to keep the codebase readable, consistent, secure, and production-ready.

## 1. Laravel Coding Standards

- Follow PSR-12 coding style
- Use strict typing where practical
- Prefer typed properties and return types
- Use meaningful names over abbreviations
- Keep methods small and focused
- Avoid deep nesting and premature abstraction
- Use Laravel’s built-in helpers and conventions over custom patterns when the built-in approach is sufficient

## 2. Naming Conventions

### Classes
- Use PascalCase for classes
- Use descriptive noun-based names such as StudentController, ResultService, and ReportCardPolicy

### Methods
- Use camelCase for methods
- Prefer verb-based names such as store, update, publish, calculateGrade, and markAttendance

### Database and variables
- Use snake_case for database columns and Eloquent attributes
- Use camelCase for PHP variables and method parameters

### Files
- Match the class name to the file name
- Example: App\Services\ResultService should live in app/Services/ResultService.php

## 3. File Organization

- Keep controllers under the appropriate role-specific namespace
- Put feature-specific logic in services rather than controllers
- Keep policies in app/Policies
- Keep form requests in app/Http/Requests
- Keep models in app/Models
- Keep shared helpers in app/Support when they are not tied to a model

## 4. Service Layer Rules

Services are the default place for business logic.

Rules:
- Services should orchestrate actions that involve multiple models or rules
- Services may depend on other services when needed
- Services should not directly depend on HTTP request objects
- Services should return clean data structures or domain results
- Complex database operations should be wrapped in transactions

Examples:
- ResultService handles grade calculation, lock rules, and publish eligibility
- FeeService handles payment recording and outstanding balance logic
- ReportCardService prepares and publishes report cards

## 5. Controller Rules

Controllers should remain thin.

Rules:
- Controllers receive requests and delegate work to services or form requests
- Controllers should not contain business logic
- Controllers should not contain validation logic beyond passing requests to Form Requests
- Controllers should return views, redirects, or JSON responses only
- Controllers should not perform direct database writes except for the simplest operations

## 6. Model Rules

Models should represent domain entities and relationships.

Rules:
- Keep models focused on data shape and relationships
- Define casts, scopes, and accessors where they improve clarity
- Avoid placing complex business logic in models
- Prefer explicit relationship methods over manual joins
- Use model factories and seeders for test data generation

## 7. Form Request Rules

All input validation should use Form Request classes.

Rules:
- Each major resource should have a dedicated request class for store and update operations where needed
- Use authorize() to delegate to policies
- Keep validation logic declarative and readable
- Use custom messages for user-facing validation errors
- Keep requests focused on validation only, not workflow orchestration

## 8. Policy Rules

Policies should govern access to resources.

Rules:
- Use policies for role- or ownership-based authorization
- Keep policy methods simple and explicit
- Use middleware for coarse role checks and policies for resource-level checks
- Do not rely on UI visibility as the only control mechanism
- Apply the same policy rules consistently in both web and future API endpoints

## 9. Migration Rules

Migrations must be careful and explicit.

Rules:
- One logical change per migration where possible
- Use foreign keys and indexes deliberately
- Keep the migration order consistent with the dependency chain
- Use descriptive names for migrations
- Avoid destructive changes without a migration plan
- Add constraints and indexes that reflect business rules

## 10. Testing Expectations

The codebase should include automated tests for the core workflows.

Required expectations:
- Feature tests for authentication, role access, and major admin workflows
- Feature tests for result locking and publishing rules
- Feature tests for fee payment behavior
- Unit tests for service-layer logic such as grade calculation and report-card preparation
- Tests should validate real behavior and not rely only on mocked internals
- Business-critical rules should be covered before the implementation moves to the next phase

## 11. Documentation Expectations

- Update documentation when introducing new business rules or architectural changes
- Keep comments focused on why something exists rather than repeating what the code already says
- Prefer self-explanatory code over excessive inline comments
- Keep the implementation aligned with this document and the architecture baseline
