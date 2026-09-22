<?php
/**
 * StudentHub — Student Validation Helper (Practical 11)
 * 
 * Validates student data for Insert and Update operations.
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
function sanitizeInput($input): string {
    if (!is_string($input)) {
        return '';
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
/**
 * Validates student input fields.
 * 
 * @param array $data Form data
 * @return array Associative array of field errors
 */
function validateStudent(array $data): array {
    $errors = [];
    // 1. Roll Number
    $rollNo = trim($data['roll_no'] ?? '');
    if ($rollNo === '') {
        $errors['roll_no'] = 'Roll Number is required.';
    } elseif (!preg_match('/^[A-Za-z0-9]{4,15}$/', $rollNo)) {
        $errors['roll_no'] = 'Roll Number must be 4–15 alphanumeric characters (e.g. 25CE127).';
    }
    // 2. Full Name
    $fullname = trim($data['fullname'] ?? '');
    if ($fullname === '') {
        $errors['fullname'] = 'Full Name is required.';
    } elseif (!preg_match('/^[A-Za-z\s]{2,50}$/', $fullname)) {
        $errors['fullname'] = 'Full Name must be 2–50 characters (alphabets and spaces only).';
    }
    // 3. Email
    $email = trim($data['email'] ?? '');
    if ($email === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }
    // 4. Mobile
    $mobile = trim($data['mobile'] ?? '');
    if ($mobile === '') {
        $errors['mobile'] = 'Mobile number is required.';
    } elseif (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
        $errors['mobile'] = 'Mobile number must be exactly 10 digits starting with 6–9.';
    }
    // 5. Gender

    $gender = trim($data['gender'] ?? '');
    if (!in_array($gender, ['male', 'female', 'other'], true)) {
        $errors['gender'] = 'Select a valid gender.';
    }
    // 6. Course
    $course = trim($data['course'] ?? '');
    $allowedCourses = ['btech-ce', 'btech-cs', 'btech-it', 'btech-ec'];
    if (!in_array($course, $allowedCourses, true)) {
        $errors['course'] = 'Select a valid course branch.';
    }
    // 7. Year
    $year = (int)($data['year'] ?? 0);
    if ($year < 1 || $year > 4) {
        $errors['year'] = 'Select an academic year between 1 and 4.';
    }
    // 8. Status (optional, defaults to active)
    $status = trim($data['status'] ?? 'active');
    if (!in_array($status, ['active', 'inactive'], true)) {
        $errors['status'] = 'Invalid status value.';
    }
    return $errors;
}
