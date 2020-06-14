<?php
?>

@extends('layouts.app')
@section('content')
    <div id="school-add-student-app">
        <div id="app-init-data-holder" style="display: none" data-school="{{ session('school.id') }}"></div>
      <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card">
                <div class="card-head">
                    <header class="full-width">
                        创建新的学生档案
                    </header>
                </div>
                <student-form ref="teacherform" :schoolid="schoolid" :student_id="student_id" :status="status"></student-form>
            </div>
        </div>
    </div>
    </div>
@endsection
