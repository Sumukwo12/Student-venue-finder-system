<?php
require_once '../config/db.php';
require_once 'includes/auth.php';

// Check if admin is logged in
checkAdminAuth();

// Set error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure PHPSpreadsheet is installed
if (!file_exists('../vendor/autoload.php')) {
    die("PHPSpreadsheet is not installed. Please run 'composer require phpoffice/phpspreadsheet' in the project root.");
}

// Require PHPSpreadsheet
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

try {
    // Get timetable data - use the correct column name case: "Venue_name" with capital V
    $stmt = $pdo->query("
        SELECT 
            id, 
            day, 
            time_slot, 
            course_code, 
            course_name, 
            Venue_name, 
            lecturer 
        FROM 
            timetable 
        ORDER BY 
            day, time_slot, Venue_name
    ");
    
    if (!$stmt) {
        throw new Exception("Database query failed: " . print_r($pdo->errorInfo(), true));
    }
    
    $timetableData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Timetable');

    // Set headers
    $headers = ['Day', 'Time Slot', 'Course Code', 'Course Name', 'Venue Name', 'Lecturer'];
    $columns = ['A', 'B', 'C', 'D', 'E', 'F'];

    foreach ($columns as $index => $column) {
        $sheet->setCellValue($column . '1', $headers[$index]);
    }

    // Style headers
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4361EE'],
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];

    $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

    // Add data
    $row = 2;
    foreach ($timetableData as $data) {
        $sheet->setCellValue('A' . $row, $data['day'] ?? '');
        $sheet->setCellValue('B' . $row, $data['time_slot'] ?? '');
        $sheet->setCellValue('C' . $row, $data['course_code'] ?? '');
        $sheet->setCellValue('D' . $row, $data['course_name'] ?? '');
        // Use the correct case for Venue_name
        $sheet->setCellValue('E' . $row, $data['Venue_name'] ?? '');
        $sheet->setCellValue('F' . $row, $data['lecturer'] ?? '');
        
        
        
        $row++;
    }

    // Style data rows
    $dataStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'CCCCCC'],
            ],
        ],
    ];

    if ($row > 2) {
        $sheet->getStyle('A2:G' . ($row - 1))->applyFromArray($dataStyle);
        
        // Add zebra striping
        for ($i = 2; $i < $row; $i += 2) {
            $sheet->getStyle('A' . $i . ':G' . $i)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F8F9FA');
        }
    }

    // Auto size columns
    foreach ($columns as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Set filename
    $filename = 'timetable_export_' . date('Y-m-d') . '.xlsx';

    // Create the writer
    $writer = new Xlsx($spreadsheet);
    
    // Save to a temporary file first
    $tempFile = tempnam(sys_get_temp_dir(), 'timetable_');
    $writer->save($tempFile);
    
    // Check if the file was created successfully
    if (!file_exists($tempFile)) {
        throw new Exception("Failed to create temporary file");
    }
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_end_clean();
    }

    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Content-Length: ' . filesize($tempFile));
    
    // Output the file
    readfile($tempFile);
    
    // Delete the temporary file
    unlink($tempFile);
    
    // End the script
    exit;
    
} catch (Exception $e) {
    // Log the error
    error_log('Excel download error: ' . $e->getMessage());
    
    // Display user-friendly error
    header('Content-Type: text/html');
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Download Error</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
            .error-container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #dc3545; border-radius: 5px; }
            h1 { color: #dc3545; }
            .back-link { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; }
            .back-link:hover { text-decoration: underline; }
            .error-details { background: #f8f9fa; padding: 10px; border-radius: 4px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>Download Error</h1>
            <p>There was a problem generating the Excel file. Please try again or contact the system administrator.</p>
            <a href="index.php" class="back-link">← Return to Dashboard</a>';
            
    if (isset($_SESSION['admin_id'])) {
        echo '<div class="error-details">
                <p><strong>Error details:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
              </div>';
    }
            
    echo '</div>
    </body>
    </html>';
    exit;
}