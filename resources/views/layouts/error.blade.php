<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Terjadi Kesalahan')
        - Suki Craft
    </title>

    <meta
        name="robots"
        content="noindex, nofollow"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    <main>
        @yield('content')
    </main>

</body>
</html>