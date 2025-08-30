<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <img src="{{ asset('storage/INH_HR_LOGO.png') }}" alt="Project Logo" style="width: 100%; max-width: 248px; height: auto;">
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-4 px-4 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>

        {{-- ============================================================= --}}
        {{-- === JAVASCRIPT FOR SHOW/HIDE PASSWORD FUNCTIONALITY       === --}}
        {{-- ============================================================= --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const togglePasswordIcons = document.querySelectorAll('.toggle-password');

                togglePasswordIcons.forEach(icon => {
                    icon.addEventListener('click', function () {
                        const passwordInput = this.parentElement.querySelector('input');
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);

                        // Toggle the icon class
                        this.classList.toggle('fa-eye');
                        this.classList.toggle('fa-eye-slash');
                    });
                });
            });
        </script>
    </body>
</html>