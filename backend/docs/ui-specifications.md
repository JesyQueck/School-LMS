# School LMS — UI Specifications

Version: 1.0.0
Status: Approved for implementation

---

## 1. Admin Dashboard

### Layout
- **Container:** Full-width dashboard grid
- **Header:** Page title "Dashboard" + date range picker + export button
- **Stats Row:** 4 stat cards in a row (students, teachers, classes, attendance rate)
- **Main Grid:** 2 columns — 8/12 main content, 4/12 sidebar
- **Main Content:** Attendance chart + recent activity table
- **Sidebar:** Upcoming events + quick actions

### Sections
1. **Stats Row**
   - 4 `<x-ui.stat-card>` components
   - Labels: Total Students, Total Teachers, Total Classes, Attendance Rate
   - Values with trend indicators
   - Icons: users, graduation-cap, school, calendar-check

2. **Main Content Area**
   - **Chart Card:** Attendance overview line chart (last 7 days)
   - **Recent Activity Card:** Table showing latest 10 activities
     - Columns: Time, User, Action, Target
     - Comfortable density
     - Pagination if more than 10

3. **Sidebar Widgets**
   - **Upcoming Events Card:** List of next 5 events
     - Event name, date, time
   - **Quick Actions Card:** Links to common tasks
     - Add Student, Add Teacher, Take Attendance, Generate Report

### Components
- `<x-ui.stat-card>` for stats
- `<x-ui.chart>` for attendance chart
- `<x-ui.table>` for recent activity
- `<x-ui.card>` for sidebar widgets

### User Interactions
- Date range picker updates chart data
- Export button downloads CSV
- Click activity row navigates to detail
- Quick action links navigate to respective pages

### Empty State
- If no activity: Show empty state with "No recent activity" message

### Loading State
- Skeleton loaders for stats and chart while data loads

### Error State
- Alert component if data fails to load
- Retry button

### Responsive Behavior
- Mobile: Stats stack vertically (1 column)
- Tablet: Stats 2 columns
- Desktop: Stats 4 columns, sidebar below main content
- Chart resizes to container width

---

## 2. Teacher Dashboard

### Layout
- **Container:** Full-width dashboard grid
- **Header:** Page title "Dashboard" + class selector dropdown
- **Stats Row:** 4 stat cards (classes today, students, pending grading, attendance)
- **Main Grid:** 2 columns — 8/12 main, 4/12 sidebar
- **Main Content:** Today's schedule table + class performance chart
- **Sidebar:** Announcements + pending tasks

### Sections
1. **Stats Row**
   - Classes Today, Total Students, Pending Grading, Attendance Rate

2. **Main Content**
   - **Schedule Card:** Table of today's classes
     - Columns: Time, Class, Subject, Room, Attendance Status
     - Click row to navigate to class detail
   - **Performance Chart:** Bar chart of class average scores

3. **Sidebar**
   - **Announcements Card:** Latest 3 announcements
   - **Pending Tasks Card:** List of tasks with due dates
     - Grade assignments, submit reports, etc.

### Components
- `<x-ui.stat-card>`
- `<x-ui.chart>`
- `<x-ui.table>`
- `<x-ui.card>`

### User Interactions
- Class selector filters dashboard data
- Click schedule row to view class details
- Click task to navigate to task detail
- Announcement click to view full announcement

### Empty State
- No classes today: "No classes scheduled for today"
- No pending tasks: "All tasks completed!"

### Loading State
- Skeleton loaders for all cards

### Error State
- Alert with retry button

### Responsive Behavior
- Mobile: Single column layout
- Tablet: 2-column stats, single column content
- Desktop: Full grid layout

---

## 3. Parent Dashboard

### Layout
- **Container:** Full-width dashboard grid
- **Header:** Page title "Dashboard" + child selector dropdown
- **Stats Row:** 4 stat cards (attendance, average grade, fees due, announcements)
- **Main Grid:** 2 columns — 8/12 main, 4/12 sidebar
- **Main Content:** Attendance overview + recent results table
- **Sidebar:** Upcoming events + fee status

### Sections
1. **Stats Row**
   - Attendance Rate, Average Grade, Fees Due, New Announcements

2. **Main Content**
   - **Attendance Card:** Weekly attendance bar chart
   - **Recent Results Card:** Table of last 5 results
     - Columns: Subject, Exam, Score, Grade, Date
     - Click to view detailed result

