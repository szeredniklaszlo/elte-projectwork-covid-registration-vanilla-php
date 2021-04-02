<?php
    session_start();
    include_once("storage.php");
    $storage = new Storage(new JsonIO("users.json"));
    
    $fullName = "";
    $TAJnumber = "";
    $mailAddress = "";
    $emailAddress = "";
    $password = "";

    unset($err);
    $finished = false;

    if (count($_POST) > 0) {
        if (isset($_POST["fullName"]) && trim($_POST["fullName"]) != "") {
            $fullName = $_POST["fullName"];
        } else $err["fullName"] = "Teljes név megadása kötelező!";

        if (isset($_POST["TAJnumber"]) && trim($_POST["TAJnumber"]) != "") {
            if (filter_var($_POST["TAJnumber"], FILTER_VALIDATE_INT) && intval($_POST["TAJnumber"]) >= 100000000 && intval($_POST["TAJnumber"]) <= 999999999) {
                $TAJnumber = $_POST["TAJnumber"];
            } else $err["TAJnumber"] = "A TAJ szám csak számokból álló, 9 jegyű számsor!";
        } else $err["TAJnumber"] = "TAJ szám megadása kötelező!";

        if (isset($_POST["mailAddress"]) && trim($_POST["mailAddress"]) != "") {
            $mailAddress = $_POST["mailAddress"];
        } else $err["mailAddress"] = "Értesítési cím megadása kötelező!";

        if (isset($_POST["emailAddress"]) && trim($_POST["emailAddress"]) != "") {
            if (filter_var($_POST["emailAddress"], FILTER_VALIDATE_EMAIL)) {
                if (emailIsAvailable($_POST["emailAddress"])) {
                    $emailAddress = $_POST["emailAddress"];
                } else $err["emailAddress"] = "A megadott e-mail címmel már regisztráltak!";
            } else $err["emailAddress"] = "A megadott e-mail cím nem megfelelő formátumú!";
        } else $err["emailAddress"] = "E-mail cím megadása kötelező!";

        if (isset($_POST["password"]) && $_POST["password"] != "") {
            if (strlen($_POST["password"]) >= 6) {
                if (isset($_POST["passwordAgain"])) {
                    if ($_POST["password"] == $_POST["passwordAgain"]) {
                        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
                    } else {
                        $err["password"] = "A megadott jelszavak nem egyeznek!";
                        $err["passwordAgain"] = "A megadott jelszavak nem egyeznek!";
                    }
                } else $err["passwordAgain"] = "Kérem adja meg újra a jelszavát!";
            } else $err["password"] = "A jelszó legalább 6 karakter hosszú legyen!";
        } else $err["password"] = "Jelszó megadása kötelező!";

        if (!isset($err)) {
            $finished = true;
            $userdata = array("fullName" => $fullName, "TAJnumber" => $TAJnumber, "mailAddress" => $mailAddress, "claimedDateId" => "", "emailAddress" => $emailAddress, "password" => $password);
            $storage -> add($userdata);
        }
    }

    unset($_POST);


    function emailIsAvailable(string $emailAddress): bool {
        global $storage;

        return $storage -> findOne(["emailAddress" => $emailAddress]) == NULL;
    }
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css\style.css">
    <title>Regisztráció - NemKoViD - Mondj nemet a koronavírusra!</title>
</head>
<body>
    <?php if(!$finished && !isset($_SESSION["loggedAccount"])): ?>
    <h1>Regisztráció</h1>
    <form method="POST" action="signup.php" novalidate>
        <table>
            <tr>
                <td><label for="fullName">Teljes név:</label></td>
                <td><input name="fullName" id="fullName" type="text" value="<?= $fullName ?>"></td>
                <td class="error"><?= isset($err["fullName"]) ? $err["fullName"] : "" ?></td>
            </tr>
            <tr>
                <td><label for="TAJnumber">TAJ szám:</label></td>
                <td><input name="TAJnumber" id="TAJnumber" type="number" min="100000000" max="999999999" value="<?= $TAJnumber ?>"></td>
                <td class="error"><?= isset($err["TAJnumber"]) ? $err["TAJnumber"] : "" ?></td>
            </tr>
            <tr>
                <td><label for="mailAddress">Értesítési cím:</label></td>
                <td><input name="mailAddress" id="mailAddress" type="text" value="<?= $mailAddress ?>"></td>
                <td class="error"><?= isset($err["mailAddress"]) ? $err["mailAddress"] : "" ?></td>
            </tr>
            <tr>
                <td><label for="emailAddress">E-mail cím:</label></td>
                <td><input name="emailAddress" id="emailAddress" type="email" value="<?= $emailAddress ?>"></td>
                <td class="error"><?= isset($err["emailAddress"]) ? $err["emailAddress"] : "" ?></td>
            </tr>
            <tr>
                <td><label for="password">Jelszó:</label></td>
                <td><input name="password" id="password" type="password" value="<?= $password ?>"></td>
                <td class="error"><?= isset($err["password"]) ? $err["password"] : "" ?></td>
            </tr>
            <tr>
                <td><label for="passwordAgain">Jelszó újra:</label></td>
                <td><input name="passwordAgain" id="passwordAgain" type="password" value="<?= $password ?>"></td>
                <td class="error"><?= isset($err["passwordAgain"]) ? $err["passwordAgain"] : "" ?></td>
            </tr>
        </table>
        <button type="submit">Regisztrálok!</button>
        <a class="button" href="login.php">Már van fiókom!</a><br>
        <a class="button" href="index.php<?= isset($_SESSION["currentMonth"]) ? "?currentMonth=".$_SESSION["currentMonth"] : "" ?>">Főoldalra</a>
    </form>
    <?php else: ?>
        <?php
            if ($finished) {
                $_SESSION["regSuccessful"] = true;
                $finished = false;

                header("Location: login.php");
                exit();
            }
            if (isset($_SESSION["loggedAccount"])) {
                $redirect = isset($_SESSION["currentMonth"]) ? "?currentMonth=".$_SESSION["currentMonth"] : "";
                header("Location: index.php".$redirect);
            }
        ?>
    <?php endif; ?>
</body>
</html>