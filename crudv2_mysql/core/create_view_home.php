<?php
$string = "
@extends('layouts.app')
@section('title')
    Halaman Home
@endsection
@section('content')
<div class=\"container mt-5\">
    <h1>Welcome {{ Auth::user()->name }}</h1>
    <p>You are logged in!</p>
</div>
@endsection";

$hasil_view_form = createFile($string, "../resources/views/home.blade.php");
