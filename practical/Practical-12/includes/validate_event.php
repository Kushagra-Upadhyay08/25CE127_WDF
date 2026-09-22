<?php
/**
 * StudentHub — Event Validation Helper (Practical 12)
 * 
 * Server-side validation for Event creation and updates.
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
 * Validates event fields.
 * 
 * @param array $data Event inputs
 * @return array Field error messages
 */
function validateEvent(array $data): array {
    $errors = [];
    // 1. Title
    $title = trim($data['title'] ?? '');
    if ($title === '') {
        $errors['title'] = 'Event title is required.';
    } elseif (strlen($title) < 3 || strlen($title) > 150) {
        $errors['title'] = 'Title must be between 3 and 150 characters.';
    }
    // 2. Category
    $category = trim($data['category'] ?? '');
    $allowedCategories = ['Technical', 'Workshop', 'Sports', 'Cultural'];
    if (!in_array($category, $allowedCategories, true)) {
        $errors['category'] = 'Please select a valid event category.';
    }
    // 3. Description
    $desc = trim($data['description'] ?? '');
    if ($desc === '') {
        $errors['description'] = 'Event description is required.';
    } elseif (strlen($desc) < 10) {
        $errors['description'] = 'Description must be at least 10 characters.';
    }
    // 4. Event Date
    $date = trim($data['event_date'] ?? '');
    if ($date === '') {
        $errors['event_date'] = 'Event date is required.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $errors['event_date'] = 'Event date must be in YYYY-MM-DD format.';
    }
    // 5. Venue
    $venue = trim($data['venue'] ?? '');
    if ($venue === '') {
        $errors['venue'] = 'Venue location is required.';
    } elseif (strlen($venue) < 2 || strlen($venue) > 100) {
        $errors['venue'] = 'Venue must be between 2 and 100 characters.';
    }
    // 6. Max Participants
    $max = (int)($data['max_participants'] ?? 0);
    if ($max < 1 || $max > 5000) {

        $errors['max_participants'] = 'Maximum participants must be a positive integer (1 to 5000).';
    }
    // 7. Status
    $status = trim($data['status'] ?? 'upcoming');
    $allowedStatuses = ['upcoming', 'completed', 'cancelled'];
    if (!in_array($status, $allowedStatuses, true)) {
        $errors['status'] = 'Select a valid status (upcoming, completed, or cancelled).';
    }
    return $errors;
}
