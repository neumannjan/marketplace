<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    @stack('meta')
    @stack('stylesheets')
</head>
<body>
@yield('body')
@stack('javascripts')
</body>
</html>
