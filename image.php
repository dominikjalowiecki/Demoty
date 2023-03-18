<?php
require 'shared/cookie_httponly.php';
session_start();
$is_logged = $_SESSION['is_logged'] ?? False;
?>
<!DOCTYPE html>
<html lang="pl_PL">

<head>
    <?php require 'shared/head.php'; ?>
    <title>Demoty! | Mem</title>
</head>

<body>
    <?php require 'shared/header.php'; ?>
    <main>
        <?php
        $options = array(
            'options' => array(
                'min_range' => 1
            )
        );
        if (
            !empty($id_image = trim($_GET["id"] ?? null)) &&
            filter_var($id_image, FILTER_VALIDATE_INT, $options)
        ) {
            require 'shared/config.php';
            $conn = mysqli_connect(CONFIG['host'], CONFIG['user'], CONFIG['password'], CONFIG['db']);

            $id_user = $_SESSION['id_user'] ?? null;

            # Fetching and printing image details
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
                                AVG(rate)
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
                        ) rates_count,
                        (
                            SELECT
                                rate
                            FROM
                                rate
                            WHERE
                                id_image = im.id_image AND
                                id_user = '$id_user'
                        ) rate
                    FROM
                        image im
                    WHERE
                        id_image = '$id_image';
                ";

            $res = mysqli_query($conn, $query);

            if (mysqli_num_rows($res) === 1) {
                $row = mysqli_fetch_assoc($res);

                $rates_avg = ceil($row['rates_avg']);
                $name = htmlspecialchars($row["name"], ENT_QUOTES, 'UTF-8');
                $created_at = substr($row['created_at'], 0, -3);

                $article = "
                        <article class='image-particular' id='{$row['id_image']}'>
                            <h3>{$name}</h3>
                            <div class='image'>
                                <img src='{$row['image_url']}'>
                            </div>
                            <div class='details'>
                                <span>Data publikacji: {$created_at}</span>
                            </div>
                        </article>
                    ";
                echo $article;
            } else {
                header('Location: index.php');
            }

            mysqli_close($conn);
        } else {
            header('Location: index.php');
        }
        ?>
        <div class="rates">
            <h3>Ocena</h3>
            <?php
            echo "<span>Liczba ocen: {$row['rates_count']}</span>";
            ?>
            <div class="ratee <?php echo $is_logged ? ($row['rate'] ? "disabled" : "") : "disabled"; ?>">
                <input type="radio" id="star5" name="rate" value="5" />
                <label for="star5">5 stars</label>
                <input type="radio" id="star4" name="rate" value="4" />
                <label for="star4">4 stars</label>
                <input type="radio" id="star3" name="rate" value="3" />
                <label for="star3">3 stars</label>
                <input type="radio" id="star2" name="rate" value="2" />
                <label for="star2">2 stars</label>
                <input type="radio" id="star1" name="rate" value="1" />
                <label for="star1">1 star</label>
            </div>
        </div>
        <div class="comments">
            <h3>Komentarze</h3>
            <?php
            echo "<span>Liczba komentarzy: {$row['comments_count']}</span>";
            ?>
            <div class="add-comment">
                <form>
                    <fieldset <?php echo $is_logged ? "" : "disabled='disabled'"; ?>>
                        <textarea maxlength="320" cols="50" rows="5" required></textarea><br>
                        <input type="submit" value="Wyślij komentarz">
                    </fieldset>
                </form>
            </div>
            <div class="inner-comments"></div>
        </div>
    </main>
    <?php require 'shared/footer.php'; ?>
    <script>
        document.title = "Demoty! | <?php echo $name; ?>";
        window.addEventListener("DOMContentLoaded", () => {
            const form_data = new FormData();
            const id_image = document.querySelector('article').id;
            const id_user = "<?php echo $_SESSION['id_user'] ?? null; ?>";

            const rate = document.querySelector('.ratee');
            let rate_avg_value = "<?php echo $row['rates_avg'] != "null" ? $row['rates_avg'] : "0"; ?>";
            rate_avg_value = Math.ceil(Number(rate_avg_value));

            // Rendering comments
            form_data.append('id_image', id_image);

            function renderComments(data) {
                let result = "";
                data.forEach((comment) => {
                    const {
                        user_avatar,
                        user_username,
                        content,
                        created_at
                    } = comment;

                    result += `
                        <div class='comment'>
                            <img class="avatar" src="${user_avatar}">
                            <h4>${user_username}</h4>
                            <p>${content}</p>
                            <small>${created_at.slice(0, -3)}</small>
                        </div>
                        <hr>
                    `;
                });
                document.querySelector(".inner-comments").innerHTML = result;
            }

            fetch('comments.php', {
                    method: 'POST',
                    body: form_data
                })
                .then(response => response.json())
                .then(renderComments)
                .catch(error => {
                    console.error('Error:', error);
                });

            // Showing average rate value
            if (rate_avg_value != 0) {
                rate.querySelector(`input[value="${rate_avg_value}"]`).checked = true;
            }

            if (id_user !== null) {
                // Handling rating system
                rate.addEventListener('click', (e) => {
                    var rate_value = e.target.value;
                    if (rate_value >= 1 && rate_value <= 5) {
                        var rate_form_data = new FormData();
                        rate_form_data.append('id_image', id_image);
                        rate_form_data.append('rate', rate_value);

                        fetch('add_rate.php', {
                                method: 'POST',
                                body: rate_form_data
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw Error(response.statusText);
                                }
                                rate.classList.add('disabled');
                                window.location.reload();
                            })
                            .catch(error => {
                                console.error('Error: ', error);
                            });
                    }
                });

                // Handling adding new comments
                const form = document.querySelector('.add-comment form');
                form.addEventListener('submit', (e) => {
                    e.preventDefault();

                    var textarea = e.currentTarget.querySelector('textarea');
                    var content = textarea.value;
                    textarea.value = "";

                    var comment_form_data = new FormData();
                    comment_form_data.append('id_image', id_image);
                    comment_form_data.append('content', content);

                    function handleErrors(response) {
                        if (!response.ok) {
                            throw Error(response.statusText);
                        }

                        fetch('comments.php', {
                                method: 'POST',
                                body: form_data
                            })
                            .then(response => response.json())
                            .then(renderComments)
                            .catch(error => {
                                console.error('Error:', error);
                            });
                    }

                    fetch('add_comment.php', {
                            method: 'POST',
                            body: comment_form_data
                        })
                        .then(handleErrors)
                        .catch(error => {
                            console.error('Error: ', error);
                        });
                });
            }
        });
    </script>
</body>

</html>