3. **Sidebar**
   - **Fee Status Card:** Current fee balance, due date, pay button
   - **Upcoming Events Card:** Next 3 school events

### Components
- `<x-ui.stat-card>`
- `<x-ui.chart>`
- `<x-ui.table>`
- `<x-ui.card>`
- `<x-ui.badge>` for fee status (Paid/Unpaid)

### User Interactions
- Child selector updates all dashboard data
- Click result row to view detailed report card
- Pay button opens payment modal
- Event click to view event details

### Empty State
- No results: "No results yet this term"
- No events: "No upcoming events"

### Loading State
- Skeleton loaders

### Error State
- Alert with retry

### Responsive Behavior
- Mobile: Single column
- Tablet: 2-column stats
- Desktop: Full grid

---

## 4. Student Dashboard

### Layout
- **Container:** Full-width dashboard grid
- **Header:** Page title "Dashboard"
- **Stats Row:** 4 stat cards (attendance, average grade, rank, assignments due)
- **Main Grid:** 2 columns — 8/12 main, 4/12 sidebar
- **Main Content:** Performance chart + upcoming assignments
- **Sidebar:** Announcements + timetable

### Sections
1. **Stats Row**
   - Attendance Rate, Average Grade, Class Rank, Assignments Due

2. **Main Content**
   - **Performance Chart:** Line chart of grades over time
   - **Upcoming Assignments Card:** List of next 5 assignments
     - Subject, title, due date, status badge

3. **Sidebar**
   - **Announcements Card:** Latest 3 announcements
   - **Today's Timetable Card:** Schedule for today
     - Time, subject, room, teacher

### Components
- `<x-ui.stat-card>`
- `<x-ui.chart>`
- `<x-ui.card>`
- `<x-ui.badge>` for assignment status

### User Interactions
- Click assignment to view details
- Click announcement to read full
- Click timetable item to navigate to class

### Empty State
- No assignments: "No upcoming assignments"
- No announcements: "No announcements"

### Loading State
- Skeleton loaders

### Error State
- Alert with retry

### Responsive Behavior
- Mobile: Single column
- Tablet: 2-column stats
- Desktop: Full grid

---

## 5. Student Management

### Layout
- **Container:** Full-width
- **Header:** Page title "Students" + breadcrumbs + actions
- **Filter Bar:** Search input + class filter + section filter + add button
- **Content:** Data table with bulk actions
- **Footer:** Pagination

### Sections
1. **Page Header**
   - Title: "Student Management"
   - Breadcrumbs: Admin > Students
   - Actions: Add Student button (primary), Export button (secondary)

2. **Filter Bar**
   - Search input (search by name, admission number)
   - Class select dropdown
   - Section select dropdown
   - Active filters count badge
   - Clear filters button

3. **Bulk Actions Bar**
   - Shows when rows are selected
   - Text: "X students selected"
   - Actions: Delete selected, Export selected, Change class

4. **Data Table**
   - Columns: Checkbox, Photo, Name, Admission No, Class, Section, Gender, Age, Actions
   - Sortable columns: Name, Class, Age
   - Row actions: View, Edit, Delete
   - Pagination: 15 items per page
   - Empty state when no students

5. **Pagination**
   - Page numbers, prev/next, showing X-Y of Z

### Components
- `<x-ui.filter-bar>`
- `<x-ui.bulk-actions>`
- `<x-ui.table>` with checkboxes
- `<x-ui.avatar>` for student photos
- `<x-ui.badge>` for gender
- `<x-ui.pagination>`
- `<x-ui.empty-state>`

### User Interactions
- Search filters table in real-time
- Filters update results
- Select all checkbox selects all visible rows
- Individual checkboxes select rows
- Bulk actions appear when rows selected
- Row click navigates to student detail
- Action buttons open modals (edit, delete)
- Pagination changes page

### Empty State
- No students found: "No students match your filters"
- Action: Clear filters button

### Loading State
- Skeleton table rows while loading
- Spinner in table body

### Error State
- Alert: "Failed to load students"
- Retry button

### Responsive Behavior
- Mobile: Table horizontal scroll, filter bar stacks vertically
- Tablet: Filter bar 2 rows
- Desktop: Filter bar single row

---

## 6. Teacher Management

### Layout
Similar to Student Management with teacher-specific fields.

### Sections
1. **Page Header**
   - Title: "Teacher Management"
   - Actions: Add Teacher, Export

2. **Filter Bar**
   - Search by name, email, employee ID
   - Department filter
   - Status filter (active, on leave)

