# StudentHub - Campus Portal (Practical 01)
**Student Name:** Kushagra Upadhyay  
**Student ID:** 25CE127  
**Course:** ITUE203 - Web Development Frameworks (WDF)  
**Institution:** CSPIT, CHARUSAT  

---

## 1. Project Overview & Scope
StudentHub is an integrated, semester-long web portal tailored for higher education institutes. It delivers centralized access to academic resources, campus announcements, event registrations, course curriculums, and administrative moderation tools.

### User Roles
- **Guest / Visitor:** Can browse home overview, about portal, FAQ directory, and contact information.
- **Student (Authenticated):** Can access personal student dashboard, view grades, track attendance, browse resources, register for university hackathons and events, and submit campus feedback.
- **Administrator:** Can manage registered students (CRUD), schedule campus events with poster uploads, monitor telemetry metrics, and review immutable audit logs.

---

## 2. Sitemap & Navigation Structure
- **Public Module:**
  - `index.html` - Homepage with hero section, portal highlights, and recent announcements.
  - `about.html` - Overview of CHARUSAT & CSPIT mission, accreditation, and department details.
  - `contact.html` - Campus contact directory and inquiry form.
  - `faq.html` - Frequently asked questions with category filtering.
  - `feedback.html` - Student satisfaction feedback form.
- **Authentication:**
  - `login.html` - Secure login entry with role-based routing.
  - `register.html` - Comprehensive student registration form.
- **Student Dashboard:**
  - `dashboard.html` - Personalized student hub with attendance, quick shortcuts, and announcements.
  - `profile.html` - Student academic credentials and profile management.
  - `events.html` - Campus events and workshop calendar.
- **Admin Console:**
  - `admin.html` - Admin dashboard, student registry management, and activity monitoring.

---

## 3. Technology Stack
- **Frontend:** HTML5, CSS3 (CSS Grid & Flexbox), Vanilla JavaScript (ES6+).
- **Backend:** PHP 8.x, Apache / XAMPP.
- **Database:** MySQL 8.x with PDO Prepared Statements.
- **Version Control:** Git & GitHub.
