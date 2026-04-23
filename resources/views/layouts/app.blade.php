<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="params" content="{{ json_encode($params) }}">
    @php( $pageInt = explode(".", $page))
    @php($css = $pageInt[sizeof($pageInt) - 1])
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/'.$css.'.css'])

    <link rel="icon" href="{{ asset('assets/icon.png')}}">
    <title>{{ $title }}</title>
</head>
<body>
    @include('components.navbar')

    @include($page, $params)

    @include('components.footer')
</body>
</html>
