<?php
include 'connect.php';

if (!isset($_GET['id'])) {
    die("❌ No application ID provided.");
}

$id = intval($_GET['id']);

// Fetch documents from the scholarship_documents table
$stmt = $conn->prepare("SELECT sd.document_name, sd.document_path FROM scholarship_documents sd WHERE sd.application_id = ?");
if ($stmt === false) {
    die("❌ Failed to prepare SQL statement: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$documents = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (!$documents) {
    die("❌ No documents found for this application.");
}

// Prepare files to zip
$files = [];
foreach ($documents as $document) {
    $files[$document['document_name']] = 'uploads/' . $document['file_path'];
}

if (empty($files)) {
    die("⚠️ No files to download.");
}

// Set a folder where you want to save the ZIP file
$downloadFolder = 'downloads';  // Change this to any directory you prefer
if (!is_dir($downloadFolder)) {
    mkdir($downloadFolder, 0777, true);  // Create the directory if it doesn't exist
}

// Create ZIP file
$zipName = 'applicant_files_' . $id . '.zip';
$zipPath = $downloadFolder . '/' . $zipName;

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
    die("❌ Failed to create ZIP.");
}

foreach ($files as $name => $filePath) {
    if (file_exists($filePath)) {
        $zip->addFile($filePath, $name . '_' . basename($filePath));
    }
}
$zip->close();

// Send ZIP for download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . filesize($zipPath));
readfile($zipPath);

// Optionally delete the ZIP file after downloading to save space
unlink($zipPath);

exit();
?>
