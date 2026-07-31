# Architecture Decision Records

This document records the architectural decisions that have already been made in the PRD and the CLAUDE guidance. Each decision is written to preserve intent and to make future implementation choices easier to evaluate.

## ADR-001: Single-School Monolithic Architecture
- Status: Accepted
- Context: The project is intended for one school, not a multi-tenant SaaS platform.
- Decision: Implement the system as a single Laravel monolithic application with role-based modules for admin, teacher, parent, and student.
- Consequences: The architecture stays simpler to operate, deploy, and maintain for a single-school environment.

## ADR-002: Shared users Table with Role-Based Access
- Status: Accepted
- Context: The PRD and CLAUDE both require exactly four roles and a shared authentication model.
- Decision: Store all authenticated users in a single users table and distinguish them with a role column.
- Consequences: Authentication and authorization remain simple while still providing clear role boundaries.

## ADR-003: Subject Normalization with class_subjects and teacher_class_subjects
- Status: Accepted
- Context: The PRD explicitly requires normalized subject allocation via class_subjects and teacher_class_subjects.
- Decision: Use a master subjects catalog, associate subjects to classes through class_subjects, and assign teacher coverage through teacher_class_subjects.
- Consequences: The data model supports timetable planning, subject allocation, and result entry with less ambiguity than a flat subject-per-class approach.

## ADR-004: Manual Fee Payment Recording
- Status: Accepted
- Context: The PRD explicitly excludes online payments and requires fee handling to be recorded manually.
- Decision: Use fee_types, student_fees, and payments to model obligations and transaction history without introducing a payment gateway.
- Consequences: The fee workflow remains simple and suitable for a school admin environment while preserving payment history.

## ADR-005: Separate Lock and Publish Workflow for Results
- Status: Accepted
- Context: The PRD and CLAUDE require that results be locked before publication and that parents and students only see published results.
- Decision: Implement two distinct states: results can be locked by an admin, and report cards or result visibility can be published later.
- Consequences: The system preserves academic control and protects the visibility of results until the proper approval step is complete.

## ADR-006: Historical Academic Snapshots
- Status: Accepted
- Context: The PRD explicitly states that historical grades and report card data must remain unchanged after publication.
- Decision: Store results and report cards as historical snapshots rather than recalculating them dynamically from live data.
- Consequences: The system preserves academic integrity and supports future audits and reporting.

## ADR-007: Current Academic Session and Current Term Model
- Status: Accepted
- Context: The project requires the school to operate in one current academic session and current term at a time.
- Decision: Implement academic_sessions and terms with explicit is_current flags, while allowing historical rows to remain stored.
- Consequences: The application can support current academic context while keeping historical data intact.

## ADR-008: Server-Rendered Blade UI with Tailwind and Alpine
- Status: Accepted
- Context: The PRD specifies Blade, Tailwind CSS, and Alpine.js for the frontend stack.
- Decision: Use Laravel Blade as the primary UI technology with Tailwind CSS for styling and Alpine.js for lightweight interactivity.
- Consequences: The stack remains simple, fast to develop, and well-suited to a shared-hosting deployment model.

## ADR-009: Validation and Authorization in the Application Layer
- Status: Accepted
- Context: The PRD requires validation through Form Requests and authorization through Policies and Middleware.
- Decision: Keep validation in Form Request classes, business rules in services, and authorization in policies and middleware.
- Consequences: The codebase remains modular and easier to test and extend.

## ADR-010: Audit Logging for Sensitive Actions
- Status: Accepted
- Context: The PRD requires audit logging for sensitive changes such as results, fees, student changes, and report card publication.
- Decision: Introduce a dedicated audit_logs table and use it whenever sensitive actions are executed.
- Consequences: The system provides a reliable audit trail for compliance, review, and troubleshooting.

## ADR-011: No Repository Layer in Version 1.0
- Status: Accepted
- Context: The architecture should remain lean and avoid adding abstraction layers that are not needed for a single-school deployment.
- Decision: Use Eloquent models directly from services and controllers rather than introducing repositories in the initial release.
- Consequences: The codebase remains easier to understand and maintain while avoiding unnecessary complexity.

## ADR-012: No Soft Deletes for Core Academic and Financial Records
- Status: Accepted
- Context: Historical integrity and auditability are critical for results, fees, and report cards.
- Decision: Use explicit status fields and account-disable patterns instead of soft deletes for core records.
- Consequences: The system remains auditable and avoids accidental data loss in financial and academic workflows.
