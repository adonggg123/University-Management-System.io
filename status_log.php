 <!-- View Status -->
 <h2>System Status</h2>
        <table border="1">
            <tr><th>Item Name</th><th>Quantity</th></tr>
            <?php
                $supplies = $conn->query("SELECT * FROM supplies");
                while ($row = $supplies->fetch_assoc()) {
                    echo "<tr><td>{$row['item_name']}</td><td>{$row['quantity']}</td></tr>";
                }
            ?>
        </table>