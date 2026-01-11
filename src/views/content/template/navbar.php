<?php

$roles_nombres = [
    1 => "Administrador",
    2 => "Director UA",
    4 => "Jefe de Area",
    5 => "Lider Sindical"
];


?>

<nav>
    <div class="user">
        <?php if ($_SESSION['user_role'] != 3) : ?>
            <div class="role">
                <span><?= $roles_nombres[$_SESSION['user_role']]; ?></span>
            </div>
        <?php endif; ?>
        <span><?= $_SESSION['user_name']; ?></span>
        <?php if ($_SESSION['user_genre'] === 'H') {
            echo '<img class="avatar" src="assets/images/hombre.png">';
        } else {
            echo '<img class="avatar" src="assets/images/mujer.png">';
        } ?>
    </div>
</nav>