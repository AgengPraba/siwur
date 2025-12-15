<?php
$string = "
@section('title')
    Halaman Register
@endsection

<div class=\"container mt-5 mb-5\">
    <div class=\"row\">
        <div class=\"col-md-12\">
            <div class=\"card border-0 shadow-sm rounded\">
                <div class=\"card-body\">
                    <h2>Register</h2>

                    <form wire:submit.prevent=\"register\">
                        @csrf
                        <div class=\"form-group mb-4\">
                            <label for=\"name\">Name</label>
                            <input type=\"text\" class=\"form-control @error('name') is-invalid @enderror\" id=\"name\"
                                wire:model=\"name\" placeholder=\"Masukkan Nama Lengkap\">
                            @error('name')
                                <div class=\"alert alert-danger mt-2\">
                                    {{ \$message }}
                                </div>
                            @enderror
                        </div>
                        <div class=\"form-group mb-4\">
                            <label for=\"email\">Email</label>
                            <input type=\"email\" class=\"form-control @error('email') is-invalid @enderror\"
                                id=\"email\" wire:model=\"email\" placeholder=\"Masukkan Email\">
                            @error('email')
                                <div class=\"alert alert-danger mt-2\">
                                    {{ \$message }}
                                </div>
                            @enderror
                        </div>
                        <div class=\"form-group mb-4\">
                            <label for=\"password\">Password</label>
                            <input type=\"password\" class=\"form-control @error('password') is-invalid @enderror\" id=\"password\" wire:model=\"password\" placeholder=\"Masukkan Password\" >
                            @error('password')
                                <div class=\"alert alert-danger mt-2\">
                                    {{ \$message }}
                                </div>
                            @enderror
                        </div>
                        <div class=\"form-group mb-4\">
                            <label for=\"password_confirmation\">Confirm Password</label>
                            <input type=\"password\" class=\"form-control @error('password_confirmation') is-invalid @enderror\" id=\"password_confirmation\"
                                wire:model=\"password_confirmation\" placeholder=\"Masukkan Konfirmasi Password\">
                        </div>
                        <button type=\"submit\" class=\"btn btn-primary\">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
";

$hasil_view_form = createFile($string, "../resources/views/livewire/auth/register.blade.php");
