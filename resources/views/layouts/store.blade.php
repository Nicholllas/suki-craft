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

    <meta
        name="twitter:card"
        content="summary_large_image"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900">

    {{-- Header --}}
    <header class="border-b bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4">

            <a
                href="{{ route('home') }}"
                class="text-xl font-bold"
            >
                Suki Craft
            </a>

            <nav class="flex items-center gap-4">
                <a href="{{ route('home') }}">
                    Home
                </a>

                @auth
                    <span>
                        {{ auth()->user()->name }}
                    </span>

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf

                        <button type="submit">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}">
                        Login
                    </a>

                    <a href="{{ route('register') }}">
                        Register
                    </a>
                @endauth
            </nav>

        </div>
    </header>


    {{-- Main --}}
    <main class="mx-auto max-w-7xl px-4 py-8">
        @yield('content')
    </main>


    {{-- Footer --}}
    <footer class="border-t bg-white">
        <div class="mx-auto max-w-7xl px-4 py-6">

            <p class="text-sm text-gray-500">
                &copy; {{ date('Y') }} Suki Craft.
                All rights reserved.
            </p>

        </div>
    </footer>

</body>

</html>