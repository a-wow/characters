<?php
$servername = "localhost";
$username = "user";
$password = "website";
$dbname = "characters";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require 'char.php';

if (isset($_GET['guid'])) {
    $guid = (int)$_GET['guid'];
    $sql = "SELECT * FROM characters WHERE guid = $guid";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $character = $result->fetch_assoc();

        $achievementCountQuery = "SELECT COUNT(*) as achievement_count FROM character_achievement WHERE guid = $guid";
        $achievementCountResult = $conn->query($achievementCountQuery);
        $achievementCountData = $achievementCountResult->fetch_assoc();
        $character['achievement_count'] = (int)$achievementCountData['achievement_count'];

        $money = $character['money'];
        $gold = floor($money / 10000);
        $silver = floor(($money % 10000) / 100);
        $copper = $money % 100;

        $character['money_formatted'] = [
            'gold' => $gold,
            'silver' => $silver,
            'copper' => $copper,
        ];
        $character['class_name'] = $class_names[$character['class']] ?? 'unknown';
        $character['race_name'] = $race_names[$character['race']] ?? 'unknown';
        $character['class_image'] = $class_image[$character['class']] ?? 'assets/images/classes/default.png';
        $character['race_image'] = $race_image[$character['race']][$character['gender']] ?? 'assets/images/race/default.png';

        $character['totalHonorPoints'] = (int)$character['totalHonorPoints'];
        $character['arenaPoints'] = (int)$character['arenaPoints'];
        $character['totalKills'] = (int)$character['totalKills'];
        $character['online'] = (int)$character['online'];

        echo json_encode($character);
    } else {
        echo json_encode(null);
    }
} else {
    echo json_encode(null);
}

$conn->close();
?>
