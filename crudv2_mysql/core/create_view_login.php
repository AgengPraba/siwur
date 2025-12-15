<?php
$string = "
@extends('layouts.app_auth')
@section('title')
    Halaman Login
@endsection
@section('content')
<div class=\"container mt-5 mb-5\">
    <div class=\"row\">
        <div class=\"col-md-12\">
            <div class=\"card border-0 shadow-sm rounded\">
                <div class=\"card-body\">
                  <h2>Halaman Login</h2>
                  @if (\$errors->any())
                        <div class=\"alert alert-danger\">
                            <ul>
                                @foreach (\$errors->all() as \$error)
                                    <li>{{ \$error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                  <form method=\"POST\" action=\"{{ route('login') }}\">
                    @csrf
                    <div class=\"mb-3\">
                        <label for=\"email\" class=\"form-label\">Email address</label>
                        <input type=\"email\" class=\"form-control\" id=\"email\" name=\"email\" required>
                    </div>
                    <div class=\"mb-3\">
                        <label for=\"password\" class=\"form-label\">Password</label>
                        <input type=\"password\" class=\"form-control\" id=\"password\" name=\"password\" required>
                    </div>
                    <button type=\"submit\" class=\"btn btn-primary\">Login</button>
                </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection";

$hasil_view_form = createFile($string, "../resources/views/auth/login.blade.php");
