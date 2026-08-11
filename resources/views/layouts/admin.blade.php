<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Admin Dashboard')
        - Suki Craft
    </title>

    <meta
        name="robots"
        content="noindex, nofollow"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100 text-gray-900">

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside class="hidden w-64 border-r bg-white md:block">

            <div class="border-b px-6 py-5">

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="text-xl font-bold"
                >
                    Suki Craft
                </a>

                <p class="mt-1 text-sm text-gray-500">
                    Admin Panel
                </p>

            </div>


            <nav class="p-4">

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="block rounded-lg px-4 py-3 text-sm font-medium hover:bg-gray-100"
                >
                    Dashboard
                </a>

            </nav>

        </aside>


        {{-- Main Area --}}
        <div class="flex min-w-0 flex-1 flex-col">

            {{-- Navbar --}}
            <header class="border-b bg-white">

                <div class="flex items-center justify-between px-4 py-4 sm:px-6">

                    <div>
                        <h1 class="text-lg font-semibold">
                            @yield('page-title', 'Dashboard')
                        </h1>
                    </div>

                    <div class="flex items-center gap-4">

                        <div class="text-right">

                            <p class="text-sm font-medium">
                                {{ auth()->user()->name }}
                            </p>

                            <p class="text-xs text-gray-500">
                                {{ auth()->user()->getRoleNames()->implode(', ') }}
                            </p>

                        </div>

                        <form
                            method="POST"
                            action="{{ route('logout') }}"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                Logout
                            </button>

                        </form>

                    </div>

                </div>

            </header>


            {{-- Content --}}
            <main class="flex-1 px-4 py-6 sm:px-6">

                @yield('content')

            </main>

        </div>

    </div>

</body>

</html>