<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <title>JobPosting Module - {{ config('app.name', 'Laravel') }}</title>

        <meta name="description" content="{{ $description ?? '' }}">
        <meta name="keywords" content="{{ $keywords ?? '' }}">
        <meta name="author" content="{{ $author ?? '' }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            'blue-500': '#3B82F6',
                            'blue-600': '#2563EB',
                            'blue-700': '#1D4ED8',
                            'blue-50': '#EFF6FF',
                            'blue-100': '#DBEAFE',
                        }
                    }
                }
            }
        </script>

        {{-- Vite CSS --}}
        {{-- {{ module_vite('build-jobposting', 'resources/assets/sass/app.scss') }} --}}
    </head>

    <body>
        {{ $slot }}

        {{-- Vite JS --}}
        {{-- {{ module_vite('build-jobposting', 'resources/assets/js/app.js') }} --}}
    </body>
</html>