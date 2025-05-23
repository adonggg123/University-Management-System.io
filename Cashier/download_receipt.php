<?php
require_once __DIR__ . '/vendor/autoload.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}
require_once 'utils/db.php';

$receipt_no = $_GET['receipt_no'] ?? '';
if (!$receipt_no) exit('No receipt specified.');

$stmt = $pdo->prepare("
    SELECT r.*, p.amount, p.payment_date, o.order_type, s.student_no, s.first_name, s.last_name, s.middle_name, s.course, s.year_level, u.full_name AS cashier_name
    FROM receipts r
    JOIN payments p ON r.payment_id = p.id
    JOIN orders o ON p.order_id = o.id
    JOIN students s ON o.student_id = s.id
    JOIN users u ON p.cashier_id = u.id
    WHERE r.receipt_no = ?
    LIMIT 1
");
$stmt->execute([$receipt_no]);
$r = $stmt->fetch();
if (!$r) exit('Receipt not found.');

$html = '
<html>
<head>
    <style>
        body { font-family: "Segoe UI", Arial, sans-serif; color: #222; }
        .receipt-box { max-width: 400px; margin: 0 auto; border:1px solid #e0e0e0; padding:2em; border-radius:8px; }
        h2 { text-align:center; margin-bottom:1em; }
        .info { margin-bottom:1em; }
        .label { color:#888; font-size:0.95em; }
        .amount { font-size:1.2em; font-weight:bold; margin:1em 0; }
    </style>
</head>
<body>
<div class="receipt-box">
    <h2>UNIVERSITY MANAGEMENT SYSTEM</h2>
    <div class="info"><span class="label">Receipt No:</span> '.htmlspecialchars($r['receipt_no']).'</div>
    <div class="info"><span class="label">Date:</span> '.htmlspecialchars(date('Y-m-d', strtotime($r['issued_at']))).'</div>
    <div class="info"><span class="label">Student No:</span> '.htmlspecialchars($r['student_no']).'</div>
    <div class="info"><span class="label">Name:</span> '.htmlspecialchars($r['last_name'] . ', ' . $r['first_name'] . ' ' . $r['middle_name']).'</div>
    <div class="info"><span class="label">Course/Year:</span> '.htmlspecialchars($r['course']).' / '.htmlspecialchars($r['year_level']).'</div>
    <div class="info"><span class="label">Order Type:</span> '.htmlspecialchars($r['order_type']).'</div>
    <div class="amount">Amount Paid: ₱'.htmlspecialchars(number_format($r['amount'], 2)).'</div>
    <div class="info"><span class="label">Cashier:</span> '.htmlspecialchars($r['cashier_name']).'</div>
    <div class="info"><span class="label">Payment Date:</span> '.htmlspecialchars(date('Y-m-d H:i', strtotime($r['payment_date']))).'</div>
</div>
</body>
</html>
';

use Dompdf\Dompdf;

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A5', 'portrait');
$dompdf->render();
$dompdf->stream('Receipt_'.$r['receipt_no'].'.pdf', ['Attachment' => true]);
exit;
