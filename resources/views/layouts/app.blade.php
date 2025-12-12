@php
    if (!isset($app_title)) {
       $app_title='';
   }
   else {
       $app_title = " - $app_title";
   }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}{{$app_title}}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">


</head>
<body style="font-family: sans-serif;">
    @yield('content')

    <script src="{{ asset('js/app.js') }}"></script>
{{--    <script src="/js/plugin/bootbox/bootbox.min.js"></script>--}}
    <script>
        window.Laravel = {csrfToken: '{{ csrf_token() }}'};
        window.is2FAEnabled = !!'{{ config('app.two_factor_authentication_mode_enabled') }}';
        window.sessionLifeTime = {{ config('session.lifetime') }}
    </script>
    @stack('scripts')
</body>
</html>
