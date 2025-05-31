<?php
$pageTitle = "Manage Bookings";
require_once 'includes/header.php';

// Process booking status change if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['action'])) {
    $bookingId = $_POST['booking_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve' || $action === 'reject') {
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        
        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // Update booking status
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$status, $bookingId]);
            
            // If approved, update timetable
            if ($status === 'approved') {
                // Get booking details
                $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
                $stmt->execute([$bookingId]);
                $booking = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($booking) {
                    // Check if this course already exists in the timetable
                    $stmt = $pdo->prepare("
                        SELECT id, venue_name, day, time_slot 
                        FROM timetable 
                        WHERE course_code = ? OR course_name = ?
                    ");
                    $stmt->execute([$booking['course_code'], $booking['course_name']]);
                    $existingCourse = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($existingCourse) {
                        // Course exists - update the existing entry with new venue, day, time
                        $stmt = $pdo->prepare("
                            UPDATE timetable 
                            SET venue_name = ?, day = ?, time_slot = ?, lecturer = ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $booking['venue_name'],
                            $booking['day'],
                            $booking['time_slot'],
                            $booking['lecturer'],
                            $existingCourse['id']
                        ]);
                        
                        // Log the update for audit purposes
                        $logMessage = "Updated course {$booking['course_code']} ({$booking['course_name']}) from {$existingCourse['venue_name']} on {$existingCourse['day']} at {$existingCourse['time_slot']} to {$booking['venue_name']} on {$booking['day']} at {$booking['time_slot']}.";
                        
                        // You could add a log table and insert this message if needed
                        // $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, ?, ?)");
                        // $stmt->execute([$_SESSION['admin_id'], 'update_timetable_entry', $logMessage]);
                    } else {
                        // Check if there's an entry for this specific venue, day and time slot
                        $stmt = $pdo->prepare("
                            SELECT id 
                            FROM timetable 
                            WHERE venue_name = ? AND day = ? AND time_slot = ?
                        ");
                        $stmt->execute([$booking['venue_name'], $booking['day'], $booking['time_slot']]);
                        $existingVenueTimeEntry = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($existingVenueTimeEntry) {
                            // Update existing venue/time entry
                            $stmt = $pdo->prepare("
                                UPDATE timetable 
                                SET course_code = ?, course_name = ?, lecturer = ? 
                                WHERE id = ?
                            ");
                            $stmt->execute([
                                $booking['course_code'], 
                                $booking['course_name'], 
                                $booking['lecturer'], 
                                $existingVenueTimeEntry['id']
                            ]);
                        } else {
                            // Insert new entry
                            $stmt = $pdo->prepare("
                                INSERT INTO timetable 
                                (day, time_slot, course_code, course_name, venue_name, lecturer) 
                                VALUES (?, ?, ?, ?, ?, ?)
                            ");
                            $stmt->execute([
                                $booking['day'],
                                $booking['time_slot'],
                                $booking['course_code'],
                                $booking['course_name'],
                                $booking['venue_name'],
                                $booking['lecturer']
                            ]);
                        }
                    }
                }
            }
            
            // Commit transaction
            $pdo->commit();
            
            // Set success message
            if ($status === 'approved') {
                $successMessage = isset($existingCourse) 
                    ? "Booking approved successfully! The existing course entry has been updated with the new venue and time." 
                    : "Booking approved successfully!";
            } else {
                $successMessage = "Booking rejected successfully!";
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            $errorMessage = "Error: " . $e->getMessage();
        }
    }
}

// Get all pending bookings
$stmt = $pdo->query("
    SELECT b.*, s.reg_number 
    FROM bookings b
    JOIN users s ON b.user_id = s.id
    WHERE b.status = 'pending'
    ORDER BY b.created_at DESC
");
$pendingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (isset($successMessage)): ?>
    <div class="alert alert-success">
        <i class="alert-icon success-icon">✓</i>
        <div class="alert-content"><?php echo htmlspecialchars($successMessage); ?></div>
    </div>
<?php endif; ?>

<?php if (isset($errorMessage)): ?>
    <div class="alert alert-error">
        <i class="alert-icon error-icon">!</i>
        <div class="alert-content"><?php echo htmlspecialchars($errorMessage); ?></div>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <i class="card-icon">📝</i>
        <h2>Pending Bookings</h2>
    </div>
    <div class="card-content">
        <?php if (count($pendingBookings) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Reg Number</th>
                            <th>Venue</th>
                            <th>Day</th>
                            <th>Time Slot</th>
                            <th>Course</th>
                            <th>Lecturer</th>
                            <th>Date Requested</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingBookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['reg_number']); ?></td>
                                <td><?php echo htmlspecialchars($booking['venue_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['day']); ?></td>
                                <td><?php echo htmlspecialchars($booking['time_slot']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($booking['course_name']); ?> 
                                    <div class="text-muted"><?php echo htmlspecialchars($booking['course_code']); ?></div>
                                    
                                    <?php
                                    // Check if this course already exists in the timetable
                                    $stmt = $pdo->prepare("
                                        SELECT venue_name, day, time_slot 
                                        FROM timetable 
                                        WHERE course_code = ? OR course_name = ? 
                                        LIMIT 1
                                    ");
                                    $stmt->execute([$booking['course_code'], $booking['course_name']]);
                                    $existingCourse = $stmt->fetch(PDO::FETCH_ASSOC);
                                    
                                    if ($existingCourse):
                                    ?>
                                        <div class="warning-badge">
                                            <i class="warning-icon">⚠️</i>
                                            <span>Currently in <?php echo htmlspecialchars($existingCourse['venue_name']); ?> 
                                            on <?php echo htmlspecialchars($existingCourse['day']); ?> 
                                            at <?php echo htmlspecialchars($existingCourse['time_slot']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($booking['lecturer']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($booking['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" class="inline-form">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirmApprove('<?php echo addslashes($booking['course_code']); ?>', <?php echo $existingCourse ? 'true' : 'false'; ?>)">
                                                <i>✓</i> Approve
                                            </button>
                                        </form>
                                        
                                        <form method="POST" class="inline-form">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Are you sure you want to reject this booking?')">
                                                <i>✗</i> Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <script>
            function confirmApprove(courseCode, exists) {
                if (exists) {
                    return confirm('This course (' + courseCode + ') already exists in the timetable. Approving this booking will update the existing entry with the new venue and time. Continue?');
                }
                return confirm('Are you sure you want to approve this booking?');
            }
            </script>
            
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📝</div>
                <h3>No Pending Bookings</h3>
                <p>There are no pending bookings to review at this time.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Rest of the file remains the same -->

<style>
.warning-badge {
    display: inline-flex;
    align-items: center;
    background-color: rgba(255, 193, 7, 0.2);
    color: #856404;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    margin-top: 5px;
}

.warning-icon {
    margin-right: 5px;
}
</style>

<?php require_once 'includes/footer.php'; ?>