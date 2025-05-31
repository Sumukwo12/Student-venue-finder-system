<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

// Get student information
$studentId = $_SESSION['student_id'];
$stmt = $pdo->prepare("SELECT * FROM student WHERE id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Get filter parameters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Prepare query based on filter
$query = "SELECT * FROM bookings WHERE student_id = ?";
$params = [$studentId];

if ($statusFilter !== 'all') {
    $query .= " AND status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY created_at DESC";

// Get bookings
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Page title
$pageTitle = "Booking History";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Venue Booking System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Booking History Page Styles */

.booking-history-table {
    width: 100%;
    border-collapse: collapse;
}

.booking-history-table th,
.booking-history-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid var(--gray-light);
}

.booking-history-table th {
    background-color: rgba(var(--primary-rgb), 0.05);
    font-weight: 600;
    color: var(--primary-dark);
}

.booking-history-table tr:hover {
    background-color: rgba(var(--primary-rgb), 0.02);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-pending {
    background-color: rgba(248, 150, 30, 0.15);
    color: #f8962e;
}

.status-approved {
    background-color: rgba(46, 204, 113, 0.15);
    color: #2ecc71;
}

.status-rejected {
    background-color: rgba(231, 76, 60, 0.15);
    color: #e74c3c;
}

.filter-controls {
    display: flex;
    align-items: center;
}

.filter-form {
    display: flex;
    align-items: center;
}

.filter-form .form-group {
    margin: 0;
    display: flex;
    align-items: center;
}

.filter-form label {
    margin-right: 10px;
    font-weight: 500;
}

.filter-form select {
    padding: 8px 12px;
    border-radius: var(--border-radius-sm);
    border: 1px solid var(--gray-light);
    background-color: white;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.info-item {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    border-radius: var(--border-radius);
    background-color: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.info-icon {
    font-size: 1.5rem;
    margin-right: 15px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.info-icon.pending {
    background-color: rgba(248, 150, 30, 0.15);
    color: #f8962e;
}

.info-icon.approved {
    background-color: rgba(46, 204, 113, 0.15);
    color: #2ecc71;
}

.info-icon.rejected {
    background-color: rgba(231, 76, 60, 0.15);
    color: #e74c3c;
}

.info-content h3 {
    margin: 0 0 5px;
    font-size: 1.1rem;
}

.info-content p {
    margin: 0;
    color: var(--gray-dark);
    font-size: 0.9rem;
}

.text-muted {
    color: var(--gray-color);
    font-size: 0.85rem;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-state-icon {
    font-size: 3rem;
    margin-bottom: 15px;
    color: var(--gray-color);
}

.empty-state h3 {
    margin-bottom: 10px;
    color: var(--primary-dark);
}

.empty-state p {
    color: var(--gray-color);
    max-width: 500px;
    margin: 0 auto;
}

.mt-3 {
    margin-top: 15px;
}

.mt-4 {
    margin-top: 20px;
}

/* Responsive styles */
@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .filter-controls {
        margin-top: 15px;
        width: 100%;
    }
    
    .filter-form {
        width: 100%;
    }
    
    .filter-form .form-group {
        width: 100%;
    }
    
    .filter-form select {
        width: 100%;
    }
    
    .table-container {
        overflow-x: auto;
    }
    
    .booking-history-table {
        min-width: 800px;
    }
}
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1><?php echo $pageTitle; ?></h1>
            <p>View all your venue booking requests and their status</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="card-icon">📚</i>
                    <h2>Your Bookings</h2>
                </div>
                
                <div class="filter-controls">
                    <form action="" method="GET" class="filter-form">
                        <div class="form-group">
                            <label for="status">Filter by Status:</label>
                            <select id="status" name="status" onchange="this.form.submit()">
                                <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Bookings</option>
                                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card-content">
                <?php if (count($bookings) > 0): ?>
                    <div class="table-container">
                        <table class="booking-history-table">
                            <thead>
                                <tr>
                                    <th>Venue</th>
                                    <th>Day</th>
                                    <th>Time Slot</th>
                                    <th>Course</th>
                                    <th>Lecturer</th>
                                    <th>Date Requested</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['venue_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['day']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['time_slot']); ?></td>
                                        <td>
                                            <div><?php echo htmlspecialchars($booking['course_name']); ?></div>
                                            <div class="text-muted"><?php echo htmlspecialchars($booking['course_code']); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars($booking['lecturer']); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($booking['created_at'])); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            $statusIcon = '';
                                            
                                            if ($booking['status'] === 'pending') {
                                                $statusClass = 'status-pending';
                                                $statusIcon = '⏳';
                                            } elseif ($booking['status'] === 'approved') {
                                                $statusClass = 'status-approved';
                                                $statusIcon = '✅';
                                            } elseif ($booking['status'] === 'rejected') {
                                                $statusClass = 'status-rejected';
                                                $statusIcon = '❌';
                                            }
                                            ?>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo $statusIcon; ?> <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📝</div>
                        <h3>No Bookings Found</h3>
                        <?php if ($statusFilter !== 'all'): ?>
                            <p>You don't have any <?php echo $statusFilter; ?> bookings. Try changing the filter or make a new booking.</p>
                        <?php else: ?>
                            <p>You haven't made any venue bookings yet. Go to the dashboard to book a venue.</p>
                        <?php endif; ?>
                        <a href="dashboard.php" class="btn btn-primary mt-3">Book a Venue</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <div class="card-title">
                    <i class="card-icon">ℹ️</i>
                    <h2>Booking Status Information</h2>
                </div>
            </div>
            <div class="card-content">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-icon pending">⏳</div>
                        <div class="info-content">
                            <h3>Pending</h3>
                            <p>Your booking request has been submitted and is awaiting approval from the administrator.</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon approved">✅</div>
                        <div class="info-content">
                            <h3>Approved</h3>
                            <p>Your booking request has been approved. The venue is reserved for you at the specified time.</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon rejected">❌</div>
                        <div class="info-content">
                            <h3>Rejected</h3>
                            <p>Your booking request has been rejected. This may be due to scheduling conflicts or other reasons.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>