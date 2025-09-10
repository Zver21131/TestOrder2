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

$sql = "SELECT OpenOrder.NumberOrder, OpenOrder.Phone, OpenOrder.StatusID, Status.title AS StatusTitle
        FROM OpenOrder
        JOIN Status ON OpenOrder.StatusID = Status.id";
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
                <button class="header-buttons" data-bs-toggle="modal" data-bs-target="#createOrderModal">Создать</button>
            </div>
            <button onclick="location.href='logout.php'" class="header-buttons">Выйти</button>
        </div>
    </div>
    <h2 class="mt-4 container text-center">Открытые заказы</h2>
    <div class="content-order d-flex container text-center">
        <table>
            <thead>
                <tr>
                    <th>Номер заказа<br>
                        <input type="text" id="orderNumberFilter" class="form-control" placeholder="Поиск по номеру">
                    </th>
                    <th>Телефон<br>
                        <input type="text" id="phoneFilter" class="form-control" placeholder="Поиск по телефону">
                    </th>
                    <th>Статус<br>
                        <select id="statusFilter" class="form-control">
                            <option value="">Все</option>
                            <option value="В работе">В работе</option>
                            <option value="Принят">Принят</option>
                            <option value="Изготовлен">Изготовлен</option>
                        </select>
                    </th>
                    <th>В работе</th>
                    <th>Отправить СМС</th>
                    <th>Завершить заказ</th>
                    <th>Редактировать</th>
                </tr>
            </thead>
            <tbody id="ordersTableBody">
                <?php foreach ($orders as $order) : ?>
                    <tr>
                        <td><?= $order['NumberOrder']; ?></td>
                        <td><?= $order['Phone']; ?></td>
                        <td><?= $order['StatusTitle']; ?></td>
                        <td>
                            <input type="radio" name="workStatus_<?= $order['NumberOrder']; ?>" onclick="updateRadioStatus(this)" data-order="<?= $order['NumberOrder']; ?>" value="2"> Да
                            <input type="radio" name="workStatus_<?= $order['NumberOrder']; ?>" onclick="updateRadioStatus(this)" data-order="<?= $order['NumberOrder']; ?>" value="1"> Нет
                        </td>
                        <td>
                            <button class="btn <?= $order['StatusID'] == 4 ? 'disabled' : ''; ?>" onclick="sendSms('<?= $order['NumberOrder']; ?>')">
                                <i class="fa-solid fa-envelope"></i>
                            </button>
                        </td>
                        <td>
                            <button class="btn <?= $order['StatusID'] == 4 ? '' : 'disabled'; ?>" onclick="closeOrder('<?= $order['NumberOrder']; ?>')">
                                <i class="fa-regular fa-circle-xmark"></i>
                            </button>
                        </td>
                        <td>
                            <button class="btn <?= $order['StatusID'] == 4 ? 'disabled' : ''; ?>" onclick="editOrder('<?= $order['NumberOrder']; ?>', '<?= $order['Phone']; ?>')">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Модальное окно создания заказа -->
    <div class="modal fade" id="createOrderModal" tabindex="-1" aria-labelledby="createOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createOrderModal">Создать заказ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createOrderForm">
                        <div class="mb-3">
                            <label for="NumberOrder" class="form-label">Номер заказа</label>
                            <input type="text" class="form-control" id="NumberOrder" required>
                        </div>
                        <div class="mb-3">
                            <label for="orderPhone" class="form-label">Телефон</label>
                            <input type="text" class="form-control" id="orderPhone" required placeholder="+7-999-999-99-99">
                        </div>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Модальное окно для редактирования заказа -->
    <div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editOrderModalLabel">Редактировать заказ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editOrderForm">
                        <div class="mb-3">
                            <label for="editNumberOrder" class="form-label">Номер заказа</label>
                            <input type="text" class="form-control" id="editNumberOrder" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="editOrderPhone" class="form-label">Телефон</label>
                            <input type="text" class="form-control" id="editOrderPhone" required placeholder="+7-999-999-99-99">
                        </div>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="lib/script.js"></script>
</body>

</html>