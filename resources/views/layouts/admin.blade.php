<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
    <title>{{ config('app.name', 'Hotel Management System') }}</title>
    <link href="{{ asset('adminkit/css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    @stack('styles')
</head>

<body>
    @php
        $role = Auth::user()?->role;
        $isManager = in_array($role, ['hotel_manager', 'admin'], true);
        $isCashier = in_array($role, ['cashier', 'user'], true);
        $isChef = $role === 'chef';
    @endphp
    <div class="wrapper">
        <nav id="sidebar" class="sidebar js-sidebar">
            <div class="sidebar-content js-simplebar">
                <a class="sidebar-brand" href="{{ route('dashboard') }}">
                    <span class="align-middle">{{ config('app.name', 'Hotel Management System') }}</span>
                </a>

                <ul class="sidebar-nav">
                    <li class="sidebar-header">Hotel</li>

                    @if(! $isChef)
                    <li class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('dashboard') }}">
                            <i class="align-middle" data-feather="sliders"></i> <span class="align-middle">Dashboard</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('rooms.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('rooms.index') }}">
                            <i class="align-middle" data-feather="home"></i> <span class="align-middle">Rooms</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('guests.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('guests.index') }}">
                            <i class="align-middle" data-feather="users"></i> <span class="align-middle">Guests</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('bookings.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('bookings.index') }}">
                            <i class="align-middle" data-feather="calendar"></i> <span class="align-middle">Bookings</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('restaurant-orders.*') || request()->routeIs('menu-items.*') ? 'active' : '' }}">
                        <a data-bs-target="#restaurant-menu" data-bs-toggle="collapse" class="sidebar-link collapsed">
                            <i class="align-middle" data-feather="coffee"></i> <span class="align-middle">Restaurant</span>
                        </a>
                        <ul id="restaurant-menu" class="sidebar-dropdown list-unstyled collapse {{ request()->routeIs('restaurant-orders.*') || request()->routeIs('menu-items.*') ? 'show' : '' }}" data-bs-parent="#sidebar">
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('restaurant-orders.index') }}">Orders</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('restaurant-orders.create') }}">New Order</a></li>
                            @if($isManager)
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('menu-items.index') }}">Menu Items</a></li>
                            @endif
                        </ul>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('payments.index') }}">
                            <i class="align-middle" data-feather="credit-card"></i> <span class="align-middle">Payments</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('stocks.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('stocks.index') }}">
                            <i class="align-middle" data-feather="package"></i> <span class="align-middle">Stock Management</span>
                        </a>
                    </li>
                    @endif

                    <li class="sidebar-item {{ request()->routeIs('kitchen-orders.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('kitchen-orders.index') }}">
                            <i class="align-middle" data-feather="clock"></i> <span class="align-middle">Kitchen Orders</span>
                        </a>
                    </li>

                    @if($isManager)
                    <li class="sidebar-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <a data-bs-target="#reports" data-bs-toggle="collapse" class="sidebar-link collapsed">
                            <i class="align-middle" data-feather="bar-chart-2"></i> <span class="align-middle">Reports</span>
                        </a>
                        <ul id="reports" class="sidebar-dropdown list-unstyled collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}" data-bs-parent="#sidebar">
                            <li><a class="sidebar-link" href="{{ route('reports.daily-collections') }}">Daily Collection</a></li>
                            <li><a class="sidebar-link" href="{{ route('reports.room-bookings') }}">Room Bookings</a></li>
                            <li><a class="sidebar-link" href="{{ route('reports.occupied-rooms') }}">Occupied Rooms</a></li>
                            <li><a class="sidebar-link" href="{{ route('reports.available-rooms') }}">Available Rooms</a></li>
                            <li><a class="sidebar-link" href="{{ route('reports.guests') }}">Guests</a></li>
                            <li><a class="sidebar-link" href="{{ route('reports.restaurant-sales') }}">Restaurant Sales</a></li>
                            <li><a class="sidebar-link" href="{{ route('reports.payments') }}">Payments</a></li>
                            <li><a class="sidebar-link" href="{{ route('reports.unpaid-bills') }}">Unpaid Bills</a></li>
                        </ul>
                    </li>

                    <li class="sidebar-header">Administration</li>

                    <li class="sidebar-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('users.index') }}">
                            <i class="align-middle" data-feather="shield"></i> <span class="align-middle">Users & Roles</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('roles.index') }}">
                            <i class="align-middle" data-feather="key"></i> <span class="align-middle">Access Permissions</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('settings.sms.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('settings.sms.index') }}">
                            <i class="align-middle" data-feather="message-square"></i> <span class="align-middle">SMS Settings</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('audit_trails.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('audit_trails.index') }}">
                            <i class="align-middle" data-feather="activity"></i> <span class="align-middle">Audit Trails</span>
                        </a>
                    </li>
                    @endif

                    <li class="sidebar-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('profile.edit') }}">
                            <i class="align-middle" data-feather="user"></i> <span class="align-middle">Profile</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <a class="sidebar-toggle js-sidebar-toggle"><i class="hamburger align-self-center"></i></a>
                <div class="navbar-collapse collapse">
                    <ul class="navbar-nav navbar-align">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
                                <img src="{{ Auth::user()?->avatar ? asset(Auth::user()->avatar) : asset('adminkit/img/avatars/avatar.jpg') }}" class="avatar img-fluid rounded me-1" alt="User Avatar" />
                                <span class="text-dark">{{ Auth::user()->name ?? 'Guest' }}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="align-middle me-1" data-feather="user"></i> Profile</a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">Log out</a>
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="content">
                <div class="container-fluid p-0">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif
                    @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                    @endif
                    @yield('content')
                </div>
            </main>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row text-muted">
                        <div class="col-12 text-start">
                            <p class="mb-0">Hotel Management System &copy; {{ date('Y') }} &nbsp; designed by <strong>EPORT SOLUTIONS LIMITED</strong></p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="{{ asset('adminkit/js/app.js') }}"></script>
    @stack('scripts')
</body>

</html>
