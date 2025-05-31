<?php
$pageTitle = "Upload Timetable";
require_once 'includes/header.php';

// Check if PHPExcel is installed
if (!file_exists('../vendor/autoload.php')) {
    $installMessage = "PHPExcel is not installed. Please run 'composer require phpoffice/phpspreadsheet' in the project root.";
}

// Process timetable upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'upload' && isset($_FILES['timetable_file'])) {
        $file = $_FILES['timetable_file'];
        
        // Check for errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = "Upload failed with error code: " . $file['error'];
        } else {
            // Check file type
            $fileType = mime_content_type($file['tmp_name']);
            if ($fileType !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' && 
                $fileType !== 'application/vnd.ms-excel') {
                $errorMessage = "Invalid file type. Please upload an Excel file (.xlsx or .xls).";
            } else {
                // Process the Excel file
                require_once '../vendor/autoload.php';
                
                try {
                    // Start transaction
                    $pdo->beginTransaction();
                    
                    // Clear existing timetable
                    $pdo->exec("DELETE FROM timetable");
                    
                    // Read Excel file
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
                    $worksheet = $spreadsheet->getActiveSheet();
                    
                    $rowCount = 0;
                    $errorRows = [];
                    
                    // Start from row 2 (assuming row 1 is header)
                    foreach ($worksheet->getRowIterator(2) as $row) {
                        $rowData = [];
                        $cellIterator = $row->getCellIterator();
                        $cellIterator->setIterateOnlyExistingCells(false);
                        
                        $i = 0;
                        foreach ($cellIterator as $cell) {
                            $rowData[$i] = $cell->getValue();
                            $i++;
                        }
                        
                        // Check if row has data
                        if (!empty($rowData[0]) && !empty($rowData[1]) && !empty($rowData[2])) {
                            $day = $rowData[0];
                            $timeSlot = $rowData[1];
                            $courseCode = $rowData[2];
                            $courseName = $rowData[3] ?? '';
                            $venueName = $rowData[4] ?? '';
                            $lecturer = $rowData[5] ?? '';
                            
                            // Insert into database
                            $stmt = $pdo->prepare("
                                INSERT INTO timetable (day, time_slot, course_code, course_name, venue_name, lecturer)
                                VALUES (?, ?, ?, ?, ?, ?)
                            ");
                            
                            $result = $stmt->execute([$day, $timeSlot, $courseCode, $courseName, $venueName, $lecturer]);
                            
                            if ($result) {
                                $rowCount++;
                            } else {
                                $errorRows[] = $row->getRowIndex();
                            }
                        }
                    }
                    
                    // Commit transaction
                    $pdo->commit();
                    
                    $successMessage = "Timetable uploaded successfully! $rowCount records imported.";
                    
                    if (!empty($errorRows)) {
                        $successMessage .= " However, there were errors in rows: " . implode(', ', $errorRows);
                    }
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $pdo->rollBack();
                    $errorMessage = "Error: " . $e->getMessage();
                }
            }
        }
    } elseif ($_POST['action'] === 'clear') {
        // Clear timetable
        try {
            $pdo->exec("DELETE FROM timetable");
            $successMessage = "Timetable cleared successfully!";
        } catch (Exception $e) {
            $errorMessage = "Error: " . $e->getMessage();
        }
    }
}

// Get timetable count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM timetable");
$timetableCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<?php if (isset($installMessage)): ?>
    <div class="alert alert-warning">
        <i class="alert-icon warning-icon">!</i>
        <div class="alert-content"><?php echo htmlspecialchars($installMessage); ?></div>
    </div>
<?php endif; ?>

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
        <i class="card-icon">📤</i>
        <h2>Upload Timetable</h2>
    </div>
    <div class="card-content">
        <p class="mb-3">Upload an Excel file (.xlsx) containing the timetable data. The file should have the following columns:</p>
        
        <div class="table-container mb-4">
            <table>
                <thead>
                    <tr>
                        <th>Column</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Day</td>
                        <td>Day of the week</td>
                        <td>Monday, Tuesday, etc.</td>
                    </tr>
                    <tr>
                        <td>Time Slot</td>
                        <td>Time slot for the class</td>
                        <td>7:10-10:00, 10:00-13:00, etc.</td>
                    </tr>
                    <tr>
                        <td>Course Code</td>
                        <td>Code for the course</td>
                        <td>CS101, PHY201, etc.</td>
                    </tr>
                    <tr>
                        <td>Course Name</td>
                        <td>Name of the course</td>
                        <td>Introduction to Computer Science, Physics II, etc.</td>
                    </tr>
                    <tr>
                        <td>Venue Name</td>
                        <td>Name of the venue</td>
                        <td>LU Main Hall, Science Lab 1, etc.</td>
                    </tr>
                    <tr>
                        <td>Lecturer</td>
                        <td>Name of the lecturer</td>
                        <td>Dr. Smith, Prof. Johnson, etc.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="upload-container">
            <form method="POST" enctype="multipart/form-data" class="upload-form">
                <input type="hidden" name="action" value="upload">
                
                <div class="form-group">
                    <label for="timetable_file">Select Excel File</label>
                    <div class="file-input-container">
                        <input type="file" id="timetable_file" name="timetable_file" accept=".xlsx,.xls" required>
                        <div class="file-input-button">
                            <i>📎</i> Choose File
                        </div>
                        <div class="file-input-name">No file chosen</div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i>📤</i> Upload Timetable
                </button>
            </form>
        </div>
        
        <div class="timetable-status mt-4">
            <h3>Current Timetable Status</h3>
            <p>There are currently <strong><?php echo $timetableCount; ?></strong> entries in the timetable.</p>
            
            <div class="timetable-actions mt-3">
                <a href="download-timetable.php" class="btn btn-outline">
                    <i>📥</i> Download Current Timetable
                </a>
                
                <form method="POST" class="inline-form ml-2">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn btn-secondary" onclick="return confirm('Are you sure you want to clear the entire timetable? This action cannot be undone.')">
                        <i>🗑️</i> Clear Timetable
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// File input display
document.getElementById('timetable_file').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
    document.querySelector('.file-input-name').textContent = fileName;
});
</script>

<?php require_once 'includes/footer.php'; ?>