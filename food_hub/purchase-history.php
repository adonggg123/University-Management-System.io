<?php 
$servername = "localhost";
$username = "root";
$password = "";
$database = "university_management_system";

// Connect to database
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch orders (sorted by latest)
$result = $conn->query("SELECT * FROM orders_fh ORDER BY created_at DESC");

$orders = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
} else {
    echo "No orders found.";
}

$conn->close();

// Helper function to generate status badge
function getStatusBadge($status) {
    $statusClasses = [
        'pending' => 'badge-warning',
        'processing' => 'badge-info',
        'delivered' => 'badge-success',
        'cancelled' => 'badge-danger',
        'completed' => 'badge-primary'
    ];
    
    $class = isset($statusClasses[strtolower($status)]) ? $statusClasses[strtolower($status)] : 'badge-secondary';
    return '<span class="badge ' . $class . '">' . htmlspecialchars($status) . '</span>';
}

// Helper function to format dates
function formatDate($datetime) {
    return date("F j, Y g:i A", strtotime($datetime));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="uploads/img/images (2).jpg"/>
    <title> History | FoodHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --primary: #FF6B35;
            --secondary: #2EC4B6;
            --dark: #333333;
            --light: #F7F7F7;
            --gray: #E0E0E0;
            --success: #4CAF50;
            --warning: #FFC107;
            --danger: #F44336;
            --info: #2196F3;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border-radius: 8px;
        }
        
        h1 {
            color: var(--primary);
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #777;
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .order-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: #f9f9f9;
            border-bottom: 1px solid var(--gray);
        }
        
        .order-id {
            font-weight: 600;
            color: var(--dark);
        }
        
        .order-date {
            font-size: 14px;
            color: #777;
        }
        
        .order-body {
            padding: 20px;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .info-item {
            margin-bottom: 5px;
        }
        
        .info-label {
            font-size: 13px;
            color: #777;
            margin-bottom: 3px;
        }
        
        .info-value {
            font-weight: 500;
        }
        
        .price-value {
            font-weight: 600;
            color: var(--primary);
            font-size: 18px;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: white;
        }
        
        .badge-success {
            background-color: var(--success);
        }
        
        .badge-warning {
            background-color: var(--warning);
            color: #333;
        }
        
        .badge-danger {
            background-color: var(--danger);
        }
        
        .badge-info {
            background-color: var(--info);
        }
        
        .badge-primary {
            background-color: var(--primary);
        }
        
        .badge-secondary {
            background-color: #757575;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .empty-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ccc;
        }
        .nav
{
    max-width: 1900px;
    display: flex;
    align-items: center;
    gap: 450px;
    /* margin-left: 25px; */
    margin-bottom: 10px;
}
.nav a
{
    text-decoration: none;
    color: #0B1215;
    background-color: #fb8500;
    border-radius: 6px;
    box-shadow: 0px 2px 4px rgba(0,0,0,0.2  );
    padding: 5px;
}
        
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-date {
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
      <div class="nav">
        <a href="index.php">↩ Back to Home</a>
            
       </div>
        <header>
            <h1>Purchase History</h1>
            <p class="subtitle">View all your previous orders and their status</p>
        </header>
        
        <!-- <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= isset($order['id']) ? $order['id'] : 'N/A' ?></td>
                <td><?= isset($order['customer_name']) ? htmlspecialchars($order['customer_name']) : 'N/A' ?></td>
                <td><?= isset($order['room_number']) ? htmlspecialchars($order['room_number']) : 'N/A' ?></td>
                <td><?= isset($order['payment_method']) ? htmlspecialchars($order['payment_method']) : 'N/A' ?></td>
                <td><?= isset($order['status']) ? getStatusBadge($order['status']) : 'N/A' ?></td>
                <td>₱<?= isset($order['total_price']) ? number_format($order['total_price'], 2) : '0.00' ?></td>
            </tr>
        <?php endforeach; ?> -->

        <?php foreach ($orders as $order): ?>
            <div clas      s="order-card">
                <div class="order-header">
                    <div class="order-id">Order #<?= $order['id'] ?></div>
                    <div class="order-date"><?= formatDate($order['created_at']) ?></div>
                </div>
                <div class="order-body">
                    <div class="order-info">
                        <div class="info-item">
                            <div class="info-label">Customer Name</div>
                            <div class="info-value"><?= htmlspecialchars($order['customer_name']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Room</div>
                            <div class="info-value"><?= htmlspecialchars($order['room_number']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Payment Method</div>
                            <div class="info-value"><?= htmlspecialchars($order['payment_method']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value"><?= getStatusBadge($order['status']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Total Amount</div>
                            <div class="info-value price-value">₱<?= number_format($order['total_price'], 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>  
    </div>
</body>
</html>