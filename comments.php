<?php
$out = [];

$options = array(
    'options' => array(
        'min_range' => 1
    )
);
if (
    !empty($id_image = trim($_POST["id_image"] ?? null)) &&
    filter_var((int) $id_image, FILTER_VALIDATE_INT, $options)
) {
    require 'shared/config.php';
    $conn = mysqli_connect(CONFIG['host'], CONFIG['user'], CONFIG['password'], CONFIG['db']);

    $query = "
            SELECT
                us.avatar user_avatar,
                us.username user_username,
                co.content,
                co.created_at
            FROM
                comment co
            JOIN
                user us
            USING
                (id_user)
            WHERE
                id_image = $id_image
            ORDER BY
                created_at DESC;
        ";
    $res = mysqli_query($conn, $query);
    $row = [];

    while ($el = mysqli_fetch_assoc($res)) {
        $el["user_username"] = htmlspecialchars($el["user_username"], ENT_QUOTES, 'UTF-8');
        $el["content"] = htmlspecialchars($el["content"], ENT_QUOTES, 'UTF-8');
        $row[] = $el;
    }

    $out = $row;

    mysqli_close($conn);
}

echo json_encode($out);
