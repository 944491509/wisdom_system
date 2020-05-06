@extends('layouts.app')
@section('content')
    <div class="row task-common" id="teacher-oa-meeting-detail-app">
        <div class="col-sm-12 col-md-12 col-xl-12">
          <div class="page-header">会议</div>
          <meeting-detail />
        </div>
    <div id="app-init-data-holder"
         data-school="{{ session('school.id') }}"
    ></div>
@endsection
