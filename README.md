# StudentHub - Web Development Frameworks (ITUE203-WDF)

**Student Name:** Kushagra Upadhyay  
**Student ID:** 25CE127  
**Department:** Computer Engineering  
**Institution:** Chandubhai S. Patel Institute of Technology (CSPIT), CHARUSAT  
**Academic Year:** 2026–2027  

---

## 📖 Course Practical Index (Practicals 01 to 14)

All laboratory assignments are organized practical-wise within the [`practical/`](./practical/) directory and documented in the official submission reports:
- [**25CE127_ITUE203-WDF_Practical_03_to_06.docx**](./25CE127_ITUE203-WDF_Practical_03_to_06.docx)
- [**25CE127_ITUE203-WDF_Practical_07_to_14.docx**](./25CE127_ITUE203-WDF_Practical_07_to_14.docx)

| Practical | Module Title & Objectives | Key Technologies |
| :--- | :--- | :--- |
| [**Practical 01**](./practical/Practical-01/) | Project Initiation, Requirement Analysis, Sitemap, Wireframe, and GitHub Setup | Markdown, HTML5, Wireframes |
| [**Practical 02**](./practical/Practical-02/) | Semantic HTML5 Pages with Accessibility-Ready Structure | Semantic HTML5, ARIA |
| [**Practical 03**](./practical/Practical-03/) | Responsive UI Design using CSS Grid, Flexbox, and Media Queries | CSS3, Flexbox, CSS Grid |
| [**Practical 04**](./practical/Practical-04/) | JavaScript DOM Manipulation, Event Handling, and UI Interactivity | ES6+ JavaScript, LocalStorage |
| [**Practical 05**](./practical/Practical-05/) | Registration Form with Frontend Validation & User-Friendly Error Handling | RegEx, Real-time Validation |
| [**Practical 06**](./practical/Practical-06/) | Rendering External JSON Data using Fetch API, Search, Filter & Pagination | Fetch API, JSON, Promises |
| [**Practical 07**](./practical/Practical-07/) | PHP Form Processing with Server-Side Validation and CSV/JSON Storage | PHP 8.x, File I/O, Sanitization |
| [**Practical 08**](./practical/Practical-08/) | MySQL Schema Design, ER Model, PDO Connectivity & Prepared Statements | MySQL, PDO, Relational 3NF |
| [**Practical 09**](./practical/Practical-09/) | Secure User Registration with Database Insert, Duplicate Check & Hashing | Bcrypt (`password_hash`), CSRF |
| [**Practical 10**](./practical/Practical-10/) | Secure Login/Logout with Sessions, Role-Based Access, Timeout & Cookies | Sessions, RBAC, Timeout |
| [**Practical 11**](./practical/Practical-11/) | Student Management CRUD with Search, Filter & CSV Export | MySQL CRUD, Dynamic SQL, CSV |
| [**Practical 12**](./practical/Practical-12/) | Event Management CRUD with Poster Upload and File Validation | File Uploads, MIME validation |
| [**Practical 13**](./practical/Practical-13/) | PHP JSON API and AJAX/Fetch-Based CRUD without Page Reload | RESTful JSON API, Fetch API |
| [**Practical 14**](./practical/Practical-14/) | Admin Dashboard with Role-Based Access Control, Dynamic Menu & Audit Log | RBAC Dashboard, Audit Logging |

---

## 🚀 Repository Directory Structure

```text
25CE127_WDF/
├── css/
│   └── style.css                     # Global responsive stylesheet
├── js/
│   └── app.js                        # Core interactivity & theme manager
├── practical/
│   ├── Practical-01/                 # Requirement analysis, sitemap, wireframes
│   ├── Practical-02/                 # Semantic HTML5 page skeletons
│   ├── Practical-03/                 # Responsive Grid & Flexbox layouts
│   ├── Practical-04/                 # DOM components, modals, sliders, FAQ
│   ├── Practical-05/                 # Client-side form validation with RegEx
│   ├── Practical-06/                 # Fetch API, search, filter, pagination
│   ├── Practical-07/                 # Server-side PHP form validation & file storage
│   ├── Practical-08/                 # MySQL schema, 3NF ER model, PDO connectivity
│   ├── Practical-09/                 # Secure registration, Bcrypt hashing, CSRF
│   ├── Practical-10/                 # Session authentication, RBAC, inactivity timeout
│   ├── Practical-11/                 # Student management CRUD with search & filter
│   ├── Practical-12/                 # Event management CRUD with poster upload
│   ├── Practical-13/                 # PHP REST API & asynchronous Fetch CRUD
│   └── Practical-14/                 # Admin dashboard with audit logging
├── 25CE127_ITUE203-WDF_Practical_03_to_06.docx
├── 25CE127_ITUE203-WDF_Practical_07_to_14.docx
├── index.html                        # Main StudentHub Portal Entry
├── about.html
├── contact.html
├── events.html
├── profile.html
├── attendance.html
├── assignments.html
├── courses.html
├── grades.html
└── resources.html
```

---

## 🛠️ How to Run Locally

### 1. Static & Frontend Practicals (Practicals 01 to 06)
Open any HTML file directly in a modern web browser (Google Chrome, Mozilla Firefox, Microsoft Edge), or run a local web server:
```bash
# Using VS Code Live Server extension or Python built-in server:
python -m http.server 8000
```
Navigate to `http://localhost:8000/`.

### 2. Backend & Database Practicals (Practicals 07 to 14)
1. Start **Apache** and **MySQL** services in the **XAMPP Control Panel**.
2. Clone or copy the repository into your web root directory (e.g. `C:/xampp/htdocs/25CE127_WDF/`).
3. Import the database schema:
   - Open phpMyAdmin (`http://localhost/phpmyadmin/`).
   - Create a new database named `studenthub`.
   - Import [`practical/Practical-08/studenthub.sql`](./practical/Practical-08/studenthub.sql).
4. Run diagnostic test script:
   - Visit `http://localhost/25CE127_WDF/practical/Practical-08/test_connection.php`.
5. Access the administrative portal or student dashboards:
   - Login: `practical/Practical-10/login.php`
   - Admin Dashboard: `practical/Practical-14/index.php`
