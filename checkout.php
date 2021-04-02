<?php
    session_start();
    if (isset($_SESSION["loggedAccount"]) && $_SESSION["loggedAccount"]["claimedDateId"] != "") {
        include_once("storage.php");
        $userStorage = new Storage(new JsonIO("users.json"));
        $dateStorage = new Storage(new JsonIO("dates.json"));

        $user = $userStorage -> findById($_SESSION["loggedAccount"]["id"]);
        $date = $dateStorage -> findById($_SESSION["loggedAccount"]["claimedDateId"]);

        $_SESSION["loggedAccount"]["claimedDateId"] = "";
        $user["claimedDateId"] = "";
        $date["claimed"]--;
        unset($date["users"][array_search($user["id"], $date["users"])]);

        $userStorage -> update($user["id"], $user);
        $dateStorage -> update($date["id"], $date);
    }

    $redirect = isset($_SESSION["currentMonth"]) ? "?currentMonth=".$_SESSION["currentMonth"] : "";
    header("Location: index.php".$redirect);
?>