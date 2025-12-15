<?php
$string = "<!DOCTYPE html>
<html lang=\"{{ str_replace('_', '-', app()->getLocale()) }}\">

<head>
    <meta charset=\"utf-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover\">
    <title>{{ isset(\$title) ? \$title . ' - ' . config('app.name') : config('app.name') }}</title>
    <meta name=\"csrf-token\" content=\"{{ csrf_token() }}\">
    <link rel=\"apple-touch-icon\" sizes=\"180x180\" href=\"https://laravel.com/img/favicon/apple-touch-icon.png\">
    <link rel=\"icon\" type=\"image/png\" sizes=\"32x32\" href=\"https://laravel.com/img/favicon/favicon-32x32.png\">
    <link rel=\"icon\" type=\"image/png\" sizes=\"16x16\" href=\"https://laravel.com/img/favicon/favicon-16x16.png\">
    <link rel=\"mask-icon\" href=\"https://laravel.com/img/favicon/safari-pinned-tab.svg\" color=\"#ff2d20\">
    <link rel=\"shortcut icon\" href=\"https://laravel.com/img/favicon/favicon.ico\">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class=\"min-h-screen font-sans antialiased bg-base-200\">
    <x-main>
        {{-- The main content goes here --}}
        <x-slot:content>
            {{ \$slot }}
        </x-slot:content>
    </x-main>

    {{-- TOAST area --}}
    <x-toast />
</body>

</html>
";

$hasil_view_form = createFile($string, "../resources/views/components/layouts/guest.blade.php");
