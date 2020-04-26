<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
?>

@extends('layouts.app')
@section('content')
 <div id="app-init-data-holder" data-school="{{ session('school.id') }}"></div>
<form action="{{ route('verified_student.profile.update') }}" method="post" id="edit-student-form">
    @csrf
    <div class="row">
        <div class="col-md-3">
            @include('student.elements.sidebar.avatar',['profile'=>$student->profile])
            @include('student.elements.sidebar.about_student',['profile'=>$student->profile])
        </div>
        <div class="col-sm-12 col-md-6 col-xl-6">
            <div class="card">
                <div class="card-head">
                    <header>用户资料管理 ({{ session('school.name') }}) - {{ $student->name }} </header>
                    @if($gradeManager)
                        <li class="list-group-item"><b>{{$student->gradeUser->grade->name}}的班长</b></li>
                    @else
                        <li class="list-group-item"><b>{{$student->gradeUser->grade->name}}的学生</b></li>
                    @endif
                    @foreach($student->community as $community)
                        <li class="list-group-item"><b>{{$community->name}}社团的团长</b></li>
                    @endforeach
                </div>
                <div class="card-body " id="bar-parent">
                @include('student.elements.form.profile',['student'=>$student])
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-3 col-xl-3">
            <div class="card">
                <div class="card-head">
                    <header>专业班级信息</header>
                </div>
                <div class="card-body" id="school-add-student-app">
                    <div class="form-group">
                        <label class="control-label">专业</label>
                        <select name="grade_user[major_id]" class="form-control" @click="getMajors" v-model="majorId">
                          <option value="">请选择</option>
                          <option v-for="(item, key) in majors"  :value="item.id" >@{{item.name}}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">班级</label>
                        <select name="grade_user[grade_id]" class="form-control" @click="getGrades">
                          <option value="">请选择</option>
                          <option v-for="(item, key) in grades"  :value="item.id" >@{{item.name}}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-head">
                    <header>奖惩信息</header>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="student-number-input">奖励记录</label>
                        <textarea class="form-control" rows="3" placeholder="例: 2019年3月5日 获得征文大赛一等奖" name="addition[reward]" >{{$addition->reward ?? ''}}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="student-number-input">惩罚记录</label>
                        <textarea class="form-control" rows="3" placeholder="例: 2019年3月5日 违法学校安全规章制度" name="addition[punishment]">{{$addition->punishment  ?? ''}}</textarea>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-head">
                    <header>寄宿信息</header>
                </div>
                <div class="card-body">
                      <div class="form-group">
                         <input type="text" class="form-control"  placeholder="联系人" name="addition[people]" value="{{$addition->people  ?? ''}}">
                      </div>
                      <div class="form-group">
                          <input type="text" class="form-control"  placeholder="联系电话" name="addition[mobile]" value="{{$addition->mobile  ?? ''}}">
                      </div>
                      <div class="form-group">
                          <input type="text" class="form-control"  placeholder="寄宿地址" name="addition[address]" value="{{$addition->address ?? '' }}">
                      </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
