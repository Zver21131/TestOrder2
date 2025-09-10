<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="lib/style.css">
</head>

<body>
    <div class="login-container">
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="login" class="form-label">Логин</label>
                <input type="text" class="form-control" id="login" name="login" required>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Пароль</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Войти</button>
            <?php
            if (isset($_GET['error']) && $_GET['error'] == 1) {
                echo '<p class="error-message">Вы ввели неверные данные, просьба проверить их или связаться со специалистом</p>';
            }
            ?>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/script.js"></script>
</body>

</html>