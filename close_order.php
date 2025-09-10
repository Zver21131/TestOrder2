<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit();
}

include 'config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT CloseOrder.NumberOrder, CloseOrder.Phone, CloseOrder.StatusID, Status.title AS StatusTitle
        FROM CloseOrder
        JOIN Status ON CloseOrder.StatusID = Status.id";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Открытые заказы</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://kit.fontawesome.com/e2294165eb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="lib/style.css">
</head>

<body>
    <div class="header">
        <div class="d-flex justify-content-between">
            <div>
                <button onclick="location.href='open_order.php'" class="header-buttons">Открытые</button>
                <button onclick="location.href='close_order.php'" class="header-buttons">Завершённые</button>
            </div>
            <button onclick="location.href='logout.php'" class="header-buttons">Выйти</button>
        </div>
    </div>
    <h2 class="mt-4 container text-center">Завершённые заказы</h2>
    <div class="content-order d-flex container text-center">
        <table>
            <thead>
                <tr>
                    <th>Номер заказа</th>
                    <th>Телефон</th>
                    <th>Статус</th>
                    <th>Удалить</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order) : ?>
                    <tr>
                        <td><?= $order['NumberOrder']; ?></td>
                        <td><?= $order['Phone']; ?></td>
                        <td><?= $order['StatusTitle']; ?></td>
                        <td>
                            <button class="btn" onclick="deleteOrder('<?= $order['NumberOrder']; ?>')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="lib/script.js"></script>
</body>

</html>