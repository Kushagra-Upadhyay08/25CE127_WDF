/* ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
   StudentHub — Fetch API, Search, Filter, Sort, Pagination (Practical 6)
   ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
document.addEventListener('DOMContentLoaded', function () {
    /* ■■ EVENTS PAGE RENDERING ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    const eventsContainer = document.getElementById('events-render');
    if (eventsContainer) {
        initEventsPage(eventsContainer);
    }
    /* ■■ ADMIN / STUDENTS PAGE RENDERING ■■■■■■■■■■■■■■■■■■■■■■■■ */
    const studentsContainer = document.getElementById('students-render');
    if (studentsContainer) {
        initStudentsPage(studentsContainer);
    }
    /* ■■ FAQ PAGE RENDERING ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    const faqsContainer = document.getElementById('faqs-render');

    if (faqsContainer) {
        initFaqsPage(faqsContainer);
    }
    /* ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    /*  EVENTS                                                    */
    /* ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    function initEventsPage(container) {
        let allEvents = [];
        let currentPage = 1;
        const perPage = 5;
        showLoading(container);
        fetch('data/events.json')
            .then(function (res) {
                if (!res.ok) throw new Error('Failed to load events');
                return res.json();
            })
            .then(function (data) {
                allEvents = data;
                renderEventsControls(container);
                applyEventsFilters();
            })
            .catch(function (err) {
                showError(container, err.message);
            });
        function renderEventsControls(container) {
            // Insert controls before the render area
            const controls = container.previousElementSibling;
            if (controls && controls.classList.contains('filter-controls')) return;
            const controlsDiv = document.createElement('div');
            controlsDiv.classList.add('filter-controls');
            controlsDiv.innerHTML =
                '<input type="text" id="event-search" placeholder="Search events..." class="search-input">' +
                '<select id="event-category-filter" class="filter-select">' +
                    '<option value="">All Categories</option>' +
                    '<option value="Technical">Technical</option>' +
                    '<option value="Workshop">Workshop</option>' +
                    '<option value="Cultural">Cultural</option>' +
                    '<option value="Sports">Sports</option>' +
                    '<option value="Seminar">Seminar</option>' +
                '</select>' +
                '<select id="event-status-filter" class="filter-select">' +
                    '<option value="">All Status</option>' +
                    '<option value="Upcoming">Upcoming</option>' +
                    '<option value="Completed">Completed</option>' +
                '</select>' +
                '<select id="event-sort" class="filter-select">' +
                    '<option value="date-asc">Date ↑</option>' +
                    '<option value="date-desc">Date ↓</option>' +
                    '<option value="title-asc">Title A–Z</option>' +
                    '<option value="title-desc">Title Z–A</option>' +
                '</select>';
            container.parentNode.insertBefore(controlsDiv, container);
            // Attach listeners
            document.getElementById('event-search').addEventListener('input', function () {
                currentPage = 1;
                applyEventsFilters();
            });
            document.getElementById('event-category-filter').addEventListener('change', function () {
                currentPage = 1;
                applyEventsFilters();
            });
            document.getElementById('event-status-filter').addEventListener('change', function () {
                currentPage = 1;
                applyEventsFilters();
            });
            document.getElementById('event-sort').addEventListener('change', function () {
                currentPage = 1;
                applyEventsFilters();
            });
        }
        function applyEventsFilters() {
            const search = (document.getElementById('event-search').value || '').toLowerCase();
            const category = document.getElementById('event-category-filter').value;
            const status = document.getElementById('event-status-filter').value;
            const sort = document.getElementById('event-sort').value;
            let filtered = allEvents.filter(function (e) {
                const matchSearch = e.title.toLowerCase().includes(search) ||
                                    e.description.toLowerCase().includes(search) ||
                                    e.venue.toLowerCase().includes(search);
                const matchCategory = category === '' || e.category === category;
                const matchStatus = status === '' || e.status === status;
                return matchSearch && matchCategory && matchStatus;
            });
            // Sort
            filtered.sort(function (a, b) {
                switch (sort) {
                    case 'date-asc':   return a.date.localeCompare(b.date);
                    case 'date-desc':  return b.date.localeCompare(a.date);
                    case 'title-asc':  return a.title.localeCompare(b.title);
                    case 'title-desc': return b.title.localeCompare(a.title);
                    default: return 0;
                }

            });
            renderPaginated(container, filtered, currentPage, perPage, renderEventCard, function (page) {
                currentPage = page;
                applyEventsFilters();
            });
        }
    }
    function renderEventCard(event) {
        var statusClass = event.status === 'Upcoming' ? 'badge-success' : 'badge-warning';
        return '<article class="card">' +
            '<h3>' + escapeHtml(event.title) + '</h3>' +
            '<p><strong>Date:</strong> ' + escapeHtml(event.date) + '</p>' +
            '<p><strong>Venue:</strong> ' + escapeHtml(event.venue) + '</p>' +
            '<p><strong>Category:</strong> ' + escapeHtml(event.category) +
            ' <span class="badge ' + statusClass + '">' + escapeHtml(event.status) + '</span></p>' +
            '<p>' + escapeHtml(event.description) + '</p>' +
        '</article>';
    }
    /* ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    /*  STUDENTS                                                  */
    /* ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    function initStudentsPage(container) {
        let allStudents = [];
        let currentPage = 1;
        const perPage = 5;
        showLoading(container);
        fetch('data/students.json')
            .then(function (res) {
                if (!res.ok) throw new Error('Failed to load students');
                return res.json();
            })
            .then(function (data) {
                allStudents = data;
                renderStudentsControls(container);
                applyStudentsFilters();
            })
            .catch(function (err) {
                showError(container, err.message);
            });
        function renderStudentsControls(container) {
            const controls = container.previousElementSibling;
            if (controls && controls.classList.contains('filter-controls')) return;
            const controlsDiv = document.createElement('div');
            controlsDiv.classList.add('filter-controls');
            controlsDiv.innerHTML =
                '<input type="text" id="student-search" placeholder="Search students..." class="search-input">' +
                '<select id="student-course-filter" class="filter-select">' +
                    '<option value="">All Courses</option>' +
                    '<option value="B.Tech CS">B.Tech CS</option>' +
                    '<option value="B.Tech IT">B.Tech IT</option>' +
                    '<option value="B.Tech EC">B.Tech EC</option>' +
                    '<option value="B.Tech ME">B.Tech ME</option>' +
                '</select>' +
                '<select id="student-sort" class="filter-select">' +
                    '<option value="name-asc">Name A–Z</option>' +
                    '<option value="name-desc">Name Z–A</option>' +
                    '<option value="enrollment-asc">Enrollment ↑</option>' +
                '</select>';
            container.parentNode.insertBefore(controlsDiv, container);
            document.getElementById('student-search').addEventListener('input', function () {
                currentPage = 1;
                applyStudentsFilters();
            });
            document.getElementById('student-course-filter').addEventListener('change', function () {
                currentPage = 1;
                applyStudentsFilters();
            });
            document.getElementById('student-sort').addEventListener('change', function () {
                currentPage = 1;
                applyStudentsFilters();
            });
        }
        function applyStudentsFilters() {
            const search = (document.getElementById('student-search').value || '').toLowerCase();
            const course = document.getElementById('student-course-filter').value;
            const sort = document.getElementById('student-sort').value;
            let filtered = allStudents.filter(function (s) {
                const matchSearch = s.name.toLowerCase().includes(search) ||
                                    s.email.toLowerCase().includes(search) ||
                                    s.enrollment.toLowerCase().includes(search);
                const matchCourse = course === '' || s.course === course;
                return matchSearch && matchCourse;
            });
            filtered.sort(function (a, b) {
                switch (sort) {
                    case 'name-asc':       return a.name.localeCompare(b.name);
                    case 'name-desc':      return b.name.localeCompare(a.name);
                    case 'enrollment-asc': return a.enrollment.localeCompare(b.enrollment);
                    default: return 0;
                }

            });
            renderPaginated(container, filtered, currentPage, perPage, renderStudentRow, function (page) {
                currentPage = page;
                applyStudentsFilters();
            }, true); // table mode
        }
    }
    function renderStudentRow(student) {
        var statusClass = student.status === 'Active' ? 'badge-success' :
                          student.status === 'Pending' ? 'badge-warning' : 'badge-warning';
        return '<tr>' +
            '<td>' + escapeHtml(student.enrollment) + '</td>' +
            '<td>' + escapeHtml(student.name) + '</td>' +
            '<td>' + escapeHtml(student.email) + '</td>' +
            '<td>' + escapeHtml(student.course) + '</td>' +
            '<td>Year ' + student.year + '</td>' +
            '<td><span class="badge ' + statusClass + '">' + escapeHtml(student.status) + '</span></td>' +
        '</tr>';
    }
    /* ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    /*  FAQs                                                      */
    /* ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    function initFaqsPage(container) {
        let allFaqs = [];
        let currentPage = 1;
        const perPage = 5;
        showLoading(container);
        fetch('data/faqs.json')
            .then(function (res) {
                if (!res.ok) throw new Error('Failed to load FAQs');
                return res.json();
            })
            .then(function (data) {
                allFaqs = data;
                renderFaqControls(container);
                applyFaqFilters();
            })
            .catch(function (err) {
                showError(container, err.message);
            });
        function renderFaqControls(container) {
            const controls = container.previousElementSibling;
            if (controls && controls.classList.contains('filter-controls')) return;
            const controlsDiv = document.createElement('div');
            controlsDiv.classList.add('filter-controls');
            controlsDiv.innerHTML =
                '<input type="text" id="faq-search" placeholder="Search FAQs..." class="search-input">' +
                '<select id="faq-category-filter" class="filter-select">' +
                    '<option value="">All Categories</option>' +
                    '<option value="Account">Account</option>' +
                    '<option value="Events">Events</option>' +
                    '<option value="Academic">Academic</option>' +
                    '<option value="General">General</option>' +
                    '<option value="Admin">Admin</option>' +
                '</select>';
            container.parentNode.insertBefore(controlsDiv, container);
            document.getElementById('faq-search').addEventListener('input', function () {
                currentPage = 1;
                applyFaqFilters();
            });
            document.getElementById('faq-category-filter').addEventListener('change', function () {
                currentPage = 1;
                applyFaqFilters();
            });
        }
        function applyFaqFilters() {
            const search = (document.getElementById('faq-search').value || '').toLowerCase();
            const category = document.getElementById('faq-category-filter').value;
            let filtered = allFaqs.filter(function (f) {
                const matchSearch = f.question.toLowerCase().includes(search) ||
                                    f.answer.toLowerCase().includes(search);
                const matchCategory = category === '' || f.category === category;
                return matchSearch && matchCategory;
            });
            renderPaginated(container, filtered, currentPage, perPage, renderFaqItem, function (page) {
                currentPage = page;
                applyFaqFilters();
            });
        }
    }
    function renderFaqItem(faq) {
        return '<details class="faq-item">' +
            '<summary>' + escapeHtml(faq.question) +
            ' <span class="badge badge-success">' + escapeHtml(faq.category) + '</span></summary>' +
            '<div class="faq-answer"><p>' + escapeHtml(faq.answer) + '</p></div>' +
        '</details>';
    }
    /* ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */

    /*  SHARED UTILITIES                                          */
    /* ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    function renderPaginated(container, items, page, perPage, renderFn, onPageChange, isTable) {
        const totalPages = Math.ceil(items.length / perPage) || 1;
        const startIdx = (page - 1) * perPage;
        const pageItems = items.slice(startIdx, startIdx + perPage);
        if (items.length === 0) {
            container.innerHTML = '<p class="no-results">No results found. Try adjusting your search or filters.</p>';
            return;
        }
        var html = '';
        if (isTable) {
            html = '<div class="table-wrapper"><table>' +
                '<thead><tr><th>Enrollment</th><th>Name</th><th>Email</th><th>Course</th><th>Year</th><th>Status</
th></tr></thead>' +
                '<tbody>' + pageItems.map(renderFn).join('') + '</tbody></table></div>';
        } else {
            html = '<div class="card-grid">' + pageItems.map(renderFn).join('') + '</div>';
        }
        // Pagination controls
        if (totalPages > 1) {
            html += '<div class="pagination">';
            html += '<button class="pagination-btn" data-page="' + (page - 1) + '"' +
                    (page === 1 ? ' disabled' : '') + '>■ Prev</button>';
            for (var i = 1; i <= totalPages; i++) {
                html += '<button class="pagination-btn' + (i === page ? ' active' : '') +
                        '" data-page="' + i + '">' + i + '</button>';
            }
            html += '<button class="pagination-btn" data-page="' + (page + 1) + '"' +
                    (page === totalPages ? ' disabled' : '') + '>Next ■</button>';
            html += '</div>';
        }
        html += '<p class="results-count">Showing ' + (startIdx + 1) + '–' +
                Math.min(startIdx + perPage, items.length) + ' of ' + items.length + ' results</p>';
        container.innerHTML = html;
        // Attach pagination click events
        container.querySelectorAll('.pagination-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var targetPage = parseInt(this.getAttribute('data-page'));
                if (targetPage >= 1 && targetPage <= totalPages) {
                    onPageChange(targetPage);
                }
            });
        });
    }
    function showLoading(container) {
        container.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading data...</p></div>';
    }
    function showError(container, message) {
        container.innerHTML = '<div class="error-state"><p>■■ Error: ' + escapeHtml(message) + '</p><p>Please try 
refreshing the page.</p></div>';
    }
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
});
