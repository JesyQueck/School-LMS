# Architecture Overview

This document establishes the production-readiness baseline for the School Management & Portal System. It is aligned to the PRD and the CLAUDE prompt and is deliberately scoped to a single-school Laravel application.

## 1. Architectural Goal

The system will be implemented as a monolithic Laravel web application for one school deployment. The architecture must remain simple, secure, auditable, and easy to operate on shared hosting while still supporting role-based access, academic integrity, and future expansion.

## 2. Overall System Architecture

The solution is composed of four layers:

1. Presentation Layer
   - Laravel Blade views
   - Tailwind CSS and Alpine.js for UI behavior
   - Controller actions that prepare data and return views or redirects

2. Application Layer
   - Form Requests for validation
   - Service classes for core business operations
   - Policies and middleware for authorization

3. Domain Layer
   - Eloquent models for school entities
   - Relationship and scope logic
   - Business rules handled in services and policies rather than in controllers

4. Infrastructure Layer
   - MySQL database
   - Laravel filesystem storage for photos, documents, and generated reports
   - Queue and mail integration only where justified by operational needs

### Request Flow

Browser -> Route -> Controller -> Form Request -> Service -> Model -> MySQL

## 3. Architectural Principles

The implementation should follow these principles:
- Keep the solution a modular monolith, not a distributed system
- Keep controllers thin and avoid business logic in them
- Put authorization in policies and middleware
- Put validation in Form Request classes
- Put domain workflows in services
- Prefer Eloquent relationships over manual joins
- Preserve academic integrity and audit history
- Avoid premature abstraction such as repositories, event buses, or service containers for every small model

## 4. Service Layer Responsibilities

The service layer is the primary place for business logic. Services should be used for operations that cross model boundaries or require orchestration.

Recommended services:
- ResultService
  - calculate totals and grades
  - enforce lock and publish business rules
  - prepare report-card data
- FeeService
  - record payments
  - calculate outstanding balances
  - generate receipt references
- ReportCardService
  - assemble report-card data
  - generate PDF output
  - apply publish workflow
- AttendanceService
  - summarize attendance records
  - provide class-level summaries

Service design rules:
- Single responsibility per service
- Injectable through the container
- Return structured results or data transfer objects where helpful
- Never contain raw HTTP concerns

## 5. Repository Strategy

A formal repository layer is not required for version 1.0.

Recommended approach:
- Use Eloquent models directly from services and controllers
- Place reusable query logic in model scopes or query builders
- Introduce repositories only if query complexity becomes excessive or if multiple services begin duplicating data access logic

This avoids unnecessary abstraction for a single-school product.

## 6. Authorization Strategy

Authorization will use middleware and policies together.

### Roles
- admin
- teacher
- parent
- student

### Enforcement Model
- Route-level checks through middleware such as role:admin and role:teacher
- Resource-level checks through policies for actions such as view, update, publish, and lock
- UI-level restrictions should never be the only control mechanism

### Required Authorization Rules
- Teachers may only work with assigned class-subjects and their own timetable data
- Parents may only view children’s results after publication
- Students may only view their own results after publication
- Admins may lock, publish, and manage core school records

## 7. Validation Strategy

All incoming request data must be validated through Form Request classes.

### Validation Responsibilities
- Form Requests validate structure, required fields, data types, lengths, dates, and uniqueness
- Services enforce business rules that are not purely structural
- Controllers remain responsible only for orchestration and response handling

### Business Rule Boundaries
- A Form Request validates that a student admission number is present and unique
- A service validates whether a result can be edited based on the current term lock state

## 8. Core Business Rules

The following rules must be part of the implementation contract and should be tested explicitly:
- Only one academic session may be marked current at a time
- Only one term may be marked current per session
- A result row must be unique for student, subject/class-subject, and term
- Results cannot be edited after the term is locked unless an admin explicitly reopens the workflow
- Report cards cannot be published unless the relevant term is locked
- Published report cards should remain immutable unless an admin explicitly republishes after correction
- Fee obligations should be unique per student, fee type, and term
- Payments must not exceed the expected amount for a fee obligation
- Attendance should not allow duplicate marks for the same student and date
- Teacher assignments must remain tied to valid class-subject relationships

## 9. Transactional Boundaries

The following workflows should use database transactions:
- Payment recording
- Result submission and lock/publish transitions
- Report-card generation and publication
- Any workflow that updates multiple related tables in a single operation

## 10. Folder Structure

The implementation should follow a clear module-based structure:

```text
app/
  Http/
    Controllers/
      Admin/
      Teacher/
      Parent/
      Student/
      Auth/
    Middleware/
    Requests/
  Models/
  Policies/
  Services/
  Support/
resources/
  views/
    layouts/
    public/
    admin/
    teacher/
    parent/
    student/
```

## 11. Laravel and Production Conventions

The project should target Laravel 13 conventions on PHP 8.5 and use the following practices:
- Use route model binding where appropriate
- Use eager loading to avoid N+1 query issues
- Use pagination for long lists
- Use queue jobs only for expensive tasks such as report-card PDF generation or bulk email dispatch, not for every write action
- Use explicit database constraints and indexes rather than relying on application logic alone
- Avoid overusing events and observers for simple CRUD workflows

## 12. Performance Considerations

The application must remain responsive for a single-school deployment, not a large enterprise platform.

Recommended practices:
- Add indexes to foreign keys and frequently filtered columns
- Use eager loading for relationships in list and dashboard views
- Paginate large result sets instead of loading everything at once
- Avoid N+1 queries in student, fee, and report-card views
- Cache static reference data such as academic sessions and fee types when appropriate
- Use queues for heavy report generation and email dispatch only if the workload grows

## 13. Security Considerations

The implementation must treat security as a first-class requirement:
- Enforce CSRF on all state-changing forms
- Hash passwords with Laravel’s secure helpers
- Enforce role-based routing and resource authorization
- Rate-limit authentication and sensitive actions
- Restrict file uploads by type and size and store them outside the public root
- Keep audit logs for sensitive actions such as result changes, fee payments, publication, and user deactivation
- Never expose internal details in validation or error responses

## 14. Known Architectural Issues and Resolution Notes

The PRD and the CLAUDE prompt contain a few schema-level differences that should be resolved explicitly in the implementation plan.

### Subject Normalization
The PRD introduces class_subjects and teacher_class_subjects as normalized association tables, while the CLAUDE prompt uses a simpler subject model. The implementation should adopt the PRD structure because it is more expressive for timetable, result-entry, and teacher-assignment workflows.

### Payment Model
The PRD describes fee obligations and separate payment transactions. The implementation should follow that structure so that payment history remains explicit and auditable.

### Result Publication Model
The lock and publish workflow should be implemented as separate states to preserve academic control and parent/student visibility rules.

## 15. Recommended Implementation Direction

The system should be built as a modular monolith with clear role-based boundaries. The initial implementation should prioritize correctness, auditability, and security over premature abstraction. Laravel 13 conventions, explicit validation, service-based business logic, and strong database constraints should be used from the start.
