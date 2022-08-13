<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <link href="/styles/css/bootstrap.min.css" rel="stylesheet">
    <script src="/styles/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<header class="p-3 mb-3 border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
            <a href="/" class="d-flex align-items-center mb-2 mb-lg-0 text-dark text-decoration-none">
                Logo
            </a>

            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                <li><a href="{{ route('orders.list') }}" class="navbar-item nav-link px-2 link-dark">Главная</a></li>
            </ul>
        </div>
    </div>
</header>

<div class="b-example-divider"></div>
<div class="container">
    <h1>@yield('header')</h1>
    @yield('main')
</div>
</body>
</html>
