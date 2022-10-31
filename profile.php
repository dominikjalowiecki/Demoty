<?php
    require 'shared/cookie_httponly.php';
    session_start();
    $is_logged = $_SESSION['is_logged'] ?? False;
    if(!$is_logged)
    {
        header('Location: login.php');
    }
?>
<!DOCTYPE html>
<html lang="pl_PL">
<head>
    <?php require 'shared/head.php'; ?>
    <title>Demoty! | Profil użytkownika</title>
</head>
<body>
    <?php require 'shared/header.php'; ?>
    <main>
        <h2>Profil użytkownika</h2>
        <?php
            require 'shared/config.php';
            $conn = mysqli_connect(CONFIG['host'], CONFIG['user'], CONFIG['password'], CONFIG['db']);
            
            # Getting current user details
            $id_user = $_SESSION['id_user'];

            $query = "
                SELECT
                    username, email, password, avatar, created_at
                FROM
                    user
                WHERE
                    id_user = '$id_user';
            ";

            $res = mysqli_query($conn, $query);
			$row = mysqli_fetch_assoc($res);
            
            # Handling change password form
			if(isset($_POST['submit']))
			{
				if(
					!empty($current_password = trim($_POST["current_password"] ?? null)) &&
					!empty($new_password = trim($_POST["new_password"] ?? null)) &&
					preg_match('/^(?=.*[A-Z]{1})(?=.*[0-9]{1})(?=.*[^a-zA-Z0-9]{1})\S{8,}$/', $current_password) &&
					preg_match('/^(?=.*[A-Z]{1})(?=.*[0-9]{1})(?=.*[^a-zA-Z0-9]{1})\S{8,}$/', $new_password) &&
					password_verify($current_password, $row['password'])
				)
				{
					$options = array(
						'cost' => 12,
					);
					$password = password_hash($new_password, PASSWORD_DEFAULT, $options);

					$query = "
						UPDATE
							user
						SET
							password = '$password'
						WHERE
							id_user = '$id_user';
					";

                    if(mysqli_query($conn, $query))
                    {
                        if(mysqli_affected_rows($conn))
                        {
                            $notification = '<span style="color: #0f0;">Pomyślnie zaktualizowano hasło!</span>';
                        } else
                        {
                            $notification = 'Nie udało się zaktualizować hasła!';
                        }
                    } else
                    {
                        $notification = 'Proszę spróbować później...';
                    }
				} else {
					$notification = 'Nie udało się zaktualizować hasła!';
				}
			}

            mysqli_close($conn);
        ?>
        <div class="user-details">
            <img class="avatar" src="<?php echo $row['avatar']; ?>">
            <span>
                <h3>Nazwa użytkownika</h3>
                <p><?php echo $row['username']; ?></p>
                <hr>
            </span>
            <span>
                <h3>Email</h3>
                <p><?php echo $row['email']; ?></p>
                <hr>
            </span>
            <span>
                <h3>Data dołączenia</h3>
                <p><?php echo $row['created_at']; ?></p>
            </span>
        </div>
        <div class="update-password">
            <h3>Zmień swoje hasło</h3>
			<form method="POST">
				<p>
					<label for="current-password">Aktualne hasło</label><br>
					<input type="password" id="current-password" name="current_password" pattern="(?=.*[A-Z]{1})(?=.*[0-9]{1})(?=.*[^a-zA-Z0-9]{1})\S{8,}" required>
				</p>
				<p>
					<label for="new-password">Nowe hasło</label><br>
					<input type="password" id="new-password" name="new_password" pattern="(?=.*[A-Z]{1})(?=.*[0-9]{1})(?=.*[^a-zA-Z0-9]{1})\S{8,}" required>
				</p>
				<p>
					<input type="submit" name="submit" value="Zmień hasło">
                </p><br>
                <?php echo '<span class="notification">'.($notification ?? '').'</span>';?>
			</form>
        </div>
    </main>
    <?php require 'shared/footer.php'; ?>
</body>
</html>