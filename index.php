<?php
$servername = "localhost";
$username = "user";
$password = "password";
$dbname = "characters";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$searchTerm = '';
$result = null;
if (isset($_POST['search']) && !empty($_POST['search'])) {
    $searchTerm = $conn->real_escape_string($_POST['search']);
    $sql = "SELECT * FROM characters WHERE name LIKE '%$searchTerm%'";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Character Search</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            color: #333;
        }
        .search-container {
            margin-bottom: 20px;
            width: 100%;
            max-width: 600px;
            display: flex;
        }
        .search-container input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px 0 0 5px;
        }
        .search-container button {
            padding: 10px 20px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
        }
        .search-container button:hover {
            background-color: #0056b3;
        }
        .character-list {
            list-style: none;
            padding: 0;
            width: 100%;
            max-width: 600px;
        }
        .character-list li {
            background: white;
            margin: 10px 0;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            cursor: pointer;
        }
        .character-list li:hover {
            background: #f0f0f0;
        }
        .character-details {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: relative;
        }
        .health-bar, .mana-bar {
            height: 20px;
            border-radius: 5px;
            position: relative;
        }
        .health-bar {
            background-color: green;
        }
        .mana-bar {
            background-color: blue;
            margin-top: 5px;
        }
        .bar-text {
            position: absolute;
            color: white;
            text-align: center;
            line-height: 20px;
            width: 100%;
        }
        .achievement {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>

<h1>Поиск персонажей</h1>
<div class="search-container">
    <form method="POST" style="display: flex; width: 100%;">
        <input type="text" name="search" placeholder="Введите имя персонажа" value="<?php echo htmlspecialchars($searchTerm); ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>
</div>

<ul class="character-list">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <li onclick="showDetails(<?php echo $row['guid']; ?>)">
                <?php echo htmlspecialchars($row['name']); ?> (Уровень: <?php echo $row['level']; ?>)
            </li>
        <?php endwhile; ?>
    <?php elseif (isset($_POST['search'])): ?>
        <li>Персонажи не найдены.</li>
    <?php endif; ?>
</ul>

<div id="character-details" class="character-details"></div>

<script>
    function showDetails(guid) {
        const detailsDiv = document.getElementById('character-details');
        detailsDiv.style.display = 'block';
        detailsDiv.innerHTML = 'Загрузка...';

        fetch(`get_character_details.php?guid=${guid}`)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    const maxExperience = 1750000; // Максимальное количество опыта
                const experiencePercentage = (data.xp / maxExperience) * 100;
                    detailsDiv.innerHTML = `
                        <div style="display: flex; align-items: center;">
                            <img src="${data.race_image}" alt="${data.race_name}" style="width: 50px; height: auto; margin-right: 10px;">
                            <img src="${data.class_image}" alt="${data.class_name}" style="width: 50px; height: auto;">
                            <h2 style="margin-right: 20px;">${data.name}</h2>
                            <p>${data.level} lvl</p>
                        </div>
                        <div style="margin: 10px 0;">
                            <div class="health-bar" style="width: ${data.health / data.maxHealth * 100}%;">
                                <div class="bar-text">${data.health}</div>
                            </div>
                        </div>
                        <div style="margin: 10px 0;">
                            <div class="mana-bar" style="width: ${data.power1 / data.maxPower * 100}%;">
                                <div class="bar-text">${data.power1}</div>
                            </div>
                        </div>
                        <div style="margin: 10px 0;">
                        <div class="experience-bar" style="background: #e0e0e0; border-radius: 5px;">
                            <div class="bar-text" style="position: absolute; width: 100%; text-align: center; color: black;">${data.xp} / ${maxExperience}</div>
                            <div class="health-bar" style="width: ${experiencePercentage}%; background: orange;">
                                <div class="bar-text">${data.xp}</div>
                            </div>
                        </div>
                    </div>
                        <p>Деньги: ${data.money_formatted.gold} золота, ${data.money_formatted.silver} серебра, ${data.money_formatted.copper} меди</p>
                        <p>Общее количество чести: ${data.totalHonorPoints}</p>
                        <p>Очки арены: ${data.arenaPoints}</p>
                        <p>Всего убийств: ${data.totalKills}</p>
                        <p>Статус: ${data.online === 1 ? 'В сети' : 'Не в сети'}</p>
                        <div class="achievement">
                            <img src="/images/achievement.png" alt="Достижение" style="width: 25px; height: auto;">
                            <p style="margin-left: 5px;"> ${data.achievement_count}</p>
                        </div>
                    `;
                } else {
                    detailsDiv.innerHTML = 'Детали не найдены.';
                }
            })
            .catch(err => {
                console.error(err);
                detailsDiv.innerHTML = 'Ошибка загрузки деталей.';
            });
    }
</script>

</body>
</html>

<?php
$conn->close();
?>
