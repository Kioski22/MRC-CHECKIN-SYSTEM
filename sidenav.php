<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-teal sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center" href="index">
        <div class="sidebar-brand-icon" style="width: 75px;">
            <!--<i class="fas fa-laugh-wink"></i>-->
            <img src="img/73rdNatCon.jpg" alt="Logo" class="img-fluid mr-0" width="55%">
        </div>

        <div class="sidebar-brand-text mx-0">PSME ICTO</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="index">
            <i class="fas fa-fw fa-th-large"></i>
            <span>Dashboard</span></a>
    </li>

    <?php if (hasRole('Check-In Staff') || hasRole('admin')): ?>
        <hr class="sidebar-divider">

        <div class="sidebar-heading">Check-In Staff</div>

        <ul class="nav flex-column">
            <li class="nav-item active">
                <a class="nav-link" href="checkin">
                    <i class="fas fa-fw fa-camera"></i>
                    <span>Scan</span>
                </a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="search">
                    <i class="fas fa-fw fa-search"></i>
                    <span>Search</span>
                </a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="list_checkins">
                    <i class="fas fa-fw fa-th-list"></i>
                    <span>List Data</span>
                </a>
            </li>
        </ul>
    <?php endif; ?>

    <?php if (hasRole('admin')): ?>
        <hr class="sidebar-divider">

        <div class="sidebar-heading">Walk-in</div>

        <ul class="nav flex-column">
            <li class="nav-item active">
                <a class="nav-link" href="registerone">
                    <i class="fas fa-fw fa-money-check"></i>
                    <span>Register</span>
                </a>
            </li>
        </ul>
    <?php endif; ?>

    <?php
    if (hasRole('Food Distributor') || hasRole('admin')) { ?>
        <hr class="sidebar-divider">

        <div class="sidebar-heading">Food Distributor</div>

        <ul class="nav flex-column">
            <!--Release food by QR Scan-->
            <li class="nav-item active">
                <a class="nav-link" href="release_food">
                    <i class="fas fa-fw fa-qrcode"></i>
                    <span>Scan</span>
                </a>
            </li>
            <!--Release food by Search-->
            <li class="nav-item active">
                <a class="nav-link" href="search_food_release">
                    <i class="fas fa-fw fa-search"></i>
                    <span>Search</span>
                </a>
            </li>
            <!--List of Food Distribution-->
            <li class="nav-item active">
                <a class="nav-link" href="list_food">
                    <i class="fas fa-fw fa-th-list"></i>
                    <span>List Distribution</span>
                </a>
            </li>
        </ul>
        <?php
    } ?>

    <?php
    if (hasRole('admin')) { ?>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">
            Admin
        </div>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true"
                aria-controls="collapseTwo">
                <i class="fas fa-fw fa-user"></i>
                <span>User Management</span>
            </a>
            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <!--<h6 class="collapse-header">Management</h6>-->
                    <?php
                    if (hasPermission('add_user')) { ?>
                        <a class="collapse-item" href="add_user">Add User</a>
                        <?php
                    }
                    ?>

                    <?php
                    if (hasPermission('list_users')) { ?>
                        <a class="collapse-item" href="list_users">List Users</a>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </li>
        <!--
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
                aria-expanded="true" aria-controls="collapseUtilities">
                <i class="fas fa-fw fa-users"></i>
                <span>Role Management</span>
            </a>
            <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <!--<h6 class="collapse-header">Management</h6>
                    <a class="collapse-item" href="#">Add Role</a>
                    <a class="collapse-item" href="#">List Roles</a>
                    <a class="collapse-item" href="#">Add Permission</a>
                    <a class="collapse-item" href="#">List Permissions</a>
                </div>
            </div>
        </li>
        -->

        <li class="nav-item">
            <a class="nav-link" href="test_mode_controls">
                <i class="fas fa-fw fa-flask"></i>
                <span>Food Test Controls</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="event_settings">
                <i class="fas fa-fw fa-cog"></i>
                <span>Event Settings</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="api_monitoring">
                <i class="fas fa-fw fa-heartbeat"></i>
                <span>API Monitoring</span>
            </a>
        </li>
    <?php } ?>



    <!-- Divider
            <hr class="sidebar-divider"> -->

    <!-- Heading 
            <div class="sidebar-heading">
                Addons
            </div>-->

    <!-- Nav Item - Pages Collapse Menu 
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
                    aria-expanded="true" aria-controls="collapsePages">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>Pages</span>
                </a>
                <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Login Screens:</h6>
                        <a class="collapse-item" href="login.html">Login</a>
                        <a class="collapse-item" href="register.html">Register</a>
                        <a class="collapse-item" href="forgot-password.html">Forgot Password</a>
                        <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Other Pages:</h6>
                        <a class="collapse-item" href="404.html">404 Page</a>
                        <a class="collapse-item" href="blank.html">Blank Page</a>
                    </div>
                </div>
            </li>-->

    <!-- Nav Item - Charts 
            <li class="nav-item">
                <a class="nav-link" href="charts.html">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Charts</span></a>
            </li>-->

    <!-- Nav Item - Tables 
            <li class="nav-item">
                <a class="nav-link" href="tables.html">
                    <i class="fas fa-fw fa-table"></i>
                    <span>Tables</span></a>
            </li>-->

    <!-- Divider 
            <hr class="sidebar-divider d-none d-md-block">-->

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->
