# StudentHub Portal - Project Documentation

## 1. Problem Scope & Identification
Modern academic settings often force students and faculty to juggle multiple disconnected platforms:
* Checking attendance on one system.
* Viewing mid-semester and end-semester grades on another.
* Accessing study resources on cloud drives.
* Reading campus announcements via bulk emails or notice boards.

**StudentHub** is a unified, browser-compatible student portal designed to consolidate these academic modules into a single, intuitive interface. It acts as a one-stop dashboard, reducing administrative friction, improving communication, and keeping academic statistics transparent and accessible.

---

## 2. User Roles
* **Student (Primary User)**: Accesses dashboards, tracks attendance, views registered courses, checks grades, submits assignments, downloads study materials, and reads notifications.
* **Faculty/Instructor (Secondary User)**: Manages class lists, updates attendance records, posts assignments and deadlines, uploads study resources, and posts grades.
* **Administrator (System Operator)**: Configures system databases, updates core schedules, manages student-faculty registrations, and posts official university announcements.

---

## 3. Key Modules
1. **Dashboard (Home)**: Aggregates notifications, calendar events, and direct entry points to other pages.
2. **Profile Management**: Displays personal information, academic registration credentials, and current enrollment status.
3. **Academics & Schedule**: Lists all currently registered courses, course codes, instructors, and class venues.
4. **Attendance System**: Calculates attendance percentages by subject and flags borderline cases (e.g., below 75%).
5. **Grades & Assessment**: Displays prior semester CGPA, mid-semester, and final grade reports.
6. **Assignments Tracker**: Lists pending and submitted tasks with deadlines.
7. **Resource Vault**: A directory of download links for slide decks, e-books, and worksheets.
8. **Notice Board**: Displays campus events, exams schedules, and emergency notices.
9. **Helpdesk / Contact Directory**: Provides contact information for departments, faculty offices, and academic staff.
10. **About Portal**: Outlines the system architecture, developmental phase, and developer profiles.

---

## 4. Project Folder Structure
All files reside in a flat directory for straightforward navigation and referencing, avoiding nested directory complexities for beginners:
```text
Html clg/
├── index.html         (Home / Dashboard)
├── profile.html       (Student Profile)
├── courses.html       (Registered Courses)
├── attendance.html    (Attendance Tracker)
├── grades.html        (Grades & Results)
├── assignments.html   (Assignments & Deadlines)
├── resources.html     (Study Resources)
├── events.html        (Announcements & Events)
├── contact.html       (Contact Directory)
├── about.html         (About Portal & Problem Scope)
├── CHARUSAT.png       (University Logo Image Asset)
└── README.md          (Project Documentation - This file)
```

---

## 5. Navigation Flow & Sitemap
Every page contains a global navigation bar enabling direct routing to any of the other 9 pages.

### Sitemap
```text
                          +-------------------------+
                          |   index.html (Home)     |
                          +------------+------------+
                                       |
     +-----------------+---------------+---------------+-----------------+
     |                 |               |               |                 |
+----+----+       +----+----+     +----+----+     +----+----+       +----+----+
| profile |       | courses |     |attendance|    | grades  |       |assignm- |
|  .html  |       |  .html  |     |  .html  |     |  .html  |       | ents    |
+----+----+       +----+----+     +----+----+     +----+----+       +----+----+
     |                 |               |               |                 |
     +-----------------+---------------+---------------+-----------------+
                                       |
     +-----------------+---------------+---------------+-----------------+
     |                 |               |               |                 |
+----+----+       +----+----+     +----+----+     +----+----+            |
|resources|       | events  |     | contact |     |  about  |            |
|  .html  |       |  .html  |     |  .html  |     |  .html  |            |
+---------+       +---------+     +---------+     +---------+            |
     |                                                                   |
     +-------------------------------------------------------------------+
     
*Note: Every file contains a persistent <nav> list containing links to all other 9 pages.*
```

---

## 6. Low-Fidelity Wireframe
Below is the visual structure used consistently across all 10 pages:

```text
+-----------------------------------------------------------------------+
|  [Logo - CHARUSAT.png]                           STUDENT HUB PORTAL   |
+-----------------------------------------------------------------------+
|  Navigation Bar (nav -> ul -> li -> a):                               |
|  [Home] | [Profile] | [Courses] | [Attendance] | [Grades]             |
|  [Assignments] | [Resources] | [Events] | [Contact] | [About]         |
+-----------------------------------------------------------------------+
|                                                                       |
|   <h1>PAGE HEADER (e.g. Student Profile)</h1>                         |
|   <u>Subheading / Current Context</u>                                 |
|                                                                       |
|   Main Content Section (e.g. Table, List, Paragraphs):                |
|   +---------------------------------------------------------------+   |
|   | Header 1       | Header 2       | Header 3                    |   |
|   +----------------+----------------+-----------------------------+   |
|   | Data 1         | Data 2         | Data 3                      |   |
|   +----------------+----------------+-----------------------------+   |
|                                                                       |
+-----------------------------------------------------------------------+
|  (C) 2026 StudentHub. Plain HTML Implementation.                      |
+-----------------------------------------------------------------------+
```

---

## 7. Permitted Attributes for Coloring & Layout (Non-CSS)
To stick strictly to the user's constraints (no CSS, no JavaScript, using only basic elements and 1-2 extra coloring/border attributes):
1. **`bgcolor`**: Used on `<body>` (e.g., `bgcolor="lightblue"` or `bgcolor="#f4f6f9"`) and on tables/headers to apply background colors.
2. **`text`**: Used on `<body>` (e.g., `text="navy"` or `text="#222222"`) to control general text color.
3. **`border`**: Used on `<table>` (e.g., `border="1"`) to format table grids.
