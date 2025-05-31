<?php
require_once '../config/db.php';
require_once 'includes/auth.php';

// Check if admin is logged in
checkAdminAuth();

// Require PHPSpreadsheet
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Get feedback data
$stmt = $pdo->query("
    SELECT f.*, b.venue_name, b.day, b.time_slot, s.reg_number, s.first_name, s.last_name
    FROM feedback f
    JOIN bookings b ON f.booking_id = b.id
    JOIN users s ON b.user_id = s.id
    ORDER BY f.created_at DESC
");
$feedbackData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create new spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers
$sheet->setCellValue('A1', 'Student Name');
$sheet->setCellValue('B1', 'Registration Number');
$sheet->setCellValue('C1', 'Venue');
$sheet->setCellValue('D1', 'Day');
$sheet->setCellValue('E1', 'Time Slot');
$sheet->setCellValue('F1', 'Rating');
$sheet->setCellValue('G1', 'Comment');
$sheet->setCellValue('H1', 'Date Submitted');

// Style headers
$sheet->getStyle('A1:H1')->getFont()->setBold(true);

// Add data
$row = 2;
foreach ($feedbackData as $data) {
    $sheet->setCellValue('A' . $row, $data['first_name'] . ' ' . $data['last_name']);
    $sheet->setCellValue('B' . $row, $data['reg_number']);
    $sheet->setCellValue('C' . $row, $data['venue_name']);
    $sheet->setCellValue('D' . $row, $data['day']);
    $sheet->setCellValue('E' . $row, $data['time_slot']);
    $sheet->setCellValue('F' . $row, ucfirst($data['rating']));
    $sheet->setCellValue('G' . $row, $data['comment']);
    $sheet->setCellValue('H' . $row, date('Y-m-d H:i:s', strtotime($data['created_at'])));
    $row++;
}

// Auto size columns
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set filename
$filename = 'feedback_export_' . date('Y-m-d') . '.xlsx';

// Set headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Save to output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;