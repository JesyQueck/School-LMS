# School Management & Portal System

> **Product Requirements Document (PRD)**

---

## Project Information

| Item | Value |
|------|-------|
| **Deployment** | Single-School Deployment |
| **Target Users** | Nigerian Secondary School |
| **Version** | 1.0 |
| **Status** | Initial Draft |
| **Technology Stack** | Laravel • MySQL • Blade • Tailwind CSS |
| **Timeline** | 13 Weeks |
| **Primary AI Assistant** | Claude Code |
| **Architecture Goal** | Production-ready Single-School Management System |

---

# Table of Contents

1. Project Overview
2. Goals
3. User Roles
4. Feature Modules
5. Database Design
6. Nigerian Grading Scale
7. Technical Stack
8. Build Timeline
9. Security Requirements
10. Out of Scope

---

# 1. Project Overview

## Purpose

The School Management & Portal System is a full-stack web application designed specifically for a Nigerian secondary school.

It replaces disconnected tools such as:

- WhatsApp communication
- Excel spreadsheets
- Paper attendance books
- Manual report cards
- Parent phone calls

with a centralized web platform where each stakeholder accesses only the information relevant to them.

The system is intended for **single-school deployment** and is **not** a multi-tenant SaaS platform.

The initial objective is to produce a high-quality demonstration system for client presentation before evolving into a production-ready application.

---

# 2. Goals

The project aims to:

- Provide the school with a modern public website.
- Centralize academic and administrative records.
- Manage students, teachers, parents, classes, and subjects.
- Generate printable PDF report cards.
- Allow parents to monitor student performance online.
- Reduce administrative workload.
- Improve communication between the school and stakeholders.
- Maintain secure audit logs for sensitive operations.

---

# 3. User Roles

## Administrator

### Responsibilities

- Full system access
- Student management
- Staff management
- Class management
- Subject management
- Fee management
- Attendance oversight
- Result management
- Timetable management
- Academic session management
- Announcement management
- User account management
- Audit log monitoring

---

## Teacher

### Responsibilities

- View assigned classes
- View assigned subjects
- Record attendance
- Submit Continuous Assessment (CA) scores
- Submit Examination scores
- View timetable
- View announcements

---

## Parent

### Responsibilities

- View enrolled children
- View report cards
- View attendance
- View fee status
- View announcements

---

## Student

### Responsibilities

- View dashboard
- View report cards
- Download PDF report card
- View attendance
- View timetable
- View announcements

---

# 4. Feature Modules

The system consists of two primary components:

1. Public Website
2. School Management Portal

---

# 4.1 Public Website

## Objective

Provide the school with a professional online presence.

### Features

- [ ] Responsive Homepage
- [ ] Hero Section
- [ ] School Motto
- [ ] Quick Statistics
- [ ] About Page
- [ ] School History
- [ ] Vision & Mission
- [ ] Academic Programs
- [ ] Gallery
- [ ] News & Achievements
- [ ] Contact Page
- [ ] Admissions Information
- [ ] Contact Form

---

# 4.2 Authentication

## Objective

Provide secure authentication and authorization.

### Features

- [ ] Single Login Page
- [ ] Role-Based Dashboard Redirect
- [ ] Password Reset via Email
- [ ] First Login Password Change
- [ ] Remember Me
- [ ] Session Timeout
- [ ] Logout
- [ ] Account Activation / Deactivation

---

# 4.3 Administrator Module

## Student Management

- [ ] Create Students
- [ ] Edit Students
- [ ] View Students
- [ ] Deactivate Students
- [ ] Assign Classes
- [ ] Upload Student Photograph
- [ ] Record Emergency Contact
- [ ] Bulk Import Students via Excel

---

## Staff Management

- [ ] Create Teachers
- [ ] Edit Teachers
- [ ] Assign Subjects
- [ ] Assign Form Teachers

---

## Class Management

- [ ] Create Classes
- [ ] Edit Classes
- [ ] Assign Form Teachers
- [ ] View Class Lists

---

## Academic Session & Term Management

- [ ] Create Academic Sessions
- [ ] Configure Terms
- [ ] Set Current Session
- [ ] Set Current Term
- [ ] Configure Academic Calendar

---

## Fee Management

- [ ] Define Fee Types
- [ ] Record Payments
- [ ] Generate Receipts
- [ ] View Payment Status
- [ ] Track Outstanding Fees

---

## Result Management

