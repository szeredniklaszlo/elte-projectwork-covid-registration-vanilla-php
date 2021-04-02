<?php
    session_start();
    include_once("storage.php");
    $storage = new Storage(new JsonIO("users.json"));

    unset($err);
    $emailAddress = "";
    $redirect = isset($_SESSION["currentMonth"]) ? "?currentMonth=".$_SESSION["currentMonth"] : "";

    if (count($_POST) > 0) {
        if (isset($_POST["emailAddress"]) && trim($_POST["emailAddress"]) != "" && filter_var($_POST["emailAddress"], FILTER_VALIDATE_EMAIL)) {
            $emailAddress = $_POST["emailAddress"];

            if (isset($_POST["password"]) && strlen($_POST["password"]) > 0) {
                if (validDatas($emailAddress, $_POST["password"])) {
                    unset($_SESSION["regSuccessful"]);
                    $_SESSION["loggedAccount"] = $storage -> findOne(["emailAddress" => $emailAddress]);

                    if (isset($_SESSION["redirectTo"])) {
                        $redirectTo = $_SESSION["redirectTo"];
                        unset($_SESSION["redirectTo"]);

                        header("Location: checkin.php?id=".$redirectTo);
                        exit();
                    }

                    header("Location: index.php".$redirect);
                    exit();
                } else $err["badDatas"] = "Helytelen e-mail cím vagy jelszó!";
            } else $err["password"] = "Add meg a jelszót!";
        } else $err["emailAddress"] = "Formailag helyes e-mail címet adj meg!";
    }

    unset($_POST);


    function validDatas($emailAddress, $password): bool {
        global $storage;

        $user = $storage -> findOne(["emailAddress" => $emailAddress]);
        return $user != NULL && password_verify($password, $user["password"]);
    }
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css\style.css">
    <title>Bejelentkezés</title>
</head>
<body>
    <?php if(!isset($_SESSION["loggedAccount"])): ?>
        <?php if(isset($_SESSION["regSuccessful"]) && $_SESSION["regSuccessful"]): ?>
            <h1>Sikeres regisztráció!</h1>
        <?php else: ?>
            <h1>Kérjük, jelentkezzen be!</h1>
        <?php endif; ?>
        
        <form method="POST" action="login.php" novalidate>
            <table>
                <tr>
                    <td><label for="emailAddress">E-mail cím:</label></td>
                    <td><input name="emailAddress" id="emailAddress" type="email" value="<?= $emailAddress ?>"></td>
                    <td class="error"><?= isset($err["emailAddress"]) ? $err["emailAddress"] : "" ?></td>
                </tr>
                <tr>
                    <td><label for="password">Jelszó:</label></td>
                    <td><input name="password" id="password" type="password"></td>
                    <td class="error"><?= isset($err["password"]) ? $err["password"] : "" ?></td>
                </tr>
            </table>
            <p class="error"><?= isset($err["badDatas"]) ? $err["badDatas"] : "" ?></p>
            <button>Bejelentkezés</button>
            <?php if(!isset($_SESSION["regSuccessful"])): ?>
                <a class="button" href="signup.php">Még nincs fiókom!</a><br>
            <?php endif; ?>
            <a class="button" href="index.php<?= isset($_SESSION["currentMonth"]) ? "?currentMonth=".$_SESSION["currentMonth"] : "" ?>">Főoldalra</a>
        </form>
    <?php else: header("Location: index.php".$redirect); ?>
    <?php endif; ?>
</body>
</html>