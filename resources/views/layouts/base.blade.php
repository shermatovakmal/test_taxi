<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <link href="/styles/css/bootstrap.min.css" rel="stylesheet">
    <script src="/styles/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<header class="border-bottom navbar navbar-collapse" >
    <div class="container-fluid" style="color: #0d6efd">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start" >
            <a href="/" class="navbar-brand d-flex align-items-center mb-2 mb-lg-0 ">
                Logo
            </a>

            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                <li><a href="/" class="navbar-item nav-link">Главная</a></li>
                <li><a href="{{ route('orders.list') }}" class="navbar-item nav-link">В ожидании</a></li>
                <li><a href="{{ route('orders.ongoing') }}" class="navbar-item nav-link" >В пути</a></li>
                <li><a href="{{ route('orders.delivered') }}" class="navbar-item nav-link">Завершен</a></li>
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
