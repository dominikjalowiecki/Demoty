<?php
require 'shared/cookie_httponly.php';
session_start();
$is_logged = $_SESSION['is_logged'] ?? False;
if ($is_logged) {
    header('Location: index.php');
}

# Handling login form
if (
    isset($_POST['submit']) &&
    (!empty($email = trim($_POST['email'] ?? null)) &&
        strlen($email) <= 50 &&
        filter_var($email, FILTER_VALIDATE_EMAIL)
    ) &&
    (!empty($password = trim($_POST['password'] ?? null))
    )
) {
    if (preg_match('/^(?=.*[A-Z]{1})(?=.*[0-9]{1})(?=.*[^a-zA-Z0-9]{1})\S{8,}$/', $password)) {
        require 'shared/config.php';
        $conn = mysqli_connect(CONFIG['host'], CONFIG['user'], CONFIG['password'], CONFIG['db']);

        $email = mysqli_real_escape_string($conn, $email);
        $query = "
                SELECT
                    id_user,
                    password
                FROM
                    user
                WHERE
                    email = '$email';
            ";

        $res = mysqli_query($conn, $query);
        if (mysqli_num_rows($res) !== 0) {
            $row = mysqli_fetch_assoc($res);

            if (password_verify($password, $row['password'])) {
                session_start();
                $_SESSION['is_logged'] = True;
                $_SESSION['id_user'] = $row['id_user'];

                header('Location: index.php');
            } else {
                $notification = 'Podano niepoprawne dane!';
            }
        } else {
            $notification = 'Podano niepoprawne dane!';
        }

        mysqli_close($conn);
    } else {
        $notification = 'Hasło ma nieprawidłowy format!';
    }
}
?>
<!DOCTYPE html>
<html lang="pl_PL">

<head>
    <?php require 'shared/head.php'; ?>
    <title>Demoty! | Logowanie</title>
</head>

<body>
    <?php require 'shared/header.php'; ?>
    <main>
        <div class="form">
            <h2>Logowanie</h3>
                <form method="POST" autocomplete="off">
                    <p>
                        <label for="email">Email</label><br>
                        <input type="email" name="email" id="email" maxlength="50" required>
                    </p>
                    <p>
                        <label for="password">Hasło</label><br>
                        <input type="password" name="password" id="password" pattern="(?=.*[A-Z]{1})(?=.*[0-9]{1})(?=.*[^a-zA-Z0-9]{1})\S{8,}" required><br>
                    </p>
                    <p>
                        <input type="submit" name="submit" value="Zaloguj się">
                    </p>
                </form>
        </div>
        <?php
        echo '<span class="notification">' . ($notification ?? '') . '</span>';
        echo isset($_GET['registered']) ? '<span style="color: #0f0;">Pomyślnie zarejestrowano do serwisu!</span>' : '';
        ?>
    </main>
    <?php require 'shared/footer.php'; ?>
</body>

</html>