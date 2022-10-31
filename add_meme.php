<?php
require 'shared/cookie_httponly.php';
session_start();
$is_logged = $_SESSION['is_logged'] ?? False;
if (!$is_logged) {
    header('Location: login.php');
}

# Handling add meme form
if (isset($_POST['submit'])) {
    $dir_size = 0;
    $max_dir_size = 536870912; // 512 MB
    $max_image_size = 2097152; // 2 MB
    $upload_dir = __DIR__ . "/uploads";

    foreach (glob($upload_dir . "/*", GLOB_NOSORT) as $each) {
        $dir_size += is_file($each) ? filesize($each) : 0;
    }

    if ($dir_size <= $max_dir_size - $max_image_size) {
        require 'shared/config.php';

        if (
            !empty($image_title = trim($_POST['image-title'] ?? null)) &&
            isset($_FILES['image']) &&
            is_uploaded_file(($file = $_FILES['image']['tmp_name']))
        ) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);

            if (
                (isset($_FILES['image']['error']) ||
                    !is_array($_FILES['image']['error'])
                ) &&
                $_FILES['image']['error'] === UPLOAD_ERR_OK &&
                $_FILES['image']['size'] <= $max_image_size &&
                false !== ($ext = array_search(
                    $finfo->file($_FILES['image']['tmp_name']),
                    array(
                        'jpg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                    ),
                    true
                ))
            ) {
                $fhash = sha1_file($file);
                $path = $upload_dir . '/' . $fhash;

                if (!file_exists($path . '.jpg')) {
                    if (!move_uploaded_file($file, $path)) {
                        $notification = 'Wystąpił błąd! Spróbuj ponownie później...';
                    }

                    # Adding "Demoty!" watermark to image
                    $watermark = imagecreatefrompng('assets/images/watermark.png');
                    $image = imagecreatefromstring(file_get_contents($path));

                    $margine_right = 10;
                    $margine_bottom = 10;
                    $size_x = imagesx($watermark);
                    $size_y = imagesy($watermark);

                    imagecopy($image, $watermark, imagesx($image) - $size_x - $margine_right, imagesy($image) - $size_y - $margine_bottom, 0, 0, $size_x, $size_y);

                    unlink($path);
                    $path = $path . '.jpg';
                    imagejpeg($image, $path);

                    imagedestroy($watermark);
                    imagedestroy($image);
                }

                $image_url = 'uploads/' . $fhash . '.jpg';

                $conn = mysqli_connect(CONFIG['host'], CONFIG['user'], CONFIG['password'], CONFIG['db']);

                $id_user = $_SESSION['id_user'];

                $image_title = mysqli_real_escape_string($conn, $image_title);
                $query = "
                        INSERT INTO
                            image(name, image_url, id_user)
                        VALUES
                            ('$image_title', '$image_url', $id_user);
                    ";

                if (mysqli_query($conn, $query)) {
                    if (mysqli_affected_rows($conn)) {
                        mysqli_close($conn);
                        header('Location: index.php');
                        exit();
                    } else {
                        $notification = 'Wystąpił błąd podczas dodawania mema! Spróbuj ponownie później...';
                    }
                } else {
                    $notification = 'Proszę spróbować później...';
                }

                mysqli_close($conn);
            } else {
                $notification = 'Plik nie spełnia wymagań!';
            }
        } else {
            $notification = 'Nie udało się dodać mema!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl_PL">

<head>
    <?php require 'shared/head.php'; ?>
    <title>Demoty! | Dodaj mema</title>
</head>

<body>
    <?php require 'shared/header.php'; ?>
    <main>
        <h2>Dodaj mema</h2>
        <div class="update-password">
            <h3>Dodaj mema</h3>
            <form method="POST" enctype="multipart/form-data">
                <p>
                    <label for="image-title">Tytuł mema</label><br />
                    <input type="text" maxlength="50" size="30" id="image-title" name="image-title" required>
                </p>
                <p>
                    <label for="image">Obrazek</label><br />
                    <input type="file" id="image" name="image" accept="image/png, image/jpeg, image/gif" required><br />
                    <small>Rozmiar: do 2MB | Dozwolone formaty: png, jpeg, gif</small>
                </p>
                <p>
                    <input type="submit" name="submit" value="Dodaj mema">
                </p><br>
                <?php echo '<span class="notification">' . ($notification ?? '') . '</span>'; ?>
            </form>
        </div>
    </main>
    <?php require 'shared/footer.php'; ?>
</body>

</html>