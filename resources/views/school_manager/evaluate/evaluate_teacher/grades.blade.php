<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
use App\User;
$years = \App\Utils\Time\GradeAndYearUtil::GetAllYears();
?>
@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card">
                <div class="card-head">
                    <header class="full-width">

                    </header>
                </div>
                <div class="card-body">
                    <div class="row">


                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>入学年份</th>
                                    <th>班级名称</th>
                                    <th>班主任</th>
                                    <th>班长</th>
                                    <th class="text-center">学生数</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($grades as $index=>$item)
                                    <tr>
                                        <td>{{ $index +1 }}</td>
                                        <td> {{ $item->grade->year }}</td>
                                        <td> {{ $item->grade->name }}</td>
                                        <td>
                                            @if($item->grade->gradeManager)
                                                {{ $item->grade->gradeManager->adviser_name }}
                                                @if(\Illuminate\Support\Facades\Auth::user()->isSchoolAdminOrAbove())
                                                    <a href="{{ route('school_manager.grade.set-adviser',['grade'=>$item->grade_id]) }}">(编辑)</a>
                                                @endif
                                            @else
                                                @if(\Illuminate\Support\Facades\Auth::user()->isSchoolAdminOrAbove())
                                                    <a href="{{ route('school_manager.grade.set-adviser',['grade'=>$item->grade_id]) }}">设置班主任</a>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->grade->gradeManager)
                                                {{ $item->grade->gradeManager->monitor_name }}
                                                @if(\Illuminate\Support\Facades\Auth::user()->isSchoolAdminOrAbove() || \Illuminate\Support\Facades\Auth::user()->isTeacher())
                                                    <a href="{{ route('teacher.grade.set-monitor',['grade'=>$item->grade_id]) }}">(编辑)</a>
                                                @endif
                                            @else
                                                @if(\Illuminate\Support\Facades\Auth::user()->isSchoolAdminOrAbove() || \Illuminate\Support\Facades\Auth::user()->isTeacher())
                                                    <a href="{{ route('teacher.grade.set-monitor',['grade'=>$item->grade_id]) }}">设置班长</a>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a class="students-counter" href="{{ route('teacher.grade.users',['type'=>\App\User::TYPE_STUDENT,'by'=>'grade','uuid'=>$item->grade_id]) }}">{{ $item->grade->studentsCount() }}</a></td>
                                        <td class="text-center">
                                            {{ \App\Utils\UI\Anchor::Print(['text'=>'查看','class'=>'btn-edit-evaluate','href'=>route('school_manager.evaluate-teacher.student-list',['evaluate_teacher_id'=>$evaluate_teacher_id,'grade_id'=>$item->grade_id])], \App\Utils\UI\Button::TYPE_DEFAULT,'edit') }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
