<?php
$pageTitle = "Manage Venues";
require_once 'includes/header.php';

// Process venue actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' && isset($_POST['venue_name'], $_POST['capacity'])) {
            $venueName = trim($_POST['venue_name']);
            $capacity = (int)$_POST['capacity'];
            
            // Validate inputs
            if (empty($venueName)) {
                $errorMessage = "Venue name cannot be empty.";
            } elseif ($capacity <= 0) {
                $errorMessage = "Capacity must be greater than zero.";
            } else {
                // Check if venue already exists
                $stmt = $pdo->prepare("SELECT id FROM venues WHERE venue_name = ?");
                $stmt->execute([$venueName]);
                
                if ($stmt->rowCount() > 0) {
                    $errorMessage = "A venue with this name already exists.";
                } else {
                    // Add venue
                    $stmt = $pdo->prepare("INSERT INTO venues (venue_name, capacity) VALUES (?, ?)");
                    
                    if ($stmt->execute([$venueName, $capacity])) {
                        $successMessage = "Venue added successfully!";
                    } else {
                        $errorMessage = "Failed to add venue.";
                    }
                }
            }
        } elseif ($_POST['action'] === 'delete' && isset($_POST['venue_id'])) {
            $venueId = (int)$_POST['venue_id'];
            
            // Check if venue is used in timetable
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM timetable WHERE venue_name = (SELECT venue_name FROM venues WHERE id = ?)");
            $stmt->execute([$venueId]);
            $usedInTimetable = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
            
            // Check if venue is used in bookings
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE venue_name = (SELECT venue_name FROM venues WHERE id = ?)");
            $stmt->execute([$venueId]);
            $usedInBookings = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
            
            if ($usedInTimetable || $usedInBookings) {
                $errorMessage = "Cannot delete venue because it is being used in timetable or bookings.";
            } else {
                // Delete venue
                $stmt = $pdo->prepare("DELETE FROM venues WHERE id = ?");
                
                if ($stmt->execute([$venueId])) {
                    $successMessage = "Venue deleted successfully!";
                } else {
                    $errorMessage = "Failed to delete venue.";
                }
            }
        }
    }
}

// Get all venues
$stmt = $pdo->query("SELECT * FROM venues ORDER BY venue_name");
$venues = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <i class="card-icon">🏫</i>
        <h2>Add New Venue</h2>
    </div>
    <div class="card-content">
        <form method="POST" class="add-venue-form">
            <input type="hidden" name="action" value="add">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="venue_name">Venue Name</label>
                    <div class="input-with-icon">
                        <i class="input-icon">🏫</i>
                        <input type="text" id="venue_name" name="venue_name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="capacity">Capacity</label>
                    <div class="input-with-icon">
                        <i class="input-icon">👥</i>
                        <input type="number" id="capacity" name="capacity" min="1" required>
                    </div>
                </div>
                
                <div class="form-group form-action">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">
                        <i>➕</i> Add Venue
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <i class="card-icon">📋</i>
        <h2>All Venues</h2>
    </div>
    <div class="card-content">
        <?php if (count($venues) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Venue Name</th>
                            <th>Capacity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($venues as $venue): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($venue['venue_name']); ?></td>
                                <td><?php echo htmlspecialchars($venue['capacity']); ?></td>
                                <td>
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="venue_id" value="<?php echo $venue['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Are you sure you want to delete this venue?')">
                                            <i>🗑️</i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">🏫</div>
                <h3>No Venues</h3>
                <p>There are no venues in the system. Add a venue using the form above.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>