3. **Data Table**
   - Columns: Photo, Name, Employee ID, Email, Department, Subjects, Classes, Status, Actions
   - Status badge (Active/On Leave)

4. **Pagination**

### Components
Same as Student Management

### User Interactions
Same pattern as Student Management

### Empty State
"No teachers found"

### Loading State
Skeleton rows

### Error State
Alert with retry

### Responsive Behavior
Same as Student Management

---

## 7. Parent Management

### Layout
Similar to Student Management.

### Sections
1. **Page Header**
   - Title: "Parent Management"
   - Actions: Add Parent, Export

2. **Filter Bar**
   - Search by name, email, phone
   - Children count filter

3. **Data Table**
   - Columns: Name, Email, Phone, Children, Occupation, Actions
   - Children shown as comma-separated names

4. **Pagination**

### Components
Same as Student Management

### User Interactions
Same pattern

### Empty State
"No parents found"

### Loading State
Skeleton rows

### Error State
Alert with retry

### Responsive Behavior
Same as Student Management

---

## 8. Attendance

### Layout
- **Container:** Full-width
- **Header:** Page title "Attendance" + date picker + class selector + save button
- **Content:** Attendance table with student rows
- **Footer:** Summary stats

### Sections
1. **Page Header**
   - Title: "Attendance"
   - Date picker (defaults to today)
   - Class selector dropdown
   - Save button (primary)
   - Export button (secondary)

2. **Filter Bar**
   - Section selector (if class has multiple sections)
   - Search student

3. **Attendance Table**
   - Columns: Photo, Name, Admission No, Status (Present/Absent/Late), Remarks
   - Status: Dropdown or radio buttons per student
   - Bulk mark all present/absent buttons
   - Remarks: Optional text input per student

4. **Summary Card**
   - Total students, Present, Absent, Late, Percentage

### Components
- `<x-ui.table>` with custom status cells
- `<x-ui.select>` for status dropdown
- `<x-ui.input>` for remarks
- `<x-ui.button>` for bulk actions
- `<x-ui.badge>` for summary stats

### User Interactions
- Date picker changes date
- Class selector changes class
- Status dropdown changes student status
- Remarks input saves on blur
- Save button saves all attendance
- Bulk mark all sets all to selected status
- Summary updates in real-time as statuses change

### Empty State
No students in class: "No students enrolled in this class"

### Loading State
Skeleton rows for table

### Error State
Alert: "Failed to load attendance"
Retry button

### Responsive Behavior
- Mobile: Table horizontal scroll
- Status columns stack or become dropdowns

---

## 9. Results

### Layout
- **Container:** Full-width
- **Header:** Page title "Results" + exam selector + class selector + publish button
- **Content:** Results table
- **Footer:** Summary statistics

### Sections
1. **Page Header**
   - Title: "Results"
   - Exam type dropdown (Mid-term, Final, Quiz)
   - Class selector
   - Publish results button (primary)
   - Export button (secondary)

2. **Filter Bar**
   - Section selector
   - Search student

3. **Results Table**
   - Columns: Rank, Name, Admission No, Subject 1, Subject 2, ..., Total, Average, Grade, Remarks
   - Sortable: Rank, Total, Average
   - Editable: Click cell to edit score inline
   - Grade auto-calculated based on average

4. **Summary Card**
   - Class average, highest score, lowest score, pass rate

### Components
- `<x-ui.table>` with inline editing
- `<x-ui.select>` for filters
- `<x-ui.badge>` for grades
- `<x-ui.stat-card>` for summary

### User Interactions
- Exam/class selector loads results
- Click cell to edit score
- Grade auto-updates on score change
- Save button persists changes
- Publish button makes results visible to students
- Sort columns by clicking headers

### Empty State
"No results for selected exam/class"
Action: Create new results entry

### Loading State
Skeleton rows

### Error State
Alert: "Failed to load results"
Retry button

### Responsive Behavior
- Mobile: Table horizontal scroll
- Columns may hide less important ones on mobile

---

## 10. Report Cards

### Layout
- **Container:** Full-width
- **Header:** Page title "Report Cards" + term selector + class selector + generate button
- **Content:** Report cards grid + individual card view

### Sections
1. **Page Header**
   - Title: "Report Cards"
   - Term dropdown
   - Class selector
   - Generate button (primary)
   - Download all button (secondary)

2. **Filter Bar**
   - Section selector
   - Search student

