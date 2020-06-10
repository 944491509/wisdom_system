<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
?>

@extends('layouts.app')
@section('content')
    <div class="row" id="school-add-teacher-app">
        <div id="app-init-data-holder" style="display: none" data-school="{{ session('school.id') }}"></div>
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card">
                <div class="card-head">
                    <header class="full-width">
                        编辑“@{{teacherName}}”的教职工档案
                        <a href="{{ route('school_manager.school.teachers') }}" class="btn btn-primary">返回教职工档案管理</a>
                    </header>
                </div>
                <teacher-form ref="teacherform" :schoolid="schoolid" :teacher_id="teacher_id"></teacher-form>
            </div>
        </div>
    </div>
@endsection
