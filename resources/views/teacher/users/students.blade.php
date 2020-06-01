<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
use App\User;
?>
@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card">
                <div class="card-head">
                    <header class="full-width">
                        {{ $parent->name??session('school.name') }}(学生总数: )
                        @if(isset($parent) && get_class($parent) === 'App\Models\Schools\Grade')
                            @if($parent->gradeManager)
                                <a href="{{ route('school_manager.grade.set-adviser',['grade'=>$parent->id]) }}">
                                @if($parent->gradeManager->adviser_id)
                                    班主任: {{ $parent->gradeManager->adviser_name }}
                                @else
                                    设置班主任
                                @endif
                                </a>

                                <a href="{{ route('teacher.grade.set-monitor',['grade'=>$parent->id]) }}">
                                    @if($parent->gradeManager->monitor_id)
                                        班长: {{ $parent->gradeManager->monitor_name }}
                                    @else
                                        设置班长
                                    @endif
                                </a>
                            @else

                            @endif
                        @endif

                        <a href="{{ route('school_manager.student.add') }}" class="btn btn-primary pull-right">
                            添加新学生 <i class="fa fa-plus"></i>
                        </a>
                    </header>
                </div>

                <div class="card-body" id="verify-list" name="students">
                    <div class="row">
                        <div class="table-padding col-12 pt-0">
                            @include('school_manager.school.reusable.nav_new',['highlight'=>'student'])
                        </div>
                        <search-bar-new mode="students" :schoolid="school_id"></search-bar-new>
                        <el-button type="primary" style="margin: 12px;">
                            查询
                        </el-button>
                        <div class="table-responsive">
                            <el-table
                                :data="list"
                                class="table table-striped table-bordered table-hover table-checkable order-column valign-middle"
                                style="width: 100%">
                                <el-table-column
                                    prop="student_number"
                                    label="学号"
                                    width="180">
                                </el-table-column>
                                <el-table-column
                                    label="头像"
                                    width="180">
                                    <template slot-scope="scope">
                                        <img :src="scope.row.avatar || '/assets/img/dp.jpg'" style="width: 60px;border-radius: 50%;" />
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    prop="name"
                                    label="姓名">
                                </el-table-column>
                                <el-table-column
                                    prop="mobile"
                                    label="联系电话">
                                </el-table-column>
                                <el-table-column
                                    prop="grade"
                                    label="所在班级">
                                </el-table-column>
                                <el-table-column
                                    prop="enquiries"
                                    label="待办的申请">
                                </el-table-column>
                                <el-table-column
                                width="280"
                                    label="操作">
                                    <template slot-scope="scope">
                                        <el-button type="primary">
                                            <i class="fa fa-calendar"></i>查看课表
                                        </el-button>
                                        <el-dropdown @command="optCommand">
                                            <el-button type="primary">
                                                可执行操作
                                            </el-button>
                                            <el-dropdown-menu slot="dropdown">
                                                <el-dropdown-item command="edit">编辑</el-dropdown-item>
                                                <el-dropdown-item command="photo">照片</el-dropdown-item>
                                            </el-dropdown-menu>
                                        </el-dropdown>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <el-pagination
                                background
                                layout="prev, pager, next"
                                :page-count="pagination.pageCount"
                                :current-page="pagination.page"
                                @current-change="onPageChange"
                                ></el-pagination>
                            <!-- <table class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                                <thead>
                                <tr>
                                    <th>学号</th>
                                    <th>头像</th>
                                    <th>姓名</th>
                                    <th>联系电话</th>
                                    <th>所在班级</th>
                                    <th>待办的申请</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <img src="{" style="width: 60px;border-radius: 50%;">
                                        </td>
                                        <td>

                                        </td>
                                        <td>
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-center">
                                            <a target="_blank" href="{{ route('school_manager.grade.view.timetable',['uuid'=>'grade_id']) }}" class="btn btn-round btn-primary btn-view-timetable">
                                                <i class="fa fa-calendar"></i>查看课表
                                            </a>
                                            @php
                                            Button::PrintGroup(
                                                [
                                                    'text'=>'可执行操作',
                                                    'subs'=>[
                                                        ['url'=>route('verified_student.profile.edit',['uuid'=>'uuid']),'text'=>'编辑'],
                                                        ['url'=>route('teacher.student.edit-avatar',['uuid'=>'uuid']),'text'=>'照片'],
                                                    ]
                                                ],
                                                Button::TYPE_PRIMARY
                                            )
                                            @endphp
                                        </td>
                                    </tr>
                                </tbody>
                            </table> -->
                        </div>
                        <div class="row">
                            <div class="col-12">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
