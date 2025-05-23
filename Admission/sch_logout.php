<?php
session_start();
session_destroy();
header("Location: sch_login.php");
?>
