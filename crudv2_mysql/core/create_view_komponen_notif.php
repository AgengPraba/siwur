<?php
$string = "
 @session('message')
            <div x-data=\"{ show: true }\" x-init=\"setTimeout(() => show = false, 3000)\" x-show=\"show\"
                class=\"fixed top-5 right-5 z-50 px-4 py-2 rounded-md shadow-lg\"
                :class=\"{
                    'bg-green-500 text-white': '{{ session('type') }}'
                    === 'success',
                    'bg-red-500 text-white': '{{ session('type') }}'
                    === 'error',
                    'bg-blue-500 text-white': '{{ session('type') }}'
                    === 'info'
                }\"
                role=\"alert\">
                {{ session('message') }}
            </div>
        @endsession
";

$hasil_view_form = createFile($string, "../resources/views/components/notif.blade.php");
