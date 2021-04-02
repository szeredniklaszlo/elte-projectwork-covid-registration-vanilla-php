<?php
    session_start();

    include_once("storage.php");
    $userStorage = new Storage(new JsonIO("users.json"));
    $dateStorage = new Storage(new JsonIO("dates.json"));

    $redirect = isset($_SESSION["currentMonth"]) ? "?currentMonth=".$_SESSION["currentMonth"] : "";
    if (count($_SESSION) == 0 || (count($_SESSION) > 0 && !isset(($userStorage -> findById($_SESSION["loggedAccount"]["id"]))["isAdmin"]))) {
        header("Location: index.php".$redirect);
        exit();
    }

    $date = "";
    $time = "";
    $capacity = "";

    unset($err);

    if (count($_POST) > 0) {
        if (isset($_POST["date"]) && trim($_POST["date"]) != "") {
            if (isValidDate($_POST["date"])) {
                $date = $_POST["date"];
            } else $err["date"] = "Formailag helyes, 2021-es dátumot adj meg!";
        } else $err["date"] = "Dátum megadása kötelező!";

        if (isset($_POST["time"]) && trim($_POST["time"]) != "") {
            if (isValidTime($_POST["time"])) {
                $time = $_POST["time"];
            } else $err["time"] = "Formailag helyes időt adj meg!";
        } else $err["time"] = "Idő megadása kötelező!";

        if ($date != "" && $time != "" && $dateStorage -> findOne(["date" => $date, "time" => $time]) != NULL) {
            $err["alreadySubmitted"] = "Ez az időpont már hozzá van adva!";
        }

        if (isset($_POST["capacity"]) && trim($_POST["capacity"]) != "") {
            if (filter_var($_POST["capacity"], FILTER_VALIDATE_INT) && intval($_POST["capacity"]) >= 1) {
                $capacity = $_POST["capacity"];
            } else $err["capacity"] = "A férőhelyek száma csak pozitív egész szám lehet!";
        } else $err["capacity"] = "Férőhelyek számának megadása kötelező!";

        if (!isset($err)) {
            $_SESSION["successfulDateSubmit"] = true;
            $newDate = array("date" => $date, "time" => $time, "capacity" => intval($capacity), "claimed" => 0, "users" => []);
            $dateStorage -> add($newDate);
            header("Location: index.php".$redirect);
        }
    }

    unset($_POST);

    function isValidDate($date): bool {
        $dateArray = explode("-", $date);
        return count($dateArray) == 3 && $dateArray[0] == "2021" && DateTime::createFromFormat("Y-m-d", $date) != false;
    }

    function isValidTime($time): bool {
        return DateTime::createFromFormat("H:i", $time) != false;
    }
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css\style.css">
    <title>Új időpont hozzáadása</title>
</head>
<body>
    <h1>Új időpont hozzáadása</h1>
    <form action="addnewdate.php" method="POST" novalidate>
        <table>
            <tr>
                <td><label for="date">Dátum:</label></td>
                <td><input name="date" id="date" type="date" min="2021-01-01" max="2021-12-31" value="<?= $date ?>"></td>
                <td class="error"><?= isset($err["date"]) ? $err["date"] : "" ?></td>
            </tr>
            <tr>
                <td><label for="time">Idő:</label></td>
                <td><input name="time" id="time" type="time" value="<?= $time ?>"></td>
                <td class="error"><?= isset($err["time"]) ? $err["time"] : "" ?></td>
            </tr>
            <tr>
                <td><label for="capacity">Helyek száma:</label></td>
                <td><input name="capacity" id="capacity" type="number" min="1" value="<?= $capacity ?>"></td>
                <td class="error"><?= isset($err["capacity"]) ? $err["capacity"] : "" ?></td>
            </tr>
        </table>
        <span><?= isset($err["alreadySubmitted"]) ? $err["alreadySubmitted"] : "" ?></span>
        <?= isset($err["alreadySubmitted"]) ? "<br>" : "" ?>

        <a class="button" href="index.php<?= isset($_SESSION["currentMonth"]) ? "?currentMonth=".$_SESSION["currentMonth"] : "" ?>">Főoldalra</a>
        <button type="submit">Hozzáadás</button>
    </form>
</body>
</html>