# Database Design

This document defines the production-ready database baseline for the system. It is derived from the PRD and the CLAUDE implementation guidance and reflects a normalized relational model for a single school.

## 1. Design Principles

- Normalize academic and administrative data
- Preserve historical records for results, fees, and report cards
- Preserve referential integrity through foreign keys
- Keep operational rules in the application layer rather than in the database alone
- Support one current academic session and one current term at a time
- Favor explicit constraints and indexes over reliance on application-only checks

## 2. Final Schema Overview

The system uses the following core entities:

- users
- academic_sessions
- terms
- classes
- subjects
- class_subjects
- teachers
- teacher_class_subjects
- students
- parents
- parent_student
- fee_types
- student_fees
- payments
- results
- attendance
- timetable
- announcements
- report_cards
- audit_logs

## 3. Entity Relationship Overview

### Core Relationships
- One academic session contains many terms
- One class can have many students and many timetable entries
- One subject can be assigned to many classes through class_subjects
- One teacher can teach many class_subjects through teacher_class_subjects
- One student belongs to one class at a time, while historical academic records remain preserved in results and report cards
- One parent can have many linked students through parent_student
- One student can have many fee obligations and many payments
- One term can have many results and many report cards

## 4. Recommended Table Structure

### users
- id
- name
- email
- password
- role
- phone
- profile_photo
- is_active
- remember_token
- created_at
- updated_at

### academic_sessions
- id
- name
- start_date
- end_date
- is_current
- created_at
- updated_at

### terms
- id
- session_id
- name
- start_date
- end_date
- is_current
- created_at
- updated_at

### classes
- id
- name
- form_teacher_id
- created_at
- updated_at

### subjects
- id
- name
- created_at
- updated_at

### class_subjects
- id
- class_id
- subject_id
- is_compulsory
- created_at
- updated_at

### teachers
- id
- user_id
- employee_id
- qualification
- phone
- created_at
- updated_at

### teacher_class_subjects
- id
- teacher_id
- class_subject_id
- assigned_at
- is_active
- created_at
- updated_at

### students
- id
- user_id
- admission_no
- class_id
- house
- gender
- state_of_origin
- date_of_birth
- blood_group
- emergency_contact
- emergency_phone
- status
- created_at
- updated_at

### parents
- id
- user_id
- occupation
- created_at
- updated_at

### parent_student
- parent_id
- student_id
- created_at
- updated_at

### fee_types
- id
- name
- amount
- term_id
- class_id
- created_at
- updated_at

### student_fees
- id
- student_id
- fee_type_id
- term_id
- amount_expected
- status
- created_at
- updated_at

### payments
- id
- student_fee_id
- receipt_number
- amount_paid
- payment_method
- payment_date
- recorded_by
- created_at
- updated_at

### results
- id
- student_id
- class_subject_id
- term_id
- ca_score
- exam_score
- total
- grade
- remark
- submitted_by
- is_locked
- created_at
- updated_at

### attendance
- id
- student_id
- class_id
- term_id
- date
- status
- marked_by
- created_at
- updated_at

### timetable
- id
- class_subject_id
- day
- start_time
- end_time
- created_at
- updated_at

### announcements
- id
- title
- body
- created_by
- target_role
- created_at
- updated_at

### report_cards
- id
- student_id
- term_id
- class_teacher_remark
- principal_remark
- position_in_class
- total_students_in_class
- next_term_begins
- is_published
- generated_at
- created_at
- updated_at

### audit_logs
- id
- user_id
- action
- target_model
- target_id
- old_value
- new_value
- ip_address
- created_at
- updated_at

## 5. Recommended Data Types and Constraints

- Use decimal columns for scores and money values to avoid floating-point errors
- Use unsigned decimal or integer values for monetary amounts where possible
- Store role values as a restricted string or enum in the application layer
- Use unique constraints where the business rule requires uniqueness
- Use foreign keys for all relationships that should remain consistent

## 6. Relationships

### One-to-Many
- academic_sessions -> terms
- classes -> students
- classes -> attendance
- terms -> results
- terms -> report_cards
- students -> results
- students -> attendance
- students -> student_fees
- fee_types -> student_fees
- student_fees -> payments
- users -> audit_logs

### Many-to-Many
- classes <-> subjects through class_subjects
- teachers <-> class_subjects through teacher_class_subjects
- parents <-> students through parent_student

## 7. Index Recommendations

The following indexes are recommended for production readiness:
- users.email unique
- users.role
- academic_sessions.is_current
- terms.session_id and terms.is_current
- classes.form_teacher_id
- class_subjects.class_id and class_subjects.subject_id
- teacher_class_subjects.teacher_id and teacher_class_subjects.class_subject_id
- students.class_id and students.admission_no unique
- parent_student.parent_id and parent_student.student_id
- student_fees.student_id, fee_type_id, term_id unique
- payments.student_fee_id and payment_date
- results.student_id, term_id, class_subject_id unique
- attendance.date, class_id, student_id
- report_cards.student_id and term_id unique
- audit_logs.target_model, target_id, created_at

## 8. Constraints and Business Rules

Recommended constraints:
- users.role must be restricted to the four supported values
- academic_sessions only one row may have is_current = true
- terms only one row per session may have is_current = true
- class_subjects should enforce a unique combination of class_id and subject_id
- teacher_class_subjects should enforce a unique combination of teacher_id and class_subject_id
- results should enforce a unique combination of student_id, class_subject_id, and term_id
- report_cards should enforce a unique combination of student_id and term_id
- payments should require a positive amount
- payment amounts should not exceed the outstanding balance for the associated fee obligation

## 9. Cascade Rules

Recommended cascade behavior:
- Deleting a user should be handled carefully because it may affect teachers, students, parents, and audit entries; a strict delete should be avoided in favor of disabling the account where possible
- Deleting a class should not automatically delete students; instead, prevent deletion while linked records exist
- Deleting a term should be prevented if academic results or report cards exist
- Deleting a student should cascade to linked parent_student records and related fee, result, and attendance records where appropriate, but this should be reviewed during implementation
- Deleting a fee type should be prevented if fee obligations already exist

## 10. Historical Data Strategy

The database must preserve academic history.

Recommended policy:
- Results are stored as immutable snapshots for each term and subject
- Report cards are generated as snapshots and should not be recalculated from live results after publication
- Historical grades and positions must remain unchanged once published
- The system should keep current academic relationship links while archival data remains intact in results and report card tables

## 11. Soft Delete Policy

The initial release should avoid soft deletes for core academic and financial tables because historical records and audit integrity are critical.

Recommended policy:
- Use explicit status values such as is_active, status, and is_published instead of soft deletes for core entities
- Disable user accounts rather than deleting them
- Preserve records for historical reporting and audit purposes
- Soft deletes may be introduced later only for non-critical reference data if the need arises
