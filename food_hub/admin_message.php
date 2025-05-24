<?php
$conn = new mysqli("localhost", "root", "quest4inno@server", "university_management_system");

$result = $conn->query("SELECT * FROM contact_messages ORDER BY submitted_at DESC");

echo "<h1>Messages</h1><table border='1'><tr><th>Name</th><th>Email</th><th>Message</th><th>Time</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['name']}</td><td>{$row['email']}</td><td>{$row['message']}</td><td>{$row['submitted_at']}</td></tr>";
}
echo "</table>";

$conn->close();
?>
