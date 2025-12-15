<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
        <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="apple-touch-icon" sizes="180x180" href="https://laravel.com/img/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="https://laravel.com/img/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="https://laravel.com/img/favicon/favicon-16x16.png">
        <link rel="mask-icon" href="https://laravel.com/img/favicon/safari-pinned-tab.svg" color="#ff2d20">
        <link rel="shortcut icon" href="https://laravel.com/img/favicon/favicon.ico">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="min-h-screen font-sans antialiased bg-base-200">
        {{-- NAVBAR MENU HEADER --}}
        @include('components.layouts.navbar-menu-header')
        {{-- NAVBAR TANPA MENU --}}
        {{-- @include('components.layouts.navbar-tanpa-menu') --}}
        {{-- MAIN CONTENT --}}
        <x-main with-nav full-width>
            {{-- @include('components.layouts.sidebar') --}}
            {{-- The main content goes here --}}
            <x-slot:content>
                {{ $slot }}
            </x-slot:content>
        </x-main>

        {{-- TOAST area --}}
        <x-toast />

        <x-modal-help />

        {{-- Chatbot Component --}}
        @livewire('chatbot-component')

        <script>
            function focusShortcutSearchField() {
                const direct = document.querySelector('[data-shortcut-search]');
                if (direct && !direct.disabled && !direct.readOnly) {
                    if (typeof direct.focus === 'function') {
                        direct.focus({
                            preventScroll: false
                        });
                    }
                    if (typeof direct.select === 'function') {
                        direct.select();
                    }
                    return true;
                }

                const candidates = Array.from(document.querySelectorAll('input, textarea')).filter(function(el) {
                    if (!el || el.disabled || el.readOnly) {
                        return false;
                    }
                    const type = (el.type || '').toLowerCase();
                    if (type === 'hidden' || type === 'radio' || type === 'checkbox' || type === 'button' || type ===
                        'submit') {
                        return false;
                    }
                    if (type === 'search') {
                        return true;
                    }
                    const name = (el.name || '').toLowerCase();
                    const placeholder = (el.getAttribute('placeholder') || '').toLowerCase();
                    return name === 'search' || placeholder.includes('cari') || placeholder.includes('search');
                });

                if (candidates.length > 0) {
                    const first = candidates[0];
                    if (typeof first.focus === 'function') {
                        first.focus({
                            preventScroll: false
                        });
                    }
                    if (typeof first.select === 'function') {
                        first.select();
                    }
                    return true;
                }

                return false;
            }

            document.addEventListener('DOMContentLoaded', function() {
                document.addEventListener('keydown', function(event) {
                    if (event.defaultPrevented) {
                        return;
                    }

                    if (event.ctrlKey && event.key === '.') {
                        event.preventDefault();
                        window.dispatchEvent(new CustomEvent('show-shortcut-help'));
                        return;
                    }

                    if (!event.ctrlKey || event.metaKey || event.altKey || event.shiftKey) {
                        return;
                    }

                    const key = event.key ? event.key.toLowerCase() : '';

                    if (!['d', 'h', 'k', 'b'].includes(key)) {
                        return;
                    }

                    if (key === 'k') {
                        event.preventDefault();
                        focusShortcutSearchField();
                        return;
                    }

                    if (key === 'd') {
                        event.preventDefault();
                        window.location.href = '{{ route('penjualan.create') }}';
                        return;
                    }

                    if (key === 'h') {
                        event.preventDefault();

                        const today = new Date().toISOString().split('T')[0];
                        const filters = {
                            filterDateFrom: today,
                            filterDateTo: today
                        };
                        const params = new URLSearchParams(filters);
                        window.location.href = `{{ route(name: 'penjualan.index') }}?${params.toString()}`;
                        return;
                    }

                    if (key === 'b') {
                        event.preventDefault();

                        // Buat tanggal hari ini (format YYYY-MM-DD)
                        const today = new Date().toISOString().split('T')[0];

                        // Definisikan filter yang ingin dikirim
                        const filters = {
                            filterDateFrom: today,
                            filterDateTo: today,
                            filterStatus: 'belum_bayar'
                        };

                        // Ubah jadi query string
                        const params = new URLSearchParams(filters);

                        window.location.href = `{{ route('penjualan.index') }}?${params.toString()}`;
                    }

                });
            });
        </script>

</body>

</html>
