<div class="sidebar">
    <div class="sidebar_header">
        <div class="title">
            <span>SGDRH</span>
        </div>
        <div class="items">
            <?php

            $currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

            function generateSidebarItem($page, $icon, $title)
            {
                global $currentPage;

                $isActive = $currentPage == $page ? 'active' : '';

                echo "<a href=\"admin_home.php?page=$page\" class=\"sidebar_item $isActive\" title=\"$title\">
                        <i class=\"fa-solid $icon\"></i>
                    </a>";
            }

            generateSidebarItem('dashboard', 'fa-home', 'Inicio');
            if ($_SESSION['user_role'] != 3) {
                generateSidebarItem('manage_users', 'fa-users', 'Administrar empleados');
            }
            generateSidebarItem('my_profile', 'fa-user-edit', 'Mi perfil');
            /*
            generateSidebarItem('commissions', 'fas fa-money-bill', 'Comisiónes');
            generateSidebarItem('TimeByTime', 'fas fa-hourglass-half', 'timebytime');
            generateSidebarItem('licencias', 'fa-solid fa-id-card', 'licencias');
            */
            
            if ($_SESSION['user_role'] == 1) {
                generateSidebarItem('roles', 'fa-solid fa-user-tag', 'Roles');
                generateSidebarItem('absences', 'fa-user-md', 'Incapacidades');
            }


            ?>
        </div>
    </div>
    <div class="sidebar_footer">
        <a class="sidebar_item_logout" id="logout" title="Cerrar sesión" href="logout.php">
            <i class="fa-solid fa-door-open"></i>
        </a>
    </div>

</div>