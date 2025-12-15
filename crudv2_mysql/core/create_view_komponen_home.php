<?php
$string = "
@section('title')
Home
@endsection

<div class=\"container mt-5 mb-5\">
    <div class=\"row\">
        <div class=\"col-md-12\">

            <!-- flash message -->
            @if (session()->has('message'))
            <div class=\"alert alert-success\">
                {{ session('message') }}
            </div>
            @endif
            <!-- end flash message -->

       
            <div class=\"card border-0 rounded shadow-sm\">
                <div class=\"card-body\">
                </div>
            </div>
        </div>
    </div>
</div>

";

$hasil_view_form = createFile($string, "../resources/views/livewire/home.blade.php");
