<nav class="navbar navbar-expand-lg navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item d-block d-xl-none">
            <a class="nav-link sidebartoggler " id="headerCollapse" href="javascript:void(0)">
                <i class="ti ti-menu-2"></i>
            </a>
        </li>
    </ul>
    <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
        <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
            <li class="nav-item dropdown">
                <a class="nav-link " href="javascript:void(0)" id="drop1" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <iconify-icon icon="solar:bell-linear" class="fs-6"></iconify-icon>
                    <div class="notification bg-primary rounded-circle"></div>
                </a>
                <div class="dropdown-menu dropdown-menu-animate-up" aria-labelledby="drop1">
                    <div class="message-body">
                        <a href="javascript:void(0)" class="dropdown-item">
                            Item 1
                        </a>
                        <a href="javascript:void(0)" class="dropdown-item">
                            Item 2
                        </a>
                    </div>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link " href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <img src="{{ asset('assets/images/profile/user1.jpg')}}" alt="" width="35" height="35"
                        class="rounded-circle">
                </a>
                <div class="dropdown">
                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        {{ auth()->user()->name ?? 'User' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" id="logoutBtn">Logout</a></li>
                        <li><a class="dropdown-item" href="#" id="logoutAllBtn">Logout dari Semua Perangkat</a></li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</nav>