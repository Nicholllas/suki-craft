<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - Suki Craft</title>
</head>
<body>

    <h1>Admin Dashboard</h1>

    <p>
        Welcome, {{ auth()->user()->name }}
    </p>

    <p>
        Role:
        {{ auth()->user()->getRoleNames()->implode(', ') }}
    </p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf

        <button type="submit">
            Logout
        </button>
    </form>

</body>
</html>