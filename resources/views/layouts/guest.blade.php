<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'FFXIV Tools') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>

<body class="font-sans antialiased bg-zinc-100 dark:bg-zinc-900">
    @inertia
</body>

</html>
