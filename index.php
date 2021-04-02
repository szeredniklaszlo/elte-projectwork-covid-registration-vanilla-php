<?php
    session_start();
    include_once("storage.php");
    $userStorage = new Storage(new JsonIO("users.json"));
    $dateStorage = new Storage(new JsonIO("dates.json"));

    $months = [1 => "Január", 2 => "Február", 3 => "Március", 4 => "Április", 5 => "Május", 6 => "Június",
               7 => "Július", 8 => "Augusztus", 9 => "Szeptember", 10 => "Október", 11 => "November", 12 => "December"];
    if (!isset($currentMonth)) {
        $currentMonth = 1;
    }

    if (isset($_GET["currentMonth"])) {
        $currentMonth = intval($_GET["currentMonth"]);
        if ($currentMonth <= 0) {
            $_SESSION["currentMonth"] = 1;
        } else if ($currentMonth >= 13) {
            $_SESSION["currentMonth"] = 12;
        } else {
            $_SESSION["currentMonth"] = $currentMonth;
        }
    }

    $getDatesByMonth = function($date) use($currentMonth) {
        return intval(explode("-", $date["date"])[1]) == $currentMonth;
    };

    $compareDates = function($a, $b) {
        $aDay = intval(explode("-", $a["date"])[2]);
        $bDay = intval(explode("-", $b["date"])[2]);

        if ($aDay == $bDay) {
            $aTime = explode(":", $a["time"]);
            $bTime = explode(":", $b["time"]);

            $aHour = intval($aTime[0]);
            $bHour = intval($bTime[0]);

            if ($aHour == $bHour) {
                $aMin = intval($aTime[1]);
                $bMin = intval($bTime[1]);

                return ($aMin < $bMin) ? -1 : 1;
            }

            return ($aHour < $bHour) ? -1 : 1;
        }
        
        return ($aDay < $bDay) ? -1 : 1;
    };

    $dates = $dateStorage -> findMany($getDatesByMonth);
    usort($dates, $compareDates);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css\style.css">
    <title>NemKoViD - Mondj nemet a koronavírusra!</title>
</head>
<body>
    <h1>Védőoltás koronavírus ellen!</h1>
    <p>A Nemzeti Koronavírus Depó (NemKoViD - Mondj nemet a koronavírusra!) központi épületében különböző időpontokban oltásokat szervez.<br>
       Védőoltásra való jelentkezését ezen az oldalon adhatja le. Fontos tudniuk:<br>
       a koronavírus ellen védőoltással csak akkor tudunk hatékonyan védekezni, ha az emberek legalább 60%-a beoltatta magát.</p>

    <?php if(!isset($_SESSION["loggedAccount"])): ?>
        <a class="button" href="login.php">Bejelentkezés</a>
        <a class="button" href="signup.php">Regisztráció</a>
    <?php else: ?>
        <span class="loggedIn">Bejelentkezve: <span><?= $_SESSION["loggedAccount"]["fullName"] ?></span> <?= " (".$_SESSION["loggedAccount"]["emailAddress"].")" ?></span><br>
        <div>
        <?php if(!isset(($userStorage -> findById($_SESSION["loggedAccount"]["id"]))["isAdmin"])): ?>
            <?php if($_SESSION["loggedAccount"]["claimedDateId"] != ""): ?>
                <h1>Az ön időpontja: <?= ($dateStorage -> findById($_SESSION["loggedAccount"]["claimedDateId"]))["date"]." ".($dateStorage -> findById($_SESSION["loggedAccount"]["claimedDateId"]))["time"] ?></h1>
            <?php else: ?>
                <h2>Még nincs foglalt időpontja.</h2>
            <?php endif; ?>
        <?php endif; ?>
        </div>
        <?php if (isset($_SESSION["successfulDateSubmit"])): ?>
            <?php unset($_SESSION["successfulDateSubmit"]); ?>
            <span id="successfulDateSubmit">Időpont sikeresen hozzáadva!</span><br>
        <?php endif; ?>
        <?php if($_SESSION["loggedAccount"]["claimedDateId"] != ""): ?>
            <a class="button" href="checkout.php">Lemondás</a>
        <?php endif; ?>
        <a class="button" href="logout.php">Kijelentkezés</a>
    <?php endif; ?>
    <h2>Védőoltások időpontjai 2021-ben (<?= $months[$currentMonth] ?>)</h2>
    <?php if(count($_SESSION) > 0 && isset($_SESSION["loggedAccount"]) && isset(($userStorage -> findById($_SESSION["loggedAccount"]["id"]))["isAdmin"]) &&
            ($userStorage -> findById($_SESSION["loggedAccount"]["id"]))["isAdmin"]): ?>
        <a href="addnewdate.php">Új időpont meghirdetése</a>
    <?php endif; ?>
    <table id="dates">
        <tr>
            <th>Nap</th>
            <th>Időpont</th>
            <th>Szabad hely / Összes hely</th>
            <?php if(isset($_SESSION["loggedAccount"]["claimedDateId"]) && $_SESSION["loggedAccount"]["claimedDateId"] == ""): ?>
                <th>Link</th>
            <?php endif; ?>
        </tr>
        <?php foreach($dates as $date): ?>
            <tr class="<?= $date["claimed"] == $date["capacity"] ? "full" : "available" ?>">
                <td><?= $date["date"] ?></td>
                <td><?= $date["time"] ?></td>
                <td><?= $date["claimed"]."/".$date["capacity"] ?></td>
                <?php if(!isset($_SESSION["loggedAccount"]) || isset($_SESSION["loggedAccount"]["claimedDateId"]) && $_SESSION["loggedAccount"]["claimedDateId"] == "" && !isset($_SESSION["loggedAccount"]["isAdmin"])): ?>
                    <td><a href="checkin.php?id=<?= $date["id"] ?>">Jelentkezés</a></td>
                <?php elseif(isset($_SESSION["loggedAccount"]["isAdmin"])): ?>
                    <td><a href="checkin.php?id=<?= $date["id"] ?>">Részletek</a></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php if($currentMonth - 1 != 0): ?>
        <a href="index.php?currentMonth=<?= $currentMonth - 1 ?>">Előző hónap</a>
    <?php endif; ?>
    <?php if($currentMonth + 1 != 13): ?>
        <a href="index.php?currentMonth=<?= $currentMonth + 1 ?>">Következő hónap</a>
    <?php endif; ?>
</body>
</html>