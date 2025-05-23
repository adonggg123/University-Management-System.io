<?php
$filename = basename($_GET['file']);
$filepath = __DIR__ . "/uploads/" . $filename;

if (file_exists($filepath)) {
  header('Content-Description: File Transfer');
  header('Content-Type: application/octet-stream');
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Content-Length: ' . filesize($filepath));
  readfile($filepath);
  exit;
} else {
  echo "File not found.";
}
