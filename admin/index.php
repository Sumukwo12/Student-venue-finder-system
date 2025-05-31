<?php
$pageTitle = "Dashboard";
require_once 'includes/header.php';

// Get total venues count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM venues");
$totalVenues = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total booked venues from timetable
$stmt = $pdo->query("SELECT COUNT(*) as count FROM timetable");
$totalTimetableEntries = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Get pending bookings count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
$pendingBookings = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Get feedback count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM feedback");
$totalFeedback = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Get actual timetable data grouped by day and time slot
$timetableQuery = "
    SELECT 
        day, 
        time_slot, 
        COUNT(*) as booked_count
    FROM 
        timetable 
    GROUP BY 
        day, time_slot
    ORDER BY 
        FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),
        time_slot
";

$stmt = $pdo->query($timetableQuery);
$timetableData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize data by day and time slot
$calendarData = [];
$timeSlots = [];

foreach ($timetableData as $data) {
    $day = $data['day'];
    $timeSlot = $data['time_slot'];
    $calendarData[$day][$timeSlot] = [
        'count' => $data['booked_count'],
        'percentage' => ($totalVenues > 0) ? round(($data['booked_count'] / $totalVenues) * 100) : 0
    ];
    
    if (!in_array($timeSlot, $timeSlots)) {
        $timeSlots[] = $timeSlot;
    }
}

// Sort time slots
sort($timeSlots);

// Define days in order
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
?>

<div class="dashboard-stats">
    <div class="stat-card total">
        <div class="stat-icon">🏫</div>
        <div class="stat-value"><?php echo $totalVenues; ?></div>
        <div class="stat-label">Total Venues</div>
    </div>
    
    <div class="stat-card booked">
        <div class="stat-icon">📅</div>
        <div class="stat-value"><?php echo $totalTimetableEntries; ?></div>
        <div class="stat-label">Booked Venues</div>
    </div>
    
    <div class="stat-card pending">
        <div class="stat-icon">⏳</div>
        <div class="stat-value"><?php echo $pendingBookings; ?></div>
        <div class="stat-label">Pending Bookings</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">💬</div>
        <div class="stat-value"><?php echo $totalFeedback; ?></div>
        <div class="stat-label">Feedback Received</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="card-icon">📊</i>
        <h2>Venue Booking Statistics</h2>
    </div>
    <div class="card-content">
        <?php if (count($timetableData) > 0): ?>
            <p class="mb-3">Percentage of venues booked at each time slot per day</p>
            
            <div class="calendar-container">
                <table class="calendar-table">
                    <thead>
                        <tr>
                            <th>Time Slot / Day</th>
                            <?php foreach ($days as $day): ?>
                                <th><?php echo htmlspecialchars($day); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($timeSlots as $timeSlot): ?>
                            <tr>
                                <td class="time-slot"><?php echo htmlspecialchars($timeSlot); ?></td>
                                <?php foreach ($days as $day): ?>
                                    <?php
                                    $cellData = $calendarData[$day][$timeSlot] ?? null;
                                    $percentage = $cellData ? $cellData['percentage'] : 0;
                                    $count = $cellData ? $cellData['count'] : 0;
                                    
                                    // Determine color based on percentage
                                    $colorClass = '';
                                    if ($percentage >= 80) {
                                        $colorClass = 'high-booking';
                                    } elseif ($percentage >= 50) {
                                        $colorClass = 'medium-booking';
                                    } elseif ($percentage > 0) {
                                        $colorClass = 'low-booking';
                                    }
                                    ?>
                                    <td class="calendar-cell <?php echo $colorClass; ?>">
                                        <?php if ($cellData): ?>
                                            <div class="booking-percentage"><?php echo $percentage; ?>%</div>
                                            <div class="booking-count"><?php echo $count; ?> / <?php echo $totalVenues; ?> venues</div>
                                        <?php else: ?>
                                            <div class="booking-percentage">0%</div>
                                            <div class="booking-count">0 / <?php echo $totalVenues; ?> venues</div>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="calendar-legend mt-3">
                <div class="legend-item">
                    <div class="legend-color low-booking"></div>
                    <div class="legend-label">Less than 50% booked</div>
                </div>
                <div class="legend-item">
                    <div class="legend-color medium-booking"></div>
                    <div class="legend-label">50-80% booked</div>
                </div>
                <div class="legend-item">
                    <div class="legend-color high-booking"></div>
                    <div class="legend-label">Over 80% booked</div>
                </div>
            </div>
            
        <?php elseif ($totalVenues > 0): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📊</div>
                <h3>No Bookings Yet</h3>
                <p>There are no bookings in the timetable yet. Once bookings are added, statistics will appear here.</p>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📊</div>
                <h3>No Venues Available</h3>
                <p>Add venues to see booking statistics. Go to the <a href="venues.php">Manage Venues</a> page to add venues.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Calendar styles */
.calendar-container {
    overflow-x: auto;
}

.calendar-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.calendar-table th,
.calendar-table td {
    border: 1px solid #dee2e6;
    padding: 15px;
    text-align: center;
}

.calendar-table th {
    background-color: rgba(67, 97, 238, 0.05);
    font-weight: 600;
    color: var(--primary-dark);
}

.time-slot {
    font-weight: 600;
    background-color: rgba(67, 97, 238, 0.05);
    text-align: left;
}

.calendar-cell {
    height: 80px;
    vertical-align: middle;
    transition: all 0.3s ease;
}

.calendar-cell:hover {
    transform: scale(1.02);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    z-index: 1;
    position: relative;
}

.low-booking {
    background-color: rgba(40, 167, 69, 0.2);
}

.medium-booking {
    background-color: rgba(255, 193, 7, 0.2);
}

.high-booking {
    background-color: rgba(220, 53, 69, 0.2);
}

.booking-percentage {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.booking-count {
    font-size: 0.85rem;
    color: var(--gray-color);
}

.calendar-legend {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 4px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .calendar-table th,
    .calendar-table td {
        padding: 10px;
    }
    
    .booking-percentage {
        font-size: 1.2rem;
    }
    
    .booking-count {
        font-size: 0.75rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>