3. **Report Cards Grid**
   - Grid of student report cards
   - Each card shows:
     - Student photo and name
     - Class and term
     - Average grade
     - Rank in class
     - Attendance percentage
     - Generate/Download button

4. **Individual Report Card Modal**
   - Detailed report card view
   - All subjects with scores, grades, remarks
   - Teacher comments
   - Principal signature
   - Print button

### Components
- `<x-ui.card>` for report card previews
- `<x-ui.stat-card>` for summary stats on card
- `<x-ui.modal>` for detailed view
- `<x-ui.button>` for actions
- `<x-ui.badge>` for grades

### User Interactions
- Filters update card grid
- Click card to open detailed modal
- Generate button creates report cards
- Download button downloads PDF
- Print button opens print dialog

### Empty State
"No report cards generated for this term"
Action: Generate report cards

### Loading State
Skeleton cards in grid

### Error State
Alert: "Failed to generate report cards"
Retry button

### Responsive Behavior
- Mobile: 1 column grid
- Tablet: 2 columns
- Desktop: 3-4 columns

---

## 11. Fees

### Layout
- **Container:** Full-width
- **Header:** Page title "Fees" + term selector + class selector + add fee button
- **Content:** Fees table
- **Footer:** Summary

### Sections
1. **Page Header**
   - Title: "Fee Management"
   - Term selector
   - Class selector
   - Add Fee button (primary)
   - Export button (secondary)

2. **Filter Bar**
   - Status filter (Paid, Unpaid, Partial)
   - Search student

3. **Fees Table**
   - Columns: Student, Class, Total Fee, Paid, Balance, Status, Due Date, Actions
   - Status badge (Paid/Unpaid/Partial)
   - Actions: View, Record Payment, Send Reminder

4. **Summary Card**
   - Total fees, Total collected, Total pending, Collection rate

### Components
- `<x-ui.table>`
- `<x-ui.badge>` for payment status
- `<x-ui.stat-card>` for summary
- `<x-ui.modal>` for record payment

### User Interactions
- Filters update table
- Click row to view fee details
- Record Payment opens modal to enter payment amount
- Send Reminder sends notification
- Export downloads CSV

### Empty State
"No fees found for selected filters"

### Loading State
Skeleton rows

### Error State
Alert with retry

### Responsive Behavior
- Mobile: Table horizontal scroll
- Action buttons become icon-only on mobile

---

## 12. Payments

### Layout
- **Container:** Full-width
- **Header:** Page title "Payments" + date range + export button
- **Content:** Payments table + chart
- **Footer:** Summary

### Sections
1. **Page Header**
   - Title: "Payment History"
   - Date range picker
   - Export button

2. **Filter Bar**
   - Search by student, receipt number
   - Payment method filter
   - Status filter

3. **Payments Table**
   - Columns: Receipt No, Date, Student, Amount, Method, Status, Actions
   - Status badge (Completed, Pending, Failed)
   - Actions: View receipt, Refund

4. **Payment Chart**
   - Line or bar chart showing payments over time

5. **Summary Card**
   - Total payments, Today's total, This week, This month

### Components
- `<x-ui.table>`
- `<x-ui.chart>`
- `<x-ui.stat-card>`
- `<x-ui.badge>`
- `<x-ui.modal>` for receipt view

### User Interactions
- Date range updates chart and table
- Click receipt to view details
- Refund action opens confirmation modal

### Empty State
"No payments found for selected period"

### Loading State
Skeleton rows + chart placeholder

### Error State
Alert with retry

### Responsive Behavior
- Mobile: Single column, table scroll
- Desktop: Chart + table layout

---

## 13. Announcements

### Layout
- **Container:** Full-width with max-width constraint
- **Header:** Page title "Announcements" + add announcement button
- **Content:** Announcements list or grid
- **Footer:** Pagination

### Sections
1. **Page Header**
   - Title: "Announcements"
   - Add Announcement button (primary)

2. **Filter Bar**
   - Search announcements
   - Category filter (General, Academic, Event, Emergency)
   - Date range

3. **Announcements List**
   - Card layout or list layout toggle
   - Each announcement card:
     - Title
     - Category badge
     - Excerpt
     - Date
     - Author
     - Read more link

4. **Announcement Detail Modal**
   - Full announcement content
   - Attachments
   - Target audience selector

