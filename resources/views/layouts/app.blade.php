<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') | {{ env('APP_NAME') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

        <link rel="stylesheet" href={{ asset('css/app.css') }}>
        @yield('headextra')
    </head>
    <body class="antialiased">
        <div><h1>Visual Site Diff</h1></div>
        @yield('content')
    </body>
</html>
