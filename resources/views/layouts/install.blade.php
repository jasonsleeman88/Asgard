<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
<div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
    <flux:container class="space-y-6">
        <a href="{{ route('install.index') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
            <flux:icon.wrench-screwdriver class="[:where(&)]:size-30" />
            <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
        </a>
        {{ $slot }}
    </flux:container>
</div>

@fluxScripts
</body>
</html>
