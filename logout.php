<?php
    session_start();
    $redirect = isset($_SESSION["currentMonth"]) ? "?currentMonth=".$_SESSION["currentMonth"] : "";
    session_destroy();

    header("Location: index.php".$redirect);
?>