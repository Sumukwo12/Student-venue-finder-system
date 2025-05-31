<?php
require_once '../config/db.php';
require_once '../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get student ID
$studentId = $_SESSION['student_id'];

// Get bookings for the current student
try {
    $stmt = $pdo->prepare("SELECT b.id, b.venue_name, v.venue_name, b.day, b.time_slot, 
                           b.course_name, b.course_code, b.lecturer, b.status, b.created_at 
                           FROM bookings b 
                           JOIN venues v ON b.venue_name = v.venue_name 
                           WHERE b.user_id = ? 
                           ORDER BY b.created_at DESC");
    $stmt->execute([$studentId]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'bookings' => $bookings]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>