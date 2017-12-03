<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    @stack('meta')
    @stack('stylesheets')
</head>
<body>
@yield('body')
@stack('javascripts')
</body>
</html>
