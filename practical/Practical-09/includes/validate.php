<?php
/**
 * StudentHub — Server-Side Validation Helper (Practical 9)
 * 
 * Consistent validation rules mirroring Practical 5 client-side checks.
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
/**
 * Sanitizes string input against XSS attacks.
 * 
 * @param mixed $input
 * @return string
 */
function sanitizeInput($input): string {
    if (!is_string($input)) {
        return '';
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
/**
 * Validates registration input array and returns array of errors.
 * 
 * @param array $data Form data
 * @return array Associative array of field-level error messages
 */
function validateRegistration(array $data): array {
    $errors = [];
    // 1. Roll Number
    $rollNo = trim($data['roll_no'] ?? '');
    if ($rollNo === '') {
        $errors['roll_no'] = 'Roll Number is required.';
    } elseif (!preg_match('/^[0-9]{2}[A-Za-z]{2,3}[0-9]{3}$/i', $rollNo) && !preg_match('/^[A-Za-z0-9]{4,15}$/', 
$rollNo)) {
        $errors['roll_no'] = 'Enter a valid Roll Number (e.g. 25CE127).';
    }
    // 2. Full Name
    $fullname = trim($data['fullname'] ?? '');
    if ($fullname === '') {
        $errors['fullname'] = 'Full Name is required.';
    } elseif (!preg_match('/^[A-Za-z\s]{2,50}$/', $fullname)) {
        $errors['fullname'] = 'Full Name must be 2–50 characters (alphabets and spaces only).';
    }
    // 3. Username
    $username = trim($data['username'] ?? '');
    if ($username === '') {
        $errors['username'] = 'Username is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $errors['username'] = 'Username must be 3–30 characters (letters, numbers, underscores only).';
    }
    // 4. Email
    $email = trim($data['email'] ?? '');
    if ($email === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    // 5. Mobile
    $mobile = trim($data['mobile'] ?? '');
    if ($mobile === '') {
        $errors['mobile'] = 'Mobile number is required.';
    } elseif (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
        $errors['mobile'] = 'Mobile number must be exactly 10 digits starting with 6–9.';
    }
    // 6. Gender
    $gender = trim($data['gender'] ?? '');
    $allowedGenders = ['male', 'female', 'other'];
    if ($gender === '' || !in_array($gender, $allowedGenders, true)) {
        $errors['gender'] = 'Please select a valid gender option.';
    }
    // 7. Course
    $course = trim($data['course'] ?? '');
    $allowedCourses = ['btech-cs', 'btech-it', 'btech-ce', 'btech-ec'];
    if ($course === '' || !in_array($course, $allowedCourses, true)) {
        $errors['course'] = 'Please select a valid course from the list.';
    }
    // 8. Year
    $year = (int)($data['year'] ?? 0);
    if ($year < 1 || $year > 4) {
        $errors['year'] = 'Please select an academic year between 1 and 4.';
    }
    // 9. Password

    $password = $data['password'] ?? '';
    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password) ||
              !preg_match('/[a-z]/', $password) ||
              !preg_match('/\d/', $password) ||
              !preg_match('/[@$!%*?&]/', $password)) {
        $errors['password'] = 'Password must contain uppercase, lowercase, number, and special character (@$!%*?&).';
    }
    // 10. Confirm Password
    $confirmPassword = $data['confirm_password'] ?? '';
    if ($confirmPassword === '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }
    // 11. Terms & Conditions
    if (empty($data['terms'])) {
        $errors['terms'] = 'You must agree to the Terms & Conditions.';
    }
    return $errors;
}
