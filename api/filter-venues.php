<?php
require_once '../config/db.php';
require_once '../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get filter parameters
$day = isset($_POST['day']) ? $_POST['day'] : '';
$timeSlot = isset($_POST['time']) ? $_POST['time'] : '';
$capacity = isset($_POST['capacity']) ? $_POST['capacity'] : '';

// Build query to find available venues
$query = "SELECT v.venue_name, v.capacity 
          FROM venues v 
          WHERE 1=1";

$params = [];

// Add capacity filter
if (!empty($capacity)) {
    list($min, $max) = explode('-', $capacity);
    
    if ($max === '+') {
        $query .= " AND v.capacity > ?";
        $params[] = intval($min);
    } else {
        $query .= " AND v.capacity BETWEEN ? AND ?";
        $params[] = intval($min);
        $params[] = intval($max);
    }
}

// Add day and time slot filter to exclude already booked venues
if (!empty($day) && !empty($timeSlot)) {
    $query .= " AND NOT EXISTS (
                    SELECT 1 FROM timetable t 
                    WHERE t.Venue_name = v.venue_name 
                    AND t.day = ? 
                    AND t.time_slot = ?
                )";
    $params[] = $day;
    $params[] = $timeSlot;
}

// Execute query
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $venues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'venues' => $venues]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>