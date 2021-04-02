<?php
    session_start();
    if (!isset($_SESSION["successfulCheckin"])) {
        $redirect = isset($_SESSION["currentMonth"]) ? "?currentMonth=".$_SESSION["currentMonth"] : "";
        header("Location: index.php".$redirect);
        exit();
    }

    unset($_SESSION["successfulCheckin"]);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css\style.css">
    <title>Sikeres jelentkezés!</title>
</head>
<body>
    <h1>Sikeres jelentkezés!</h1>
    <p>Hamarosan átirányítunk a főoldalra...</p>
    <meta http-equiv="refresh" content="2;url=index.php<?= isset($_SESSION["currentMonth"]) ? "?currentMonth=".$_SESSION["currentMonth"] : "" ?>">
</body>
</html>