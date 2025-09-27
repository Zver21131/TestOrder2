<?php
header('Content-Type: application/json');

include 'config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$action = $_POST['action'];

switch ($action) {
    case 'create_order':
        $number = $_POST['number'];
        $phone = $_POST['phone'];

        $stmt = $conn->prepare("INSERT INTO OpenOrder (NumberOrder, Phone, StatusID) VALUES (?, ?, 1)");
        $stmt->bind_param("ss", $number, $phone);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Заказ создан']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка создания заказа']);
        }

        $stmt->close();
        break;

    case 'edit_order':
        $number = $_POST['number'];
        $phone = $_POST['phone'];

        $stmt = $conn->prepare("UPDATE OpenOrder SET Phone = ? WHERE NumberOrder = ?");
        $stmt->bind_param("ss", $phone, $number);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Заказ обновлен']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка обновления заказа']);
        }

        $stmt->close();
        break;

    case 'update_status':
        $number = $_POST['number'];
        $status = $_POST['status'];

        $stmt = $conn->prepare("UPDATE OpenOrder SET StatusID = ? WHERE NumberOrder = ?");
        $stmt->bind_param("is", $status, $number);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Статус обновлён']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка обновления статуса']);
        }

        $stmt->close();
        break;

    case 'send_sms':

        $number = $_POST['number'];

        $stmt = $conn->prepare("SELECT Phone FROM OpenOrder WHERE NumberOrder = ?");
        $stmt->bind_param("s", $number);
        $stmt->execute();
        $stmt->bind_result($phone);
        $stmt->fetch();
        $stmt->close();

        if (!$phone) {
            echo json_encode(['success' => false, 'message' => 'Телефон не найден']);
            break;
        }

        $apiKey = 'C8310W8M57826KQ8G904771A984TDKOO622BGP611970SEK9276AYT565GNC1B56';
        $message = "Ваш заказ готов и ожидает вас!";
        $params = [
            'send'   => $message,
            'to'     => preg_replace('/\D+/', '', $phone),
            'apikey' => $apiKey,
            'format' => 'json',
            'sender' => 'MyShop'
        ];

        $ch = curl_init('https://smspilot.ru/api.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $result = curl_exec($ch);
        curl_close($ch);

        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Ошибка соединения с SMS сервисом']);
            break;
        }


        //----
        $resp = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                'success' => false,
                'message' => 'Ошибка разбора JSON от SMS Pilot: ' . json_last_error_msg()
            ]);
            break;
        }

        if (!empty($resp['error']) && !empty($resp['error']['description_ru'])) {
            $code = var_dump($resp['error']['description_ru']);

            echo json_encode([
                'success' => false,
                'message' => 'Сервис вернул ошибку: ' . $code
            ]);
            break;
        }
        //----



        $stmt = $conn->prepare("UPDATE OpenOrder SET StatusID = 4 WHERE NumberOrder = ?");
        $stmt->bind_param("s", $number);
        $stmt->execute();
        $stmt->close();


        echo json_encode(['success' => true, 'message' => 'СМС отправлено', 'api_response' => $resp]);
        break;

    case 'close_order':
        $number = $_POST['number'];

        $stmt = $conn->prepare("INSERT INTO CloseOrder (NumberOrder, Phone, StatusID) SELECT NumberOrder, Phone, 5 FROM OpenOrder WHERE NumberOrder = ?");
        $stmt->bind_param("s", $number);

        if ($stmt->execute()) {
            $stmt = $conn->prepare("DELETE FROM OpenOrder WHERE NumberOrder = ?");
            $stmt->bind_param("s", $number);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Заказ закрыт']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка закрытия заказа']);
        }

        $stmt->close();
        break;

    case 'delete_order':
        $number = $_POST['number'];

        $stmt = $conn->prepare("DELETE FROM CloseOrder WHERE NumberOrder = ?");
        $stmt->bind_param("s", $number);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Заказ удалён']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка удаления заказа']);
        }

        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
        break;
}

$conn->close();
