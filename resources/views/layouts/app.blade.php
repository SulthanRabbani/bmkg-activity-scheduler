<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'BMKG Activity Scheduler' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Vite CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">
    {{ $slot }}

    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>
