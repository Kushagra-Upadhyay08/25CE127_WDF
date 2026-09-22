<?php
/**
 * StudentHub — Reusable Server-Side Validation Functions (Practical 7)
 * 
 * Provides sanitization and validation helpers for form processing.
 */
/**
 * Sanitize a string input
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
/**
 * Validate full name: 2-50 chars, alphabets and spaces only
 */
function validate_fullname($name) {
    $name = sanitize_input($name);
    if (empty($name)) {
        return ['valid' => false, 'message' => 'Full name is required.', 'value' => $name];
    }
    if (!preg_match('/^[A-Za-z\s]{2,50}$/', $name)) {
        return ['valid' => false, 'message' => 'Full name must be 2-50 characters (alphabets and spaces only).', 
'value' => $name];
    }
    return ['valid' => true, 'message' => '', 'value' => $name];
}
/**
 * Validate email address
 */
function validate_email($email) {
    $email = sanitize_input($email);
    if (empty($email)) {
        return ['valid' => false, 'message' => 'Email address is required.', 'value' => $email];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'message' => 'Enter a valid email address.', 'value' => $email];
    }
    return ['valid' => true, 'message' => '', 'value' => $email];
}
/**
 * Validate mobile number: 10 digits, starts with 6-9
 */
function validate_mobile($mobile) {
    $mobile = sanitize_input($mobile);
    if (empty($mobile)) {
        return ['valid' => false, 'message' => 'Mobile number is required.', 'value' => $mobile];
    }
    if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
        return ['valid' => false, 'message' => 'Enter a valid 10-digit mobile number starting with 6-9.', 'value' => 
$mobile];
    }
    return ['valid' => true, 'message' => '', 'value' => $mobile];
}
/**
 * Validate gender selection
 */
function validate_gender($gender) {
    $gender = sanitize_input($gender);
    $allowed = ['male', 'female', 'other'];
    if (empty($gender) || !in_array($gender, $allowed)) {
        return ['valid' => false, 'message' => 'Please select a valid gender.', 'value' => $gender];
    }
    return ['valid' => true, 'message' => '', 'value' => $gender];
}
/**
 * Validate course selection
 */
function validate_course($course) {
    $course = sanitize_input($course);
    $allowed = ['btech-cs', 'btech-it', 'btech-ec', 'btech-me'];
    if (empty($course) || !in_array($course, $allowed)) {
        return ['valid' => false, 'message' => 'Please select a valid course.', 'value' => $course];
    }
    return ['valid' => true, 'message' => '', 'value' => $course];
}
/**
 * Validate year of study
 */
function validate_year($year) {
    $year = sanitize_input($year);
    if (empty($year) || !in_array($year, ['1', '2', '3', '4'])) {
        return ['valid' => false, 'message' => 'Please select a valid year of study.', 'value' => $year];
    }
    return ['valid' => true, 'message' => '', 'value' => $year];
}
/**

 * Validate password: min 8 chars, uppercase, lowercase, digit, special char
 */
function validate_password($password) {
    if (empty($password)) {
        return ['valid' => false, 'message' => 'Password is required.'];
    }
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => 'Password must be at least 8 characters.'];
    }
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        return ['valid' => false, 'message' => 'Password must include uppercase, lowercase, digit, and special 
character.'];
    }
    return ['valid' => true, 'message' => ''];
}
/**
 * Validate confirm password matches password
 */
function validate_confirm_password($password, $confirm) {
    if (empty($confirm)) {
        return ['valid' => false, 'message' => 'Please confirm your password.'];
    }
    if ($password !== $confirm) {
        return ['valid' => false, 'message' => 'Passwords do not match.'];
    }
    return ['valid' => true, 'message' => ''];
}
/**
 * Validate contact form message
 */
function validate_message($message) {
    $message = sanitize_input($message);
    if (empty($message)) {
        return ['valid' => false, 'message' => 'Message is required.', 'value' => $message];
    }
    if (strlen($message) < 10) {
        return ['valid' => false, 'message' => 'Message must be at least 10 characters.', 'value' => $message];
    }
    return ['valid' => true, 'message' => '', 'value' => $message];
}
/**
 * Validate subject field
 */
function validate_subject($subject) {
    $subject = sanitize_input($subject);
    if (empty($subject)) {
        return ['valid' => false, 'message' => 'Subject is required.', 'value' => $subject];
    }
    if (strlen($subject) < 3) {
        return ['valid' => false, 'message' => 'Subject must be at least 3 characters.', 'value' => $subject];
    }
    return ['valid' => true, 'message' => '', 'value' => $subject];
}
?>
