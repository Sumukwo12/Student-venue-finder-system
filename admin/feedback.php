<?php
$pageTitle = "Student Feedback";
require_once 'includes/header.php';

// Get all feedback
$stmt = $pdo->query("
    SELECT f.*, b.venue_name, b.day, b.time_slot, s.reg_number, s.first_name, s.last_name
    FROM feedback f
    JOIN bookings b ON f.booking_id = b.id
    JOIN users s ON b.user_id = s.id
    ORDER BY f.created_at DESC
");
$feedbackList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header">
        <i class="card-icon">💬</i>
        <h2>Student Feedback</h2>
    </div>
    <div class="card-content">
        <?php if (count($feedbackList) > 0): ?>
            <div class="feedback-actions mb-3">
                <a href="download-feedback.php" class="btn btn-outline">
                    <i>📥</i> Download Feedback Data
                </a>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Venue</th>
                            <th>Day & Time</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Date Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbackList as $feedback): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($feedback['first_name'] . ' ' . $feedback['last_name']); ?>
                                    <div class="text-muted"><?php echo htmlspecialchars($feedback['reg_number']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($feedback['venue_name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($feedback['day']); ?>
                                    <div class="text-muted"><?php echo htmlspecialchars($feedback['time_slot']); ?></div>
                                </td>
                                <td>
                                    <div class="rating-display">
                                        <?php
                                        $rating = $feedback['rating'];
                                        $ratingClass = '';
                                        $ratingIcon = '';
                                        
                                        if ($rating === 'excellent') {
                                            $ratingClass = 'excellent';
                                            $ratingIcon = '⭐⭐⭐⭐⭐';
                                        } elseif ($rating === 'good') {
                                            $ratingClass = 'good';
                                            $ratingIcon = '⭐⭐⭐⭐';
                                        } elseif ($rating === 'average') {
                                            $ratingClass = 'average';
                                            $ratingIcon = '⭐⭐⭐';
                                        } elseif ($rating === 'poor') {
                                            $ratingClass = 'poor';
                                            $ratingIcon = '⭐⭐';
                                        }
                                        ?>
                                        <span class="rating <?php echo $ratingClass; ?>">
                                            <?php echo ucfirst(htmlspecialchars($rating)); ?>
                                        </span>
                                        <div class="rating-stars"><?php echo $ratingIcon; ?></div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($feedback['comment'])): ?>
                                        <div class="comment-text">
                                            <?php echo nl2br(htmlspecialchars($feedback['comment'])); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No comment provided</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($feedback['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">💬</div>
                <h3>No Feedback Yet</h3>
                <p>There is no feedback from students yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>