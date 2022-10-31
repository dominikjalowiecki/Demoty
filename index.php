<?php
    require 'shared/cookie_httponly.php';
    session_start();
    $is_logged = $_SESSION['is_logged'] ?? False;
?>
<!DOCTYPE html>
<html lang="pl_PL">
<head>
    <?php require 'shared/head.php'; ?>
    <title>Demoty! | Strona główna</title>
</head>
<body>
    <?php require 'shared/header.php'; ?>
    <main>
        <?php
            require 'shared/config.php';
            $conn = mysqli_connect(CONFIG['host'], CONFIG['user'], CONFIG['password'], CONFIG['db']);

            # Pagination
            $pagination_step = 5;

            $query = "
                SELECT
                    COUNT(1) images_count
                FROM
                    image;
            ";
            $res = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($res);
            
            $pages_count = ceil($row['images_count'] / $pagination_step);

            $page = isset($_GET['page']) ? (int)$_GET['page'] : null;
            $options = array(
                'options' => array(
                    'min_range' => 1,
                    'max_range' => $pages_count + 1
                )
            );

            if(
                is_null($page) ||
                !filter_var($page, FILTER_VALIDATE_INT, $options)
            )
            {
                $page = 1;
            }

            # Fetching and printing all available images
            $pagination_start = ($pagination_step * $page) - $pagination_step;
            $query = "
                SELECT
                    im.*,
                    (
                        SELECT
                            COUNT(1)
                        FROM
                            comment
                        WHERE
                            id_image = im.id_image
                    ) comments_count,
                    (
                        SELECT
                            ROUND(AVG(rate), 1)
                        FROM
                            rate
                        WHERE
                            id_image = im.id_image
                    ) rates_avg,
                    (
                        SELECT
                            COUNT(1)
                        FROM
                            rate
                        WHERE
                            id_image = im.id_image
                    ) rates_count
                FROM
                    image im
                ORDER BY
                    created_at DESC
                LIMIT
                    $pagination_step
                OFFSET
                    $pagination_start;
            ";
            
            $res = mysqli_query($conn, $query);

            while($el = mysqli_fetch_assoc($res))
            {
                $rates_avg = $el['rates_avg'] ?? 0;
                $created_at = substr($el['created_at'], 0, -3);

                $article = "
                    <article>
                        <h3>{$el['name']}</h3>
                        <div class='image'>
                            <a href='image.php?id={$el['id_image']}'><img src='{$el['image_url']}' loading='lazy' /></a>
                        </div>
                        <div class='details'>
                            <span>Data publikacji: {$created_at} | </span>
                            <span>Liczba komentarzy: {$el['comments_count']} | </span>
                            <span>Średnia ocen: {$rates_avg}</span>
                        </div>
                    </article>
                ";                
                echo $article;
            }

            mysqli_close($conn);
        ?>

        <div class="pagination">
            <a <?php echo $page !== 1 ? "href='?page=". ($page-1) . "'" : "class='pagination-arrow disabled'"; ?> class="pagination-arrow">&lt;</a>
            <a <?php echo $page !== 1 ? "href='?page=". 1 . "'" : "class='pagination-arrow disabled'"; ?> class="pagination-arrow">&lt;&lt;</a>
            <?php echo $page !== 1 ? "<span class='page-indicator-prev'>".($page-1)."</span>" : ""; ?>
            <span class='page-indicator'><?php echo $page; ?></span>
            <?php echo $page < $pages_count ? "<span class='page-indicator-next'>".($page+1)."</span>" : ""; ?>
            <a <?php echo $page < $pages_count ? "href='?page=". $pages_count . "'" : "class='pagination-arrow disabled'"; ?> class="pagination-arrow">&gt;&gt;</a>
            <a <?php echo $page < $pages_count ? "href='?page=". ($page+1) . "'" : "class=' pagination-arrow disabled'"; ?> class="pagination-arrow">&gt;</a>
        </div>
    </main>
    <?php require 'shared/footer.php'; ?>
</body>
</html>