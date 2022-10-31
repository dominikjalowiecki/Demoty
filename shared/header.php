<header>
    <div class="inner-header">
        <a href="index.php" class="logo-link"><span class="logo">
            <i>Demoty!</i>
        </span></a>
        <nav>
            <ul>
                <li><a href="index.php">Strona główna</a></li>
                <?php
                    if($is_logged)
                    {
                ?>
                    <li><a href="add_meme.php">Dodaj mema</a></li>
                    <li><a href="profile.php">Mój profil</a></li>
                    <li><a href="logout.php">Wyloguj</a></li>
                <?php
                    } else
                    {
                ?>
                    <li><a href="login.php">Logowanie</a></li>
                    <li><a href="register.php">Rejestracja</a></li>
                <?php
                    }
                ?>
            </ul>
        </nav>
    </div>
</header>