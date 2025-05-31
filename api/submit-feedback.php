<?php
require_once '../config/db.php';
require_once '../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get feedback data
$bookingId = isset($_POST['booking_id']) ? $_POST['booking_id'] : '';
$rating = isset($_POST['rating']) ? $_POST['rating'] : '';
$comment = isset($_POST['comment']) ? $_POST['comment'] : '';

// Validate required fields
if (empty($bookingId)) {
    echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
    exit;
}

// Insert feedback
try {
    $stmt = $pdo->prepare("INSERT INTO feedback (booking_id, rating, comment) VALUES (?, ?, ?)");
    $stmt->execute([$bookingId, $rating, $comment]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>