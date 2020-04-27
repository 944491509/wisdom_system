@php
@endphp
@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-9 col-lg-9 col-xl-8">
            <div class="card">
                <div class="card-head">
                    <header>
                        {{ session('school.name') }}
                    </header>
                </div>
            </div>
        </div>
    </div>
@endsection
