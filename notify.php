<?php
include 'db_conn.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>
    <div class="notification-container">
        <h2>Notifications</h2>
        <table border="1" cellpadding="10">
            <tr>
                <th>Item</th>
                <th>Quantity Requested</th>
                <th>Date Requested</th>
                <th>Action</th>
            </tr>
            <?php
            $result = $conn->query("SELECT * FROM requests ORDER BY request_date DESC");
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['item_name']}</td>
                        <td>{$row['quantity']}</td>
                        <td>{$row['request_date']}</td>
                        <td>
                            <form action='read_notification.php' method='POST'>
                                <input type='hidden' name='notification_id' value='{$row['id']}'>
                                <button type='submit'>Mark as Read</button>
                            </form>
                        </td>
                    </tr>";
            }
            ?>
        </table>

        <br><a href="#supply">Back to Supply</a>
    </div>
</body>
</html>

