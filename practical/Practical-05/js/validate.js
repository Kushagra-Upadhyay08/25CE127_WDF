/* ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
   StudentHub — Registration Form Validation (Practical 5)
   Frontend Validation with Regex, Password Strength, Error Handling
   ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registration-form');
    if (!form) return;
    /* ■■ Regex Patterns ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    const patterns = {
        fullname: /^[A-Za-z\s]{2,50}$/,
        email:    /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        mobile:   /^[6-9]\d{9}$/,
        password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/

    };
    /* ■■ Field References ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    const fields = {
        fullname:        document.getElementById('reg-fullname'),
        email:           document.getElementById('reg-email'),
        mobile:          document.getElementById('reg-mobile'),
        gender:          document.getElementById('reg-gender'),
        course:          document.getElementById('reg-course'),
        year:            document.getElementById('reg-year'),
        password:        document.getElementById('reg-password'),
        confirmPassword: document.getElementById('reg-confirm-password'),
        terms:           document.getElementById('reg-terms')
    };
    /* ■■ Create Error Message Elements ■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    function createErrorElement(field, id) {
        let errorEl = document.getElementById(id);
        if (!errorEl) {
            errorEl = document.createElement('span');
            errorEl.id = id;
            errorEl.classList.add('field-error');
            errorEl.setAttribute('role', 'alert');
            field.parentNode.appendChild(errorEl);
            field.setAttribute('aria-describedby', id);
        }
        return errorEl;
    }
    // Create error elements for each field
    const errors = {};
    Object.keys(fields).forEach(function (key) {
        if (fields[key]) {
            errors[key] = createErrorElement(fields[key], 'error-' + key);
        }
    });
    /* ■■ Password Strength Meter ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    let strengthMeter = document.getElementById('password-strength');
    if (!strengthMeter && fields.password) {
        strengthMeter = document.createElement('div');
        strengthMeter.id = 'password-strength';
        strengthMeter.classList.add('password-strength');
        strengthMeter.innerHTML = '<div class="strength-bar"><div class="strength-fill"></div></div><span 
class="strength-text"></span>';
        fields.password.parentNode.appendChild(strengthMeter);
    }
    function checkPasswordStrength(password) {
        let score = 0;
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[@$!%*?&]/.test(password)) score++;
        const fill = strengthMeter.querySelector('.strength-fill');
        const text = strengthMeter.querySelector('.strength-text');
        if (password.length === 0) {
            fill.style.width = '0%';
            fill.className = 'strength-fill';
            text.textContent = '';
            return;
        }
        if (score <= 2) {
            fill.style.width = '25%';
            fill.className = 'strength-fill weak';
            text.textContent = 'Weak';
            text.style.color = '#c62828';
        } else if (score <= 4) {
            fill.style.width = '60%';
            fill.className = 'strength-fill fair';
            text.textContent = 'Fair';
            text.style.color = '#f57c00';
        } else {
            fill.style.width = '100%';
            fill.className = 'strength-fill strong';
            text.textContent = 'Strong';
            text.style.color = '#2e7d32';
        }
    }
    /* ■■ Validation Functions ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    function showError(field, errorEl, message) {
        errorEl.textContent = message;
        errorEl.classList.add('visible');
        field.classList.add('input-error');
        field.classList.remove('input-success');
    }
    function showSuccess(field, errorEl) {
        errorEl.textContent = '';
        errorEl.classList.remove('visible');
        field.classList.remove('input-error');
        field.classList.add('input-success');
    }
    function validateFullname() {
        const val = fields.fullname.value.trim();

        if (val === '') {
            showError(fields.fullname, errors.fullname, 'Full name is required.');
            return false;
        }
        if (!patterns.fullname.test(val)) {
            showError(fields.fullname, errors.fullname, 'Only alphabets and spaces allowed (2–50 characters).');
            return false;
        }
        showSuccess(fields.fullname, errors.fullname);
        return true;
    }
    function validateEmail() {
        const val = fields.email.value.trim();
        if (val === '') {
            showError(fields.email, errors.email, 'Email address is required.');
            return false;
        }
        if (!patterns.email.test(val)) {
            showError(fields.email, errors.email, 'Enter a valid email address (e.g., user@example.com).');
            return false;
        }
        showSuccess(fields.email, errors.email);
        return true;
    }
    function validateMobile() {
        const val = fields.mobile.value.trim();
        if (val === '') {
            showError(fields.mobile, errors.mobile, 'Mobile number is required.');
            return false;
        }
        if (!patterns.mobile.test(val)) {
            showError(fields.mobile, errors.mobile, 'Enter a valid 10-digit mobile number starting with 6–9.');
            return false;
        }
        showSuccess(fields.mobile, errors.mobile);
        return true;
    }
    function validateSelect(field, errorEl, label) {
        if (field.value === '') {
            showError(field, errorEl, label + ' is required.');
            return false;
        }
        showSuccess(field, errorEl);
        return true;
    }
    function validatePassword() {
        const val = fields.password.value;
        if (val === '') {
            showError(fields.password, errors.password, 'Password is required.');
            return false;
        }
        if (val.length < 8) {
            showError(fields.password, errors.password, 'Password must be at least 8 characters.');
            return false;
        }
        if (!patterns.password.test(val)) {
            showError(fields.password, errors.password, 'Must include uppercase, lowercase, digit, and special 
character (@$!%*?&).');
            return false;
        }
        showSuccess(fields.password, errors.password);
        return true;
    }
    function validateConfirmPassword() {
        const val = fields.confirmPassword.value;
        if (val === '') {
            showError(fields.confirmPassword, errors.confirmPassword, 'Please confirm your password.');
            return false;
        }
        if (val !== fields.password.value) {
            showError(fields.confirmPassword, errors.confirmPassword, 'Passwords do not match.');
            return false;
        }
        showSuccess(fields.confirmPassword, errors.confirmPassword);
        return true;
    }
    function validateTerms() {
        if (!fields.terms.checked) {
            showError(fields.terms, errors.terms, 'You must agree to the Terms & Conditions.');
            return false;
        }
        showSuccess(fields.terms, errors.terms);
        return true;
    }
    /* ■■ Real-time Validation (keyup/change) ■■■■■■■■■■■■■■■■■■■■ */
    if (fields.fullname) {
        fields.fullname.addEventListener('keyup', validateFullname);
        fields.fullname.addEventListener('blur', validateFullname);
    }
    if (fields.email) {
        fields.email.addEventListener('keyup', validateEmail);
        fields.email.addEventListener('blur', validateEmail);
    }
    if (fields.mobile) {
        fields.mobile.addEventListener('keyup', validateMobile);

        fields.mobile.addEventListener('blur', validateMobile);
    }
    if (fields.gender) {
        fields.gender.addEventListener('change', function () {
            validateSelect(fields.gender, errors.gender, 'Gender');
        });
    }
    if (fields.course) {
        fields.course.addEventListener('change', function () {
            validateSelect(fields.course, errors.course, 'Course');
        });
    }
    if (fields.year) {
        fields.year.addEventListener('change', function () {
            validateSelect(fields.year, errors.year, 'Year of study');
        });
    }
    if (fields.password) {
        fields.password.addEventListener('keyup', function () {
            validatePassword();
            checkPasswordStrength(fields.password.value);
            // Re-validate confirm password if it has content
            if (fields.confirmPassword.value) {
                validateConfirmPassword();
            }
        });
        fields.password.addEventListener('blur', validatePassword);
    }
    if (fields.confirmPassword) {
        fields.confirmPassword.addEventListener('keyup', validateConfirmPassword);
        fields.confirmPassword.addEventListener('blur', validateConfirmPassword);
    }
    if (fields.terms) {
        fields.terms.addEventListener('change', validateTerms);
    }
    /* ■■ Form Submit Validation ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const results = [
            validateFullname(),
            validateEmail(),
            validateMobile(),
            validateSelect(fields.gender, errors.gender, 'Gender'),
            validateSelect(fields.course, errors.course, 'Course'),
            validateSelect(fields.year, errors.year, 'Year of study'),
            validatePassword(),
            validateConfirmPassword(),
            validateTerms()
        ];
        const isValid = results.every(function (r) { return r; });
        if (isValid) {
            // Show success message
            showSuccessModal();
        } else {
            // Scroll to first error
            const firstError = form.querySelector('.input-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
    /* ■■ Success Modal ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */
    function showSuccessModal() {
        const overlay = document.createElement('div');
        overlay.classList.add('modal-overlay');
        const modal = document.createElement('div');
        modal.classList.add('modal-container');
        modal.innerHTML = '<h2 style="color:#2e7d32;margin-top:0;">■ Registration Successful!</h2>' +
            '<p>Your account has been created successfully. You can now <a href="login.html">login</a> to access the 
StudentHub portal.</p>' +
            '<button class="btn" onclick="this.closest(\'.modal-overlay\').remove()">Close</button>';
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        requestAnimationFrame(function () {
            overlay.classList.add('active');
        });
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.remove();
        });
    }
});