- [ ] Enter CA Scores
- [ ] Enter Examination Scores
- [ ] Automatic Total Calculation
- [ ] Automatic Grade Calculation
- [ ] Lock Results
- [ ] Publish Results
- [ ] Generate Report Cards

---

## Attendance Management

- [ ] Daily Attendance
- [ ] Attendance Summary
- [ ] Class Attendance Reports

---

## Timetable Management

- [ ] Create Timetable
- [ ] Assign Teachers
- [ ] Assign Subjects
- [ ] Manage Time Slots

---

## Announcement Management

- [ ] Create Announcements
- [ ] Target by User Role
- [ ] Schedule Announcements

---

## Dashboard

Display:

- Total Students
- Total Teachers
- Total Parents
- Fee Collection Summary
- Attendance Summary
- Recent Activities
- Quick Statistics

---

# 4.4 Teacher Module

### Features

- [ ] Dashboard
- [ ] View Assigned Classes
- [ ] View Assigned Subjects
- [ ] Record Attendance
- [ ] Submit Results
- [ ] Edit Results (Before Lock)
- [ ] View Timetable
- [ ] View Announcements

---

# 4.5 Parent Module

### Features

- [ ] Dashboard
- [ ] View Children
- [ ] View Report Cards
- [ ] View Attendance
- [ ] View Fee Status
- [ ] View Announcements

---

# 4.6 Student Module

### Features

- [ ] Dashboard
- [ ] View Personal Results
- [ ] Download Report Card PDF
- [ ] View Attendance
- [ ] View Timetable
- [ ] View Announcements

---

# 5. Database Design

## Overview

The database follows a normalized relational design suitable for a production-ready single-school deployment.

### Design Principles

- Eliminate unnecessary data duplication.
- Preserve historical academic records.
- Maintain referential integrity.
- Support future expansion without major schema changes.
- Keep business rules in the service layer rather than the database where appropriate.

---

# Core Tables

## Users

Stores all authenticated users.

| Column | Description |
|---------|-------------|
| id | Primary Key |
| name | Full Name |
| email | Login Email |
| password | Hashed Password |
| role | admin, teacher, parent, student |
| is_active | Account Status |
| timestamps | Laravel timestamps |

---

## Students

Stores student profiles.

| Column | Description |
|---------|-------------|
| id | Primary Key |
| user_id | Linked User |
| admission_number | Unique Admission Number |
| class_id | Current Class |
| house | School House |
| admission_date | Admission Date |
| genotype | Student Genotype |
| emergency_contact | Emergency Contact |
| photo | Profile Photo |
| timestamps | Laravel timestamps |

---

## Teachers

Stores teacher profiles.

- employee_id
- qualification
- phone
- user_id

---

## Parent Profiles

Stores parent information.

- user_id
- occupation
- phone
- address

---

## Parent Student

Many-to-many relationship between parents and students.

| Column |
|----------|
| parent_id |
| student_id |

---

## Classes

Represents school classes.

Examples:

- JSS1A
- JSS1B
- SS2A
- SS3B

Columns

- class_name
- class_level
- form_teacher_id
- timestamps

---

## Subjects

Master list of school subjects.

Examples

- Mathematics
- English Language
- Biology
- Physics

Columns

- code
- name
- timestamps

A subject exists only once in the system.

---

## Class Subjects

Defines which subjects are offered by each class.

Columns

- class_id
- subject_id
- is_compulsory

Constraint

Unique

(class_id, subject_id)

---

## Teacher Class Subjects

Defines which teacher teaches a subject to a class.

Columns

- teacher_id
- class_subject_id
- assigned_at
- is_active

Constraint

Unique

(teacher_id, class_subject_id)

---

## Academic Sessions

Example

2025 / 2026

Columns

- session_name
- is_current

Business Rule

Only one session may be current.

---

## Terms

Examples

- First Term
- Second Term
- Third Term

Columns

- session_id
- term_name
- start_date
- end_date

Result Locking

- results_locked
- results_locked_at
- results_locked_by

Business Rule

Only one term may be current.

---

## Results

Stores academic performance.

Columns

- student_id
- class_subject_id
- term_id
- ca_score
- exam_score
- total
- grade
- remark
- submitted_by
- timestamps

Constraint

Unique

(student_id, class_subject_id, term_id)

Business Rules

- Historical grades never change after publication.
- Total, Grade and Remark are stored as academic snapshots.

---

## Report Cards

Represents finalized academic reports.

Columns

