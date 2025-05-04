<?php
include 'db_conn.php';

              // Handle borrow requests
              if (isset($_POST['borrow_request'])) {
                $item_name = $_POST['item_name'];
                $quantity = $_POST['quantity'];
                $sql = "INSERT INTO borrow_requests (item_name, quantity) VALUES ('$item_name', '$quantity')";
                $conn->query($sql);

                // Add notification
                $message = "New borrow request for $item_name ($quantity)";
                $conn->query("INSERT INTO notifications (message) VALUES ('$message')");
            }
        ?>
        
    <h2>Borrow Request</h2>
            <form action="index.php" method="POST">
                <input type="text" name="item_name" placeholder="Item Name" required>
                <input type="number" name="quantity" placeholder="Quantity" required>
                <button type="submit" name="borrow_request">Request Borrow</button>
            </form>