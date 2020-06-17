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
                        <header class="full-width" v-if="studentName">
                            编辑“@{{studentName}}”的档案
                        </header>
                    </div>
                    <student-form ref="studentform" :schoolid="schoolid" :student_id="student_id" :status="status"></student-form>
                </div>
            </div>
        </div>
    </div>
@endsection