- student_id
- term_id
- class_teacher_remark
- principal_remark
- position_in_class
- total_students
- generated_at
- is_published
- published_at
- published_by

Constraint

Unique

(student_id, term_id)

---

## Fee Types

Examples

- Tuition
- PTA
- Examination Levy
- Development Levy

Columns

- name
- amount
- description

---

## Student Fees

Represents the fee obligation for a student.

Columns

- student_id
- fee_type_id
- term_id
- amount_expected
- status

Business Rule

One obligation per fee type per term.

---

## Payments

Stores every payment transaction.

Columns

- student_fee_id
- receipt_number
- amount_paid
- payment_method
- payment_date
- recorded_by

Business Rules

- Multiple payments are allowed.
- Receipt numbers are generated automatically.
- Complete payment history is preserved.

---

## Attendance

Stores daily attendance.

Columns

- student_id
- class_id
- attendance_date
- status
- recorded_by

Business Rule

Attendance retains the student's class historically.

---

## Timetable

Stores school timetable.

Columns

- class_subject_id
- day_of_week
- start_time
- end_time
- room

Business Rule

Monday–Friday only.

---

## Announcements

Stores school announcements.

Columns

- title
- body
- created_by

---

## Announcement Roles

Allows announcements to target multiple user roles.

Columns

- announcement_id
- role

Examples

- Admin
- Teacher
- Parent
- Student

---

## Audit Logs

Tracks all sensitive system activities.

Columns

- user_id
- action
- table_name
- record_id
- old_values
- new_values
- ip_address
- user_agent
- created_at

Tracked Actions

- Student Updates
- Result Changes
- Fee Records
- Account Changes
- Publish / Unpublish
- Lock / Unlock
- Login Events

---

# 6. Nigerian Grading Scale

| Score | Grade | Remark | WAEC Equivalent |
|--------|-------|---------|-----------------|
| 75–100 | A | Excellent | A1 |
| 70–74 | B | Very Good | B2 |
| 65–69 | B | Good | B3 |
| 60–64 | C | Credit | C4 |
| 55–59 | C | Credit | C5 |
| 50–54 | C | Credit | C6 |
| 45–49 | D | Pass | D7 |
| 40–44 | E | Pass | E8 |
| 0–39 | F | Fail | F9 |

Business Rule

Grades are stored as historical snapshots.

Changing the grading scale in future academic sessions must never modify historical report cards.

---


# 7. Technical Stack

## Architecture

The system follows a layered Laravel architecture designed for maintainability, scalability, and production deployment.

```
Browser
    │
Blade + Tailwind CSS
    │
Laravel Controllers
    │
Form Requests
    │
Service Layer
    │
Policies / Middleware
    │
Repositories (Optional)
    │
Eloquent Models
    │
MySQL Database
```

---

## Technology Stack

| Layer | Technology | Purpose |
|--------|------------|---------|
| Backend | Laravel 12 (PHP 8.4+) | Business Logic |
| Frontend | Blade | Server-side Rendering |
| Styling | Tailwind CSS | Responsive UI |
| JavaScript | Alpine.js | Lightweight Interactivity |
| Database | MySQL 8+ | Persistent Storage |
| Authentication | Laravel Breeze | Authentication |
| Authorization | Laravel Policies + Middleware | Access Control |
| PDF Generation | DomPDF | Report Cards |
| Excel | Laravel Excel | Import / Export |
| File Storage | Laravel Storage | Photos & Documents |
| Queue | Laravel Queue | Background Jobs |
| Email | SMTP | Password Reset & Notifications |
| Logging | Laravel Log | Error Logging |
| Hosting | cPanel Shared Hosting | Production Deployment |

---

## Development Standards

### Coding Standards

- Follow PSR-12 Coding Standard
- Use Laravel Best Practices
- Keep Controllers Thin
- Business Logic belongs in Services
- Authorization belongs in Policies
- Validation belongs in Form Requests
- Avoid duplicated logic
- Prefer Dependency Injection
- Prefer Eloquent Relationships over manual joins where appropriate

---

## Laravel Packages

### Required

- Laravel Breeze
- Laravel DomPDF
- Laravel Excel (Maatwebsite)

### Optional

- Laravel Debugbar (Development)
- Laravel Telescope (Development)
- Spatie Permission (Future if RBAC expands)

---

## File Storage

Use Laravel Public Storage.

Examples

- Student Photos
- Teacher Photos
- Generated PDFs
- Uploaded Documents

