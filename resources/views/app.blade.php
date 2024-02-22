<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    @vite('resources/js/app.js')
    @vite('resources/css/app.css')
    @vite('resources/js/Pages/**/*.vue')

    @inertiaHead
</head>

<body>
    <div class="min-h-screen bg-gray-100">
        @inertia

    </div>
</body>

</html>