<?php
include 'connect.php';

$result = $conn->query("SELECT * FROM scholarships ORDER BY deadline ASC");
?>

<h2>Available Scholarships</h2>
<table border="1">
  <tr>
    <th>Title</th>
    <th>Category</th>
    <th>Eligibility</th>
    <th>Deadline</th>
    <th>Benefits</th>
  </tr>
  <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
      <td><?php echo $row['title']; ?></td>
      <td><?php echo $row['category']; ?></td>
      <td><?php echo $row['eligibility']; ?></td>
      <td><?php echo $row['deadline']; ?></td>
      <td><?php echo $row['benefits']; ?></td>
    </tr>
  <?php } ?>
</table>
