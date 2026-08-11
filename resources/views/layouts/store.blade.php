<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Suki Craft')
    </title>

    <meta
        name="description"
        content="@yield(
            'description',
            'Suki Craft - Flower & Gift Shop'
        )"
    >

    <link
        rel="canonical"
        href="{{ url()->current() }}"
    >

    {{-- Open Graph --}}
    <meta
        property="og:title"
        content="@yield('title', 'Suki Craft')"
    >

    <meta
        property="og:description"
        content="@yield(
            'description',
            'Suki Craft - Flower & Gift Shop'
        )"
    >

    <meta
        property="og:url"
        content="{{ url()->current() }}"
    >

    <meta
        property="og:type"
        content="@yield('og_type', 'website')"
    >

    <meta
        property="og:site_name"
        content="Suki Craft"
    >

    {{-- Twitter --}}
    <meta
        name="twitter:card"
        content="summary_large_image"
    >

    <meta
        name="twitter:title"
        content="@yield('title', 'Suki Craft')"
    >

    <meta
        name="twitter:description"
        content="@yield(
            'description',
            'Suki Craft - Flower & Gift Shop'
        )"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    <header>
        <h2>Suki Craft</h2>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <p>
            &copy; {{ date('Y') }} Suki Craft
        </p>
    </footer>

</body>
</html>