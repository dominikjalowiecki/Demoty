<?php
require 'shared/cookie_httponly.php';
session_start();
$is_logged = $_SESSION['is_logged'] ?? False;
if ($is_logged) {
    header('Location: index.php');
}

# Handling registration form
if (
    isset($_POST['submit']) &&
    (!empty($username = trim($_POST['username'] ?? null)) &&
        strlen($username) <= 30
    ) &&
    (!empty($email = trim($_POST['email'] ?? null)) &&
        strlen($email) <= 50 &&
        filter_var($email, FILTER_VALIDATE_EMAIL)
    ) &&
    (!empty($password = trim($_POST['password'] ?? null))
    )
) {
    if (preg_match('/^(?=.*[A-Z]{1})(?=.*[0-9]{1})(?=.*[^a-zA-Z0-9]{1})\S{8,}$/', $password)) {
        if ($password == $_POST['repassword']) {
            require 'shared/config.php';
            $conn = mysqli_connect(CONFIG['host'], CONFIG['user'], CONFIG['password'], CONFIG['db']);

            $username = mysqli_real_escape_string($conn, $username);
            $email = mysqli_real_escape_string($conn, $email);
            $query = "
                    SELECT
                        username,
                        email
                    FROM
                        user
                    WHERE
                        username = '$username' OR
                        email = '$email';
                ";

            $res = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($res);

            if ($username === $row['username']) {
                $notification = 'Podana nazwa użytkownika jest zajęta!';
            } elseif ($email === $row['email']) {
                $notification = 'Podany email jest zajęty!';
            } else {
                $options = array(
                    'cost' => 12,
                );
                $password = password_hash($password, PASSWORD_DEFAULT, $options);
                $avatar = "https://www.gravatar.com/avatar/" . md5($email) . "?d=retro";
                $query = "
                        INSERT INTO
                            user(username, email, password, avatar)
                        SELECT
                            '$username',
                            '$email',
                            '$password',
                            '$avatar';
                    ";

                if (mysqli_query($conn, $query)) {
                    header('Location: login.php?registered');
                } else {
                    $notification = 'Proszę spróbować później...';
                }
            }

            mysqli_close($conn);
        } else {
            $notification = 'Hasła nie pasują do siebie!';
        }
    } else {
        $notification = 'Hasło ma nieprawidłowy format!';
    }
}
?>
<!DOCTYPE html>
<html lang="pl_PL">

<head>
    <?php require 'shared/head.php'; ?>
    <title>Demoty! | Rejestracja</title>
</head>

<body>
    <?php require 'shared/header.php'; ?>
    <main>
        <div class="form">
            <h2>Rejestracja</h3>
                <form method="POST" autocomplete="off">
                    <p>
                        <label for="username">Nazwa użytkownika</label><br>
                        <input type="text" name="username" id="username" maxlength="30" required>
                    </p>
                    <p>
                        <label for="email">Email</label><br>
                        <input type="email" name="email" id="email" maxlength="50" required>
                    </p>
                    <p>
                        <label for="password">Hasło</label><br>
                        <input type="password" name="password" id="password" pattern="(?=.*[A-Z]{1})(?=.*[0-9]{1})(?=.*[^a-zA-Z0-9]{1})\S{8,}" required><br>
                        <small>Hasło musi posiadać co najmniej 8 znaków,
                            musi zawierać przynajmniej 1 dużą literę, 1 cyfrę
                            i 1 znak specjalny.</small>
                    </p>
                    <p>
                        <label for="repassword">Powtórz hasło</label><br>
                        <input type="password" name="repassword" id="repassword" pattern="(?=.*[A-Z]{1})(?=.*[0-9]{1})(?=.*[^a-zA-Z0-9]{1})\S{8,}" required>
                    </p>
                    <p>
                        <input type="submit" name="submit" value="Zarejestruj się">
                    </p>
                </form>
        </div>
        <?php echo '<span class="notification">' . ($notification ?? '') . '</span>'; ?>
    </main>
    <?php require 'shared/footer.php'; ?>
</body>

</html>