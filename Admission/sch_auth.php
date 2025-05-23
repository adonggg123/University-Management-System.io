<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: sch_login.php");
    exit();
}
?>
