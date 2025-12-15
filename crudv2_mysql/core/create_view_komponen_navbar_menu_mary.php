<?php
$string = " 
<div x-data=\"{ mobileMenuOpen: false }\">
     <div class=\"navbar bg-base-100 shadow-sm border-b border-base-300 sticky top-0 z-30 pr-10 pl-10\">
         {{-- Brand/Logo dan Menu --}}
         <div class=\"navbar-start\">
             <div class=\"flex items-center space-x-4\">
                 {{-- Logo --}}
                 <div class=\"lg:hidden\">
                     {{-- Mobile Menu Toggle --}}
                     <button @click=\"mobileMenuOpen = !mobileMenuOpen\"
                         class=\"btn btn-ghost btn-square h-10 w-10 flex items-center justify-center\"
                         :class=\"{ 'btn-active': mobileMenuOpen }\">
                         <x-icon name=\"o-bars-3\" class=\"w-6 h-6\" x-show=\"!mobileMenuOpen\" />
                         <x-icon name=\"o-x-mark\" class=\"w-6 h-6\" x-show=\"mobileMenuOpen\" />
                     </button>
                 </div>
                 <div class=\"flex items-center\">
                     <x-icon name=\"o-home\" class=\"w-8 h-8 text-primary mr-2\" />
                     <span class=\"text-xl font-bold text-base-content\">{{ config('app.name', 'App') }}</span>
                 </div>

                 {{-- Desktop Menu --}}
                 <ul class=\"hidden lg:flex menu-horizontal px-1 space-x-2\">
                     <li
                         class=\"{{ Request::routeIs('home') ? 'bg-gray-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}\">
                         <a href=\"{{ route('home') }}\" wire:navigate
                             class=\"btn btn-ghost\">
                             <x-icon name=\"o-squares-2x2\" class=\"w-4 h-4\" />
                             Home
                         </a>
                     </li>
                    

                     {{-- Contoh Dropdown --}}
                     <li>
                         <details class=\"dropdown\">
                             <summary class=\"btn btn-ghost\">
                                 <x-icon name=\"o-cube\" class=\"w-4 h-4\" />
                                 Contoh Dropdown
                                 <x-icon name=\"o-chevron-down\" class=\"w-4 h-4\" />
                             </summary>
                             <ul class=\"dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow\">
                                 <li>
                                     <a href=\"/products\">
                                         <x-icon name=\"o-list-bullet\" class=\"w-4 h-4\" />
                                         All Products
                                     </a>
                                 </li>
                                 <li>
                                     <a href=\"/categories\">
                                         <x-icon name=\"o-tag\" class=\"w-4 h-4\" />
                                         Categories
                                     </a>
                                 </li>
                              
                             </ul>
                         </details>
                     </li>


                 </ul>
             </div>
         </div>

         {{-- Right side actions --}}
         <div class=\"navbar-end\">
             {{-- Desktop Actions --}}
             <div class=\"hidden lg:flex items-center space-x-2\">
                 <x-theme-toggle />
                 {{-- User Dropdown --}}
                 @auth
                     @php
                         \$user = Auth::user();
                         if (\$user) {
                             \$names = explode(' ', \$user->name);
                             \$initials = '';
                             foreach (\$names as \$name) {
                                 \$initials .= strtoupper(substr(\$name, 0, 1));
                             }
                             \$initials; // Menampilkan contoh: \"M.K\"
                         } else {
                             \$initials = ''; // Menampilkan contoh: \"M.K\"
                         }
                     @endphp
                     <div class=\"dropdown dropdown-end\">
                         <div tabindex=\"0\" role=\"button\" class=\"btn btn-ghost btn-circle\">
                             <x-avatar placeholder=\"{{ \$initials }}\" class=\"!w-8 !h-8\">
                             </x-avatar>
                         </div>
                         <ul tabindex=\"0\" class=\"dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow\">
                             <li>
                                 <a href=\"/profile\">
                                     <x-icon name=\"o-user\" class=\"w-4 h-4\" />
                                     Profile
                                 </a>
                             </li>
                             
                             <li>
                                 <x-menu-separator />
                             </li>
                             <li>
                                 <form method=\"POST\" action=\"{{ route('logout') }}\" class=\"inline\">
                                     @csrf
                                     <button type=\"submit\"
                                         class=\"text-red-600 hover:text-red-700 transition-colors\">
                                         <x-icon name=\"o-arrow-left-on-rectangle\" class=\"w-5 h-5\" />
                                         <span>Logout</span>
                                     </button>
                                 </form>
                             </li>
                         </ul>
                     </div>
                 @else
                     <x-button label=\"Login\" link=\"/login\" class=\"btn btn-ghost btn-sm\" />
                 @endauth
             </div>

             {{-- Mobile Menu Toggle --}}
             <div class=\"lg:hidden flex items-center space-x-2\">
                 {{-- Theme Toggle --}}
                 <x-theme-toggle />
                 <div class=\"btn btn-ghost btn-square p-0\">

                     <div class=\"dropdown dropdown-end\">
                         <div tabindex=\"0\" role=\"button\" class=\"btn btn-ghost btn-circle\">
                             <x-avatar placeholder=\"{{ \$initials }}\" class=\"!w-8 !h-8\">
                             </x-avatar>
                         </div>
                         <ul tabindex=\"0\" class=\"dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow\">
                             <li>
                                 <a href=\"/profile\">
                                     <x-icon name=\"o-user\" class=\"w-4 h-4\" />
                                     Profile
                                 </a>
                             </li>
                             <li>
                                 <a href=\"/settings\">
                                     <x-icon name=\"o-cog-6-tooth\" class=\"w-4 h-4\" />
                                     Settings
                                 </a>
                             </li>
                             <li>
                                 <hr class=\"my-1\">
                             </li>
                             <li>
                                 <form method=\"POST\" action=\"{{ route('logout') }}\" class=\"inline\">
                                     @csrf
                                     <button type=\"submit\"
                                         class=\"flex items-center space-x-2 text-red-600 hover:text-red-700 px-4 py-2 rounded-lg transition-colors\">
                                         <x-icon name=\"o-arrow-left-on-rectangle\" class=\"w-5 h-5\" />
                                         <span>Logout</span>
                                     </button>
                                 </form>
                             </li>
                         </ul>
                     </div>
                 </div>


             </div>

         </div>
     </div>


     {{-- Mobile Menu Overlay --}}
     <div x-show=\"mobileMenuOpen\" x-transition:enter=\"transition ease-out duration-200\"
         x-transition:enter-start=\"opacity-0\" x-transition:enter-end=\"opacity-100\"
         x-transition:leave=\"transition ease-in duration-150\" x-transition:leave-start=\"opacity-100\"
         x-transition:leave-end=\"opacity-0\" class=\"fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden\"
         @click=\"mobileMenuOpen = false\">
     </div>

     {{-- Mobile Menu Sidebar --}}
     <div x-show=\"mobileMenuOpen\" x-transition:enter=\"transition ease-out duration-300\"
         x-transition:enter-start=\"-translate-x-full\" x-transition:enter-end=\"translate-x-0\"
         x-transition:leave=\"transition ease-in duration-300\" x-transition:leave-start=\"translate-x-0\"
         x-transition:leave-end=\"-translate-x-full\"
         class=\"fixed top-0 left-0 w-80 h-full bg-base-100 shadow-xl z-50 lg:hidden overflow-y-auto\">

         {{-- Mobile Menu Header --}}
         <div class=\"p-4 border-b border-base-300\">
             <div class=\"flex items-center justify-between\">
                 <div class=\"flex items-center\">
                     <x-icon name=\"o-home\" class=\"w-8 h-8 text-primary mr-2\" />
                     <span class=\"text-xl font-bold\">{{ config('app.name', 'App') }}</span>
                 </div>
                 <x-button @click=\"mobileMenuOpen = false\" class=\"btn-ghost btn-square btn-sm\">
                     <x-icon name=\"o-x-mark\" class=\"w-5 h-5\" />
                 </x-button>
             </div>
         </div>

         {{-- User Info (Mobile) --}}
         @auth
             <div class=\"p-4 border-b border-base-300\">
                 <x-list-item :item=\"auth()->user()\" value=\"name\" sub-value=\"email\" no-separator no-hover>
                     <x-slot:avatar>
                         <x-avatar placeholder=\"{{ \$initials }}\" class=\"w-6 h-6\">
                         </x-avatar>
                     </x-slot:avatar>
                 </x-list-item>
             </div>
         @endauth

         {{-- Mobile Menu Items --}}
         <div class=\"p-4\">
             <x-menu activate-by-route>
                 <x-menu-item title=\"Home\" icon=\"o-squares-2x2\" link=\"{{ route('home') }}\"
                     @click=\"mobileMenuOpen = false\" />
                

                 <x-menu-separator />

                 {{-- Contoh Dropdown --}}
                 <x-menu-sub title=\"Products\" icon=\"o-cube\">
                     <x-menu-item title=\"Contoh Dropdown\" icon=\"o-list-bullet\" link=\"/products\"
                         @click=\"mobileMenuOpen = false\" />
                     <x-menu-item title=\"Categories\" icon=\"o-tag\" link=\"/categories\"
                         @click=\"mobileMenuOpen = false\" />
                     <x-menu-item title=\"Inventory\" icon=\"o-archive-box\" link=\"/inventory\"
                         @click=\"mobileMenuOpen = false\" />
                 </x-menu-sub>
             </x-menu>
         </div>
     </div>
 </div>
";

$hasil_view_form = createFile($string, "../resources/views/components/layouts/navbar-menu-header.blade.php");
