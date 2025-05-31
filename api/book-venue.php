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

// Get booking data

$venueName = isset($_POST['venue_name']) ? $_POST['venue_name'] : '';
$day = isset($_POST['day']) ? $_POST['day'] : '';
$timeSlot = isset($_POST['time_slot']) ? $_POST['time_slot'] : '';
$courseName = isset($_POST['course_name']) ? $_POST['course_name'] : '';
$courseCode = isset($_POST['course_code']) ? $_POST['course_code'] : '';
$lecturer = isset($_POST['lecturer']) ? $_POST['lecturer'] : '';

// Validate required fields
if (empty($venueName) || empty($day) || empty($timeSlot) || 
    empty($courseName) || empty($courseCode) || empty($lecturer)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Check if venue is already booked
$stmt = $pdo->prepare("SELECT 1 FROM timetable WHERE venue_name = ? AND day = ? AND time_slot = ?");
$stmt->execute([$venueName, $day, $timeSlot]);

if ($stmt->fetchColumn()) {
    echo json_encode(['success' => false, 'message' => 'This venue is already booked for the selected day and time']);
    exit;
}

// Insert booking
try {
    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, venue_name, day, time_slot, course_name, course_code, lecturer, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$studentId, $venueName, $day, $timeSlot, $courseName, $courseCode, $lecturer]);
    
    $bookingId = $pdo->lastInsertId();
    
    echo json_encode(['success' => true, 'booking_id' => $bookingId]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>