<?php
$string = "
@section('title')
    Halaman Login
@endsection

<div class=\"container mt-5 mb-5\">
    <div class=\"row\">
        <div class=\"col-md-12\">
            <div class=\"card border-0 shadow-sm rounded\">
                <div class=\"card-body\">
                    <h2>Halaman Login</h2>
                   
                    <form wire:submit.prevent=\"login\">
                        <div class=\"form-group mb-4\">
                            <label for=\"email\">Email</label>
                            <input type=\"email\" class=\"form-control @error('email') is-invalid @enderror\" id=\"email\" wire:model=\"email\" value=\"{{ session('email') }}\" placeholder=\"Masukkan Email\">
                            @error('email')
                                <div class=\"alert alert-danger mt-2\">
                                    {{ \$message }}
                                </div>
                            @enderror
                        </div>
                        
                        <div class=\"form-group mb-4\">
                            <label for=\"password\">Password</label>
                            <input type=\"password\" class=\"form-control  @error('password') is-invalid @enderror\" id=\"password\" wire:model=\"password\" placeholder=\"Masukkan Password\">
                            @error('password')
                                <div class=\"alert alert-danger mt-2\">
                                    {{ \$message }}
                                </div>
                            @enderror
                        </div>
                       
                        <button type=\"submit\" class=\"btn btn-primary\">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>";

$hasil_view_form = createFile($string, "../resources/views/livewire/auth/login.blade.php");