### Components
- `<x-ui.card>` for announcements
- `<x-ui.badge>` for categories
- `<x-ui.modal>` for create/edit/view
- `<x-ui.pagination>`
- `<x-ui.rich-editor>` for content (or simple textarea)

### User Interactions
- Click announcement to view details
- Add button opens create modal
- Edit button opens edit modal
- Delete button with confirmation
- Filters update list
- Pagination for large lists

### Empty State
"No announcements yet"
Action: Create first announcement

### Loading State
Skeleton cards

### Error State
Alert with retry

### Responsive Behavior
- Mobile: Single column cards
- Tablet: 2 columns
- Desktop: 3 columns or list view

---

## 14. Settings

### Layout
- **Container:** Max-width 3xl (narrow, form-focused)
- **Header:** Page title "Settings"
- **Content:** Tabbed form
- **Footer:** Save button

### Sections
1. **Page Header**
   - Title: "Settings"
   - Subtitle: "Manage your school preferences"

2. **Tabs**
   - General Settings
   - Academic Settings
   - Notification Settings
   - Security Settings

3. **Tab Panels**
   - **General:** School name, logo, address, phone, email, timezone, currency
   - **Academic:** Current term, academic year, grading system, pass mark
   - **Notifications:** Email notifications, SMS notifications, push notifications
   - **Security:** Password policy, two-factor authentication, session timeout

4. **Form Actions**
   - Save button (primary)
   - Reset button (secondary)

### Components
- `<x-ui.tabs>`
- `<x-ui.tab>`
- `<x-ui.tab-panel>`
- `<x-ui.input>`
- `<x-ui.select>`
- `<x-ui.checkbox>`
- `<x-ui.radio>`
- `<x-ui.textarea>`
- `<x-ui.alert>` for success/error messages
- `<x-ui.button>`

### User Interactions
- Tab switches content without page reload
- Form validation on submit
- Save persists settings
- Reset reverts to last saved values
- Success alert after save

### Empty State
Not applicable (settings always have values)

### Loading State
Button spinner during save

### Error State
Alert: "Failed to save settings"
Retry button

### Responsive Behavior
- Mobile: Full-width form, tabs scroll horizontally if needed
- Tablet/Desktop: Centered form with comfortable width

---

## 15. Public Website

### Layout
- **Container:** Full-width with max-width constraints
- **Header:** Sticky top navigation
- **Content:** Full-width sections
- **Footer:** Multi-column footer

### Sections
1. **Navigation**
   - Logo
   - Links: Home, About, Academics, Admissions, Contact
   - CTA: Apply Now / Login

2. **Hero Section**
   - Full-width background image/gradient
   - Headline: "Welcome to [School Name]"
   - Subheadline: "Excellence in Education"
   - CTA buttons: Learn More, Apply Now
   - Stats: Students, Teachers, Years of Excellence

3. **About Section**
   - School overview
   - Mission/Vision cards
   - Values list

4. **Academics Section**
   - Programs offered
   - Curriculum highlights
   - Facilities

5. **Admissions Section**
   - Admission process steps
   - Requirements list
   - Application CTA

6. **Contact Section**
   - Contact form
   - Address, phone, email
   - Map placeholder

7. **Footer**
   - School logo and info
   - Quick links
   - Contact info
   - Social media links
   - Copyright

### Components
- `<x-ui.button>` for CTAs
- `<x-ui.card>` for program cards
- `<x-ui.input>` for contact form
- `<x-ui.badge>` for highlights
- `<x-ui.stat-card>` for hero stats

### User Interactions
- Smooth scroll to sections
- Form submission with validation
- Mobile menu toggle
- Theme toggle

### Empty State
Not applicable

### Loading State
Page load spinner

### Error State
Form submission error alert

### Responsive Behavior
- Mobile: Single column, hamburger menu
- Tablet: 2-column layouts
- Desktop: Multi-column layouts
- Hero: Full viewport height on desktop

---

## Cross-Cutting Concerns

### Authentication States
- All authenticated pages require login
- Redirect to `/login` if not authenticated
- Show "Login" link on public pages if not authenticated
- Show user dropdown if authenticated

### Loading Strategy
- Initial page load: Show skeleton for main content
- API calls: Show loading in specific component
- Navigation: Instant with client-side cache

### Error Handling
- 404: Custom not found page with navigation back
- 403: Access denied page
- 500: Error page with retry button
- Network errors: Alert with retry

### Print Styles
- Hide sidebar, header actions
- Simplify tables
- Show only main content
- Black text on white background
