<?php
include 'db_conn.php';

            // Delete Supply
            if (isset($_POST['delete_supply'])) {
                $delete_id = $_POST['delete_id'];
                $conn->query("DELETE FROM supplies WHERE id = $delete_id");
            }

        
            // Add a new supply
            if (isset($_POST['add_supply'])) {
                $item_name = $_POST['item_name'];
                $quantity = $_POST['quantity'];
                $sql = "INSERT INTO supplies (item_name, quantity) VALUES ('$item_name', '$quantity')";
                $conn->query($sql);
            }

            // Handle purchase in/out transactions
            if (isset($_POST['transaction'])) {
                $supply_id = $_POST['supply_id'];
                $quantity = $_POST['quantity'];
                $action = $_POST['action'];
                $sql = "INSERT INTO transactions (supply_id, quantity, action) VALUES ('$supply_id', '$quantity', '$action')";
                $conn->query($sql);

                // Update supply quantity based on action
                if ($action == 'purchase_in') {
                    $conn->query("UPDATE supplies SET quantity = quantity + $quantity WHERE id = $supply_id");
                } elseif ($action == 'purchase_out') {
                    $conn->query("UPDATE supplies SET quantity = quantity - $quantity WHERE id = $supply_id");
                }
            }

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

        <!-- Add Supply -->
        <h4>Add New Supply</h4>
            <form action="index.php" method="POST">
                <input type="text" name="item_name" placeholder="Item Name" required>
                <input type="number" name="quantity" placeholder="Quantity" required>
                <button type="submit" name="add_supply">Add Supply</button>
            </form>

        <!-- Add Purchase In/Out Transaction -->
        <h4>Purchase In/Out</h4>
            <form action="index.php" method="POST">
                <select name="supply_id" required>
                    <?php
                        $result = $conn->query("SELECT * FROM supplies");
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>{$row['item_name']}</option>";
                            }
                    ?>
                </select>
                <input type="number" name="quantity" placeholder="Quantity" required>
                    <select name="action" required>
                        <option value="purchase_in">Purchase In</option>
                        <option value="purchase_out">Purchase Out</option>
                    </select>
                <button type="submit" name="transaction">Submit Transaction</button>
            </form>

        <!-- Borrow Request -->
        <h2>Borrow Request</h2>
            <form action="index.php" method="POST">
                <input type="text" name="item_name" placeholder="Item Name" required>
                <input type="number" name="quantity" placeholder="Quantity" required>
                <button type="submit" name="borrow_request">Request Borrow</button>
            </form>

        <!-- View Notifications -->
        <h2>Notifications</h2>
        <?php
            $notifications = $conn->query("SELECT * FROM notifications WHERE status = 'unread'");
            while ($row = $notifications->fetch_assoc()) {
                echo "<p>{$row['message']}</p>";
                echo "<form action='read_notification.php' method='POST'>
                        <input type='hidden' name='notification_id' value='{$row['id']}'>
                        <button type='submit'>Mark as Read</button>
                    </form>";
            }
            ?>
