/* ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
   StudentHub — Main JavaScript (Practical 4)
   DOM Manipulation, Event Handling, UI Interactivity
   ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
document.addEventListener('DOMContentLoaded', function () {
    /* ■■ 1. HAMBURGER MENU TOGGLE ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    const hamburger = document.getElementById('hamburger-btn');
    const mainNav = document.querySelector('.main-nav');
    const navOverlay = document.getElementById('nav-overlay');
    if (hamburger && mainNav) {
        hamburger.addEventListener('click', function () {
            const isOpen = mainNav.classList.toggle('open');
            hamburger.classList.toggle('active');
            hamburger.setAttribute('aria-expanded', isOpen);
            if (navOverlay) {
                navOverlay.classList.toggle('active');
            }
        });
        // Close menu on overlay click
        if (navOverlay) {
            navOverlay.addEventListener('click', function () {
                mainNav.classList.remove('open');
                hamburger.classList.remove('active');
                hamburger.setAttribute('aria-expanded', 'false');
                navOverlay.classList.remove('active');
            });
        }
        // Close menu on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && mainNav.classList.contains('open')) {
                mainNav.classList.remove('open');
                hamburger.classList.remove('active');
                hamburger.setAttribute('aria-expanded', 'false');
                if (navOverlay) navOverlay.classList.remove('active');
            }
        });
    }
    /* ■■ 2. FAQ ACCORDION ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    const faqItems = document.querySelectorAll('.faq-item');
    if (faqItems.length > 0) {
        faqItems.forEach(function (item) {
            const summary = item.querySelector('summary');
            if (summary) {
                summary.addEventListener('click', function (e) {
                    // Close other open FAQ items (accordion behaviour)
                    faqItems.forEach(function (otherItem) {
                        if (otherItem !== item && otherItem.hasAttribute('open')) {
                            otherItem.removeAttribute('open');
                        }
                    });
                });
            }
        });
    }
    /* ■■ 3. MODAL POPUP ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */

    // Look for elements with data-modal attribute
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    modalTriggers.forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal-target');
            openModal(modalId);
        });
    });
    function openModal(contentId) {
        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.classList.add('modal-overlay');
        overlay.id = 'modal-overlay';
        const modal = document.createElement('div');
        modal.classList.add('modal-container');
        const closeBtn = document.createElement('button');
        closeBtn.classList.add('modal-close');
        closeBtn.textContent = '✕';
        closeBtn.setAttribute('aria-label', 'Close modal');
        const body = document.createElement('div');
        body.classList.add('modal-body');
        // Try to get content from a hidden element
        const source = document.getElementById(contentId);
        if (source) {
            body.innerHTML = source.innerHTML;
        } else {
            body.innerHTML = '<p>Event details will be loaded here.</p>';
        }
        modal.appendChild(closeBtn);
        modal.appendChild(body);
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        // Animate in
        requestAnimationFrame(function () {
            overlay.classList.add('active');
        });
        // Close on button click
        closeBtn.addEventListener('click', function () {
            closeModal(overlay);
        });
        // Close on overlay click
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                closeModal(overlay);
            }
        });
        // Close on Escape
        function handleEscape(e) {
            if (e.key === 'Escape') {
                closeModal(overlay);
                document.removeEventListener('keydown', handleEscape);
            }
        }
        document.addEventListener('keydown', handleEscape);
    }
    function closeModal(overlay) {
        overlay.classList.remove('active');
        setTimeout(function () {
            overlay.remove();
        }, 300);
    }
    /* ■■ 4. NOTIFICATION BANNER DISMISS ■■■■■■■■■■■■■■■■■■■■■■■■■ */
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        // Add dismiss button if not already present
        if (!alert.querySelector('.alert-dismiss')) {
            const dismissBtn = document.createElement('button');
            dismissBtn.classList.add('alert-dismiss');
            dismissBtn.textContent = '✕';
            dismissBtn.setAttribute('aria-label', 'Dismiss notification');
            dismissBtn.addEventListener('click', function () {
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(20px)';
                setTimeout(function () {
                    alert.remove();
                }, 300);
            });
            alert.style.position = 'relative';
            alert.appendChild(dismissBtn);
        }
    });
    /* ■■ 5. CONTENT / ANNOUNCEMENT SLIDER ■■■■■■■■■■■■■■■■■■■■■■■ */
    const slider = document.getElementById('content-slider');
    if (slider) {
        const slides = slider.querySelectorAll('.slide');
        const dotsContainer = slider.querySelector('.slider-dots');

        let currentSlide = 0;
        let slideInterval;
        if (slides.length > 1) {
            // Create dots
            if (dotsContainer) {
                slides.forEach(function (_, index) {
                    const dot = document.createElement('button');
                    dot.classList.add('slider-dot');
                    if (index === 0) dot.classList.add('active');
                    dot.setAttribute('aria-label', 'Go to slide ' + (index + 1));
                    dot.addEventListener('click', function () {
                        goToSlide(index);
                        resetInterval();
                    });
                    dotsContainer.appendChild(dot);
                });
            }
            function goToSlide(index) {
                slides[currentSlide].classList.remove('active');
                const dots = dotsContainer ? dotsContainer.querySelectorAll('.slider-dot') : [];
                if (dots[currentSlide]) dots[currentSlide].classList.remove('active');
                currentSlide = index;
                slides[currentSlide].classList.add('active');
                if (dots[currentSlide]) dots[currentSlide].classList.add('active');
            }
            function nextSlide() {
                goToSlide((currentSlide + 1) % slides.length);
            }
            function resetInterval() {
                clearInterval(slideInterval);
                slideInterval = setInterval(nextSlide, 4000);
            }
            // Initialize first slide
            slides.forEach(function (slide, i) {
                if (i === 0) slide.classList.add('active');
                else slide.classList.remove('active');
            });
            // Auto-rotate
            slideInterval = setInterval(nextSlide, 4000);
            // Prev / Next buttons
            const prevBtn = slider.querySelector('.slider-prev');
            const nextBtn = slider.querySelector('.slider-next');
            if (prevBtn) {
                prevBtn.addEventListener('click', function () {
                    goToSlide((currentSlide - 1 + slides.length) % slides.length);
                    resetInterval();
                });
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    nextSlide();
                    resetInterval();
                });
            }
        }
    }
    /* ■■ 6. LIGHT/DARK THEME SWITCHER ■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    const themeToggle = document.getElementById('theme-toggle');
    const savedTheme = localStorage.getItem('studenthub-theme');
    // Apply saved theme on load
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
        if (themeToggle) themeToggle.textContent = '■■';
    }
    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const isDark = document.body.classList.toggle('dark-theme');
            localStorage.setItem('studenthub-theme', isDark ? 'dark' : 'light');
            themeToggle.textContent = isDark ? '■■' : '■';
        });
    }
    /* ■■ 7. SMOOTH SCROLL FOR ANCHOR LINKS ■■■■■■■■■■■■■■■■■■■■■■ */
    document.querySelectorAll('a[href^="#"]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            const target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
    /* ■■ 8. BACK TO TOP BUTTON ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    const backToTopBtn = document.createElement('button');
    backToTopBtn.id = 'back-to-top';
    backToTopBtn.textContent = '↑';

    backToTopBtn.setAttribute('aria-label', 'Back to top');
    backToTopBtn.classList.add('back-to-top');
    document.body.appendChild(backToTopBtn);
    window.addEventListener('scroll', function () {
        if (window.scrollY > 300) {
            backToTopBtn.classList.add('visible');
        } else {
            backToTopBtn.classList.remove('visible');
        }
    });
    backToTopBtn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});
