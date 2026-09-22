<?php
/**
 * StudentHub Events JSON API - Practical 13
 * Supports GET, POST, PUT and DELETE without page reload.
 */
declare(strict_types=1);
require_once __DIR__ . '/../../Practical-12/config/db.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function respond(int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
function requestData(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') return $_POST;
    $data = json_decode($raw, true);
    if (!is_array($data)) respond(400, ['success' => false, 'message' => 'Request body must be valid JSON.']);
    return $data;
}
function validateEvent(array $data): array {
    $event = [
        'title' => trim((string)($data['title'] ?? '')),
        'category' => trim((string)($data['category'] ?? '')),
        'description' => trim((string)($data['description'] ?? '')),
        'event_date' => trim((string)($data['event_date'] ?? '')),
        'venue' => trim((string)($data['venue'] ?? '')),
        'max_participants' => (int)($data['max_participants'] ?? 0),
        'status' => trim((string)($data['status'] ?? 'upcoming')),
    ];
    $errors = [];
    foreach (['title' => 'Title', 'category' => 'Category', 'description' => 'Description', 'event_date' => 'Event 
date', 'venue' => 'Venue'] as $key => $label) {
        if ($event[$key] === '') $errors[$key] = "$label is required.";
    }
    if ($event['event_date'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $event['event_date'])) $errors[
'event_date'] = 'Use YYYY-MM-DD for the event date.';
    if ($event['max_participants'] < 1 || $event['max_participants'] > 5000) $errors['max_participants'] = 'Capacity 
must be between 1 and 5000.';
    if (!in_array($event['status'], ['upcoming', 'completed', 'cancelled'], true)) $errors['status'] = 'Invalid event 
status.';
    return [$event, $errors];
}
try {
    $pdo = getDBConnection();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($method === 'GET') {
        $search = trim((string)($_GET['search'] ?? ''));
        $sql = 'SELECT id, title, category, description, event_date, venue, max_participants, status, created_at FROM 
events';
        $params = [];
        if ($id) { $sql .= ' WHERE id = ?'; $params[] = $id; }
        elseif ($search !== '') { $sql .= ' WHERE title LIKE ? OR category LIKE ? OR venue LIKE ?'; $params = [
"%$search%", "%$search%", "%$search%"]; }
        $sql .= ' ORDER BY event_date ASC';
        $stmt = $pdo->prepare($sql); $stmt->execute($params);
        $events = $id ? $stmt->fetch() : $stmt->fetchAll();
        if ($id && !$events) respond(404, ['success' => false, 'message' => 'Event not found.']);
        respond(200, ['success' => true, 'data' => $events]);
    }
    $data = requestData();
    if ($method === 'POST') {
        [$event, $errors] = validateEvent($data);
        if ($errors) respond(422, ['success' => false, 'message' => 'Please correct the highlighted fields.', 
'errors' => $errors]);
        $stmt = $pdo->prepare('INSERT INTO events (title, category, description, event_date, venue, max_participants, 
status) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute(array_values($event));
        respond(201, ['success' => true, 'message' => 'Event created successfully.', 'data' => ['id' => (int)$pdo-
>lastInsertId()] + $event]);
    }
    if ($method === 'PUT') {
        if (!$id) respond(400, ['success' => false, 'message' => 'A valid event id is required.']);
        [$event, $errors] = validateEvent($data);
        if ($errors) respond(422, ['success' => false, 'message' => 'Please correct the highlighted fields.', 
'errors' => $errors]);
        $stmt = $pdo->prepare('UPDATE events SET title=?, category=?, description=?, event_date=?, venue=?, 
max_participants=?, status=? WHERE id=?');
        $stmt->execute([...array_values($event), $id]);
        if ($stmt->rowCount() === 0) respond(404, ['success' => false, 'message' => 'Event not found or unchanged.']);
        respond(200, ['success' => true, 'message' => 'Event updated successfully.', 'data' => ['id' => $id] + 
$event]);
    }
    if ($method === 'DELETE') {
        if (!$id) respond(400, ['success' => false, 'message' => 'A valid event id is required.']);
        $stmt = $pdo->prepare('DELETE FROM events WHERE id = ?'); $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) respond(404, ['success' => false, 'message' => 'Event not found.']);

        respond(200, ['success' => true, 'message' => 'Event deleted successfully.']);
    }
    respond(405, ['success' => false, 'message' => 'Method not allowed.']);
} catch (Throwable $e) {
    error_log('[Practical-13 API] ' . $e->getMessage());
    respond(500, ['success' => false, 'message' => 'The API could not complete the request. Check database 
configuration.']);
}
