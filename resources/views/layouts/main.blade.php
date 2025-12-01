<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Your Application')</title>

    <!-- Tailwind CSS and Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Livewire Styles -->
    @livewireStyles

    <!-- Additional Styles -->
    @stack('styles')
</head>
<body class="bg-gray-200 flex flex-col min-h-screen">

    <!-- Header -->
    <header>
        <!-- Your header content -->
    </header>

    <!-- Main Content -->
    <main class="flex flex-1 container mx-auto mt-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <!-- Your footer content -->
    </footer>

    <!-- Livewire Scripts -->
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.2/dist/cdn.min.js" defer></script>

    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>
