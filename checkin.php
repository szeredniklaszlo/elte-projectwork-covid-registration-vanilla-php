<?php
    session_start();

    if (!isset($_SESSION["loggedAccount"])) {
        if (isset($_GET["id"])) {
            $_SESSION["redirectTo"] = $_GET["id"];
        }
        header("Location: login.php");
        exit();
    } else if ($_SESSION["loggedAccount"]["claimedDateId"] != "") {
        $redirect = isset($_SESSION["currentMonth"]) ? "?currentMonth=".$_SESSION["currentMonth"] : "";
        header("Location: index.php".$redirect);
        exit();
    }

    if (isset($_GET["id"])) {
        $_SESSION["id"] = $_GET["id"];
    }

    include_once("storage.php");
    $userStorage = new Storage(new JsonIO("users.json"));
    $dateStorage = new Storage(new JsonIO("dates.json"));
    $date = $dateStorage -> findById($_SESSION["id"]);
    $user = $userStorage -> findById($_SESSION["loggedAccount"]["id"]);
    $users = $userStorage -> findAll();

    unset($err);

    if ($date == NULL) {
        $redirect = isset($_SESSION["currentMonth"]) ? "?currentMonth=".$_SESSION["currentMonth"] : "";
        header("Location: index.php".$redirect);
        exit();
    }

    if (count($_POST) > 0) {
        if (isset($_POST["accepted"])) {
            array_push($date["users"], $_SESSION["loggedAccount"]["id"]);
            $date["claimed"]++;
            $dateStorage -> update($date["id"], $date);

            $_SESSION["loggedAccount"]["claimedDateId"] = $_SESSION["id"];
            $user["claimedDateId"] = $_SESSION["id"];
            $userStorage -> update($user["id"], $user);

            $_SESSION["successfulCheckin"] = true;
            header("Location: successfulcheckin.php");
            exit();
        } else $err = "Az oltásra való jelentkezésért el kell fogadnod a feltételeket!";
    }

    unset($_POST);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css\style.css">
    <title>Jelentkezés</title>
</head>
<body id="checkin">
    <h1>Időpont: <?= $date["date"]." ".$date["time"] ?> <span id="<?= $date["claimed"] == $date["capacity"] ? "full" : "available" ?>"><?=" (".$date["claimed"]."/".$date["capacity"].")" ?></span></h1>
    <?php if(!isset($_SESSION["loggedAccount"]["isAdmin"])): ?>
        <h2>Az Ön adatai:</h2>
        <table>
            <tr>
                <td>Név:</td>
                <td><?= $_SESSION["loggedAccount"]["fullName"] ?></td>
            </tr>
            <tr>
                <td>Lakcím</td>
                <td><?= $_SESSION["loggedAccount"]["mailAddress"] ?></td>
            </tr>
            <tr>
                <td>TAJ szám:</td>
                <td><?= $_SESSION["loggedAccount"]["TAJnumber"] ?></td>
            </tr>
        </table>
        <?php if($date["claimed"] != $date["capacity"]): ?>
            <form action="checkin.php" method="POST" id="acceptTerms" novalidate>
                <input type="hidden" name="submitted" id="submitted">
                <label for="accepted"><input id="accepted" name="accepted" type="checkbox" value="accepted"> Elfogadom a jelentkezés feltételeit (kötelező megjelenni, lehetnek az oltásnak mellékhatásai)</label>
                <br>
                <div class="error"><?= isset($err) ? $err : "" ?></div>
                <button type="submit">Jelentkezem!</button>
            </form>
        <?php else: ?>
            <h2 class="error">Ezen az időponton már nincs több férőhely</h2>
        <?php endif; ?>
    <?php else: ?>
        <h2>A jelentkezettek adatai:</h2>
        <table id="users">
            <tr>
                <th>Név</th>
                <th>TAJ szám</th>
                <th>E-mail cím</th>
            </tr>
            <?php foreach($users as $aUser): ?>
                <?php if(in_array($aUser["id"], $date["users"])): ?>
                    <tr>
                        <td><?= $aUser["fullName"] ?></td>
                        <td><?= $aUser["TAJnumber"] ?></td>
                        <td><?= $aUser["emailAddress"] ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    <a class="button" href="index.php<?= isset($_SESSION["currentMonth"]) ? "?currentMonth=".$_SESSION["currentMonth"] : "" ?>">Főoldalra</a>
</body>
</html>