<?php
$options = array(
    'options' => array(
        'min_range' => 1
    )
);
if (
    !empty($id_image = trim($_POST["id_image"] ?? null)) &&
    !empty($id_user = trim($_POST["id_user"] ?? null)) &&
    !empty($content = trim($_POST["content"] ?? null)) &&
    filter_var((int) $id_image, FILTER_VALIDATE_INT, $options) &&
    filter_var((int) $id_user, FILTER_VALIDATE_INT, $options) &&
    strlen($content) <= 320
) {
    require 'shared/config.php';
    $conn = mysqli_connect(CONFIG['host'], CONFIG['user'], CONFIG['password'], CONFIG['db']);

    $content = mysqli_real_escape_string($conn, $content);
    $query = "
            INSERT INTO
                comment
            SET
                id_image = '$id_image',
                id_user = '$id_user',
                content = '$content';
        ";

    mysqli_query($conn, $query);

    if (mysqli_affected_rows($conn)) {
        http_response_code(201);
        echo '';
    } else {
        http_response_code(400);
        echo '';
    }

    mysqli_close($conn);
} else {
    http_response_code(400);
    echo '';
}
