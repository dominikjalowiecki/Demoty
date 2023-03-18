<?php
session_start();

$options = array(
    'options' => array(
        'min_range' => 1
    )
);

if (
    !empty($id_image = trim($_POST["id_image"] ?? null)) &&
    !empty($rate = trim($_POST["rate"] ?? null)) &&
    filter_var((int) $id_image, FILTER_VALIDATE_INT, $options) &&
    filter_var((int) $rate, FILTER_VALIDATE_INT, $options)
) {
    require 'shared/config.php';
    $conn = mysqli_connect(CONFIG['host'], CONFIG['user'], CONFIG['password'], CONFIG['db']);

    $id_user = $_SESSION['id_user'];
    $query = "
            INSERT INTO
                rate (id_image, id_user, rate)
            SELECT
                '$id_image', '$id_user', '$rate';
        ";

    if (mysqli_query($conn, $query)) {
        if (mysqli_affected_rows($conn)) {
            http_response_code(201);
            echo json_encode([]);
        } else {
            http_response_code(400);
            echo json_encode([]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => mysqli_error($conn)]);
    }

    mysqli_close($conn);
} else {
    http_response_code(400);
    echo json_encode([]);
}
