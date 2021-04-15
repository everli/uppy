<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <title>{{ config('app.name') }}</title>
    <script>
        window.config = @json([
            'app_name' => config('app.name')
        ]);
    </script>
</head>
<body>
<div id="app">
    <router-view></router-view>
</div>
<script src="{{ mix('js/vendor.js') }}" type="application/javascript"></script>
<script src="{{ mix('js/app.js') }}" type="application/javascript"></script>
</body>
</html>
