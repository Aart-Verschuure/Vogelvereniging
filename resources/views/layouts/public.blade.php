<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Vogelvereniging 't Fratertje</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-100 font-sans antialiased min-h-screen flex flex-col">

<header class="bg-[#7d848c] shadow-md w-full">
    <div class="flex items-center justify-between px-6 py-4 max-w-7xl mx-auto w-full">
        <a href="{{ url('/') }}" class="flex items-center">
            <img src="{{ asset('Images/frontpage.jpg') }}" alt="Frater Logo" class="h-16 w-auto object-contain">
        </a>
        <div class="flex space-x-4">
            <a href="{{ route('membership.apply') }}" class="bg-[#a3b8cc] text-gray-900 px-6 py-2 font-semibold hover:bg-gray-500 transition">
                Lid worden
            </a>
            <a href="{{ route('login') }}" class="bg-[#a3b8cc] text-gray-900 px-6 py-2 font-semibold hover:bg-gray-500 transition">
                Inloggen
            </a>
        </div>
    </div>
</header>

<main class="flex-grow bg-[#b0c4de] p-6 md:p-12 text-gray-900">
    <div class="max-w-3xl mx-auto">
        @yield('content')
    </div>
</main>

<footer class="bg-[#f3b05a] px-6 py-2 text-sm text-gray-800 flex justify-between">
    <p>&copy; {{ date('d-m-Y') }} - Made by Aart Verschuure</p>
    <a href="{{ route('membership.cancel') }}" class="hover:underline">Lidmaatschap opzeggen</a>
</footer>
</body>
</html>