---

## Email

SMTP

Used for:

- Password Reset
- Account Notifications
- Future Email Announcements

---

## Security

- CSRF Protection
- XSS Protection
- SQL Injection Protection
- Password Hashing
- Validation via Form Requests
- Authorization via Policies
- HTTPS on Production

---

# 8. Development Roadmap

## Phase 1 — Foundation

### Week 1

### Project Setup

- [ ] Create Laravel Project
- [ ] Configure Environment
- [ ] Configure Git
- [ ] Install Breeze
- [ ] Configure Tailwind
- [ ] Configure Authentication
- [ ] Configure Role Middleware

Deliverable

Working authentication system.

---

## Phase 2 — Public Website

### Week 2

Build

- [ ] Homepage
- [ ] About
- [ ] Programs
- [ ] Gallery
- [ ] Contact
- [ ] Admissions

Deliverable

Complete public-facing website.

---

## Phase 3 — Core Administration

### Weeks 3–4

Build

- [ ] Student Management
- [ ] Parent Management
- [ ] Teacher Management
- [ ] Class Management
- [ ] Subject Management

Deliverable

Core school data management.

---

## Phase 4 — Academic Structure

### Week 5

Build

- [ ] Academic Sessions
- [ ] Terms
- [ ] Subject Allocation
- [ ] Teacher Assignments
- [ ] Timetable Foundation

Deliverable

Academic structure completed.

---

## Phase 5 — Finance

### Week 6

Build

- [ ] Fee Types
- [ ] Student Fees
- [ ] Payments
- [ ] Receipts

Deliverable

Fee management system.

---

## Phase 6 — Results

### Week 7

Build

- [ ] Continuous Assessment
- [ ] Examination Scores
- [ ] Automatic Totals
- [ ] Grade Calculation
- [ ] Lock Results

Deliverable

Academic result system.

---

## Phase 7 — Report Cards

### Week 8

Build

- [ ] Report Card Generator
- [ ] PDF Export
- [ ] Position Calculation
- [ ] Publish Workflow

Deliverable

Professional report cards.

---

## Phase 8 — Teacher Portal

### Week 9

Build

- [ ] Dashboard
- [ ] Attendance
- [ ] Result Entry
- [ ] Timetable

Deliverable

Teacher portal.

---

## Phase 9 — Parent & Student Portals

### Week 10

Build

- [ ] Parent Dashboard
- [ ] Student Dashboard
- [ ] Report Card Viewing
- [ ] Attendance Viewing
- [ ] Fee Status

Deliverable

Parent and student portals.

---

## Phase 10 — Communication

### Week 11

Build

- [ ] Announcements
- [ ] Notifications
- [ ] Excel Import
- [ ] Excel Export

Deliverable

Communication tools.

---

## Phase 11 — Audit & Testing

### Week 12

Build

- [ ] Audit Logs
- [ ] Feature Tests
- [ ] Bug Fixes
- [ ] Seed Demo Data

Deliverable

Production-ready application.

---

## Phase 12 — Deployment

### Week 13

Tasks

- [ ] Production Build
- [ ] cPanel Deployment
- [ ] SSL Configuration
- [ ] Final Testing
- [ ] Performance Optimization
- [ ] Documentation

Deliverable

Production deployment ready for client delivery.

---

# 9. Security Requirements

## Security Objectives

The system must ensure confidentiality, integrity, and availability of all school data while following Laravel security best practices.

---

## Authentication

### Requirements

- [ ] Secure Login
- [ ] Password Hashing (Laravel Hash)
- [ ] Password Reset via Email
- [ ] Remember Me
- [ ] Session Timeout
- [ ] Logout from All Devices (Future Enhancement)

---

## Authorization

Access must be controlled using **Role-Based Access Control (RBAC).**

### Roles

- Administrator
- Teacher
- Parent
- Student

Users may only access resources permitted by their assigned role.

Laravel Policies and Middleware shall enforce authorization.

---

## Input Validation

All incoming requests must use **Laravel Form Request Validation**.

Requirements:

- Required field validation
- Data type validation
- Maximum length validation
- File validation
- Date validation
- Unique field validation
- Custom business rule validation

---

## CSRF Protection

All forms must use Laravel CSRF protection.

---

## SQL Injection Protection

All database operations must use:

- Eloquent ORM
- Query Builder

Raw SQL should only be used when absolutely necessary.

---

## XSS Protection

- Escape all Blade output using `{{ }}`.
- Sanitize rich text inputs where applicable.

---

## File Upload Security

Allowed uploads include:

- Student photographs
- Teacher photographs
- School documents

Validation requirements:

- Allowed MIME types only
- Maximum file size
- Randomized file names
- Store outside the public root using Laravel Storage

---

## Password Requirements

Passwords must:

- Be hashed using Laravel Hash
- Meet minimum complexity requirements
- Never be stored in plain text

---

## HTTPS

Production deployment must enforce HTTPS.

Requirements:

- SSL Certificate
- Secure Cookies
- HSTS (if supported by hosting)

---

## Login Protection

Protect against brute-force attacks using Laravel Rate Limiting.

Example:

- 5 failed attempts
- Temporary lockout
- Automatic reset after cooldown

---

## Audit Logging

Sensitive operations must be recorded.

Examples:

- User Login
- User Logout
- Student Updates
- Teacher Updates
- Parent Updates
- Result Changes
- Attendance Changes
- Fee Payments
- Result Locking
- Result Publishing
- User Deactivation

Each audit log should include:

- User
- Action
- Affected Table
- Record ID
- Previous Values
- New Values
- IP Address
- Browser Information
- Timestamp

---

## Backup Strategy

Production database should support:

- Daily Automated Backups
- Weekly Full Backup
- Monthly Archive Backup

Backup files should be encrypted and stored securely.

---

## Environment Configuration

Sensitive information must never be committed to version control.

Store the following in `.env`:

- Database Credentials
- Mail Credentials
- App Key
- API Keys
- Queue Configuration

---

## Academic Data Integrity

Business rules:

- Teachers cannot edit results after the term is locked.
- Only administrators may unlock a term.
- Report cards cannot be published unless the term is locked.
- Published report cards become immutable.
- Corrections require:
  1. Unpublish Report Card
  2. Make Correction
  3. Regenerate Report Card
  4. Publish Again

---

# 10. Out of Scope

The following features are intentionally excluded from Version 1.0.

## Multi-Tenant SaaS

Not included.

The application serves a single school only.

---

## Online Fee Payment

Not included.

Payments are recorded manually by administrators.

Future versions may integrate:

- Paystack
- Flutterwave
- Moniepoint

---

## Mobile Applications

Native Android and iOS applications are not part of Version 1.

The web application must instead be fully responsive.

---

## Student Admission Portal

Online admission applications are excluded.

Admissions are managed offline.

---

## Library Management

Library inventory and borrowing are outside the scope.

---

## Hostel Management

Boarding and hostel management are excluded.

---

## Inventory Management

School inventory management is not included.

---

## Payroll

Teacher payroll is excluded.

---

## Human Resource Management

Employee recruitment and HR workflows are excluded.

---

## Accounting

Full accounting and bookkeeping are excluded.

Only fee management is included.

---

## Internal Messaging

Real-time messaging or chat between users is excluded.

Announcements will serve as the communication mechanism.

---

# Revision History

| Version | Date | Description |
|----------|------|-------------|
| 1.0 | Initial | Initial PRD created |
| 1.1 | Architecture Review | Database normalized and production improvements |
| 1.2 | Final | Approved implementation blueprint |

---

# Success Criteria

The project is considered complete when:

- ✅ Public website is fully functional.
- ✅ Authentication and RBAC are implemented.
- ✅ All user roles function correctly.
- ✅ Student records are fully manageable.
- ✅ Teacher workflows operate correctly.
- ✅ Parent and student portals display accurate information.
- ✅ Fee management supports multiple payments.
- ✅ Report cards generate correctly as PDFs.
- ✅ Audit logging is operational.
- ✅ Security requirements are satisfied.
- ✅ Production deployment is successful.

---

# Future Enhancements (Version 2)

Potential improvements include:

- Online fee payment integration
- SMS notifications
- Email announcements
- Mobile applications
- Student admission portal
- Library management
- Hostel management
- Payroll
- Learning Management System (Assignments & Exams)
- CBT (Computer-Based Testing)
- API integrations
- Multi-school SaaS support

---

# Implementation Notes

This PRD serves as the primary source of truth for the project.

Before implementation:

1. Finalize the database schema.
2. Review and approve the ERD.
3. Complete architectural decisions.
4. Generate migrations.
5. Generate Eloquent models.
6. Implement services and policies.
7. Build features incrementally according to the roadmap.

No implementation should begin until the architecture has been approved.

---