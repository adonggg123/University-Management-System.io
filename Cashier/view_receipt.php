<?php
session_start();
$host = "localhost";
$dbname = "university_management_system";
$user = "root";
$pass = "quest4inno@server";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
$receipt_no = $_GET['receipt_no'] ?? '';
if (!$receipt_no) exit('No receipt specified.');

$stmt = $pdo->prepare("
    SELECT r.*, p.amount, p.payment_date, o.order_type, s.student_no, s.first_name, s.last_name, s.middle_name, s.course, s.year_level, u.full_name AS cashier_name
    FROM receipts1 r
    JOIN payments1 p ON r.payment_id = p.id
    JOIN orders1 o ON p.order_id = o.id
    JOIN students1 s ON o.student_id = s.id
    JOIN users1 u ON p.cashier_id = u.id
    WHERE r.receipt_no = ?
    LIMIT 1
");
$stmt->execute([$receipt_no]);
$r = $stmt->fetch();
if (!$r) exit('Receipt not found.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt <?= htmlspecialchars($r['receipt_no']) ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #fff; color: #222; }
        .receipt-box { max-width: 400px; margin: 40px auto; border:1px solid #e0e0e0; padding:2em; border-radius:8px; }
        h2 { text-align:center; margin-bottom:1em; }
        .info { margin-bottom:1em; }
        .label { color:#888; font-size:0.95em; }
        .amount { font-size:1.2em; font-weight:bold; margin:1em 0; }
        .print-btn { display:block; margin:1.5em auto 0; padding:0.5em 2em; background:#2d7ff9; color:#fff; border:none; border-radius:4px; cursor:pointer;}
        .print-btn:hover { background:#195ec7; }
    </style>
</head>
<body>
<div class="receipt-box">
    <h2>UNIVERSITY MANAGEMENT SYSTEM</h2>
    <div class="info"><span class="label">Receipt No:</span> <?= htmlspecialchars($r['receipt_no']) ?></div>
    <div class="info"><span class="label">Date:</span> <?= htmlspecialchars(date('Y-m-d', strtotime($r['issued_at']))) ?></div>
    <div class="info"><span class="label">Student No:</span> <?= htmlspecialchars($r['student_no']) ?></div>
    <div class="info"><span class="label">Name:</span> <?= htmlspecialchars($r['last_name'] . ', ' . $r['first_name'] . ' ' . $r['middle_name']) ?></div>
    <div class="info"><span class="label">Course/Year:</span> <?= htmlspecialchars($r['course']) ?> / <?= htmlspecialchars($r['year_level']) ?></div>
    <div class="info"><span class="label">Order Type:</span> <?= htmlspecialchars($r['order_type']) ?></div>
    <div class="amount">Amount Paid: ₱<?= htmlspecialchars(number_format($r['amount'], 2)) ?></div>
    <div class="info"><span class="label">Cashier:</span> <?= htmlspecialchars($r['cashier_name']) ?></div>
    <div class="info"><span class="label">Payment Date:</span> <?= htmlspecialchars(date('Y-m-d H:i', strtotime($r['payment_date']))) ?></div>
    <button class="print-btn" onclick="window.print()">Print</button>
    <form method="get" action="download_receipt.php" style="margin-top:1em;">
        <input type="hidden" name="receipt_no" value="<?= htmlspecialchars($r['receipt_no']) ?>">
        <button type="submit" class="print-btn" style="background:#43a047;">Download PDF</button>
    </form>
</div>
</body>
</html>
