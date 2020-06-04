<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
use App\User;
?>
@extends('layouts.app')
@section('content')
    <style>
        .cell .休学{
            color: #FE7B1C;
        }
        .cell .在校{
            color: #D4D7DE;
        }
        .cell .毕业{
            color: #6DCC58;
        }
        .cell .退学{
            color: #FA3D3D;
        }
        .cell .转学{
            color: #66D9FF;
        }
    </style>
    <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card">
                <div class="card-head">
                    <header class="full-width">
                        {{ $parent->name??session('school.name') }}
                        (学生总数: <span id="veri-list-total">0</span>)
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

                        <div class="table-padding col-12 pt-0">
                            @include('school_manager.school.reusable.nav_new',['highlight'=>'student'])
                        </div>
                    </header>
                </div>

                <div class="card-body" id="verify-list" name="students">
                    <div class="row">
                        <div style="width: 100%;">
                            <search-bar-new mode="students" :schoolid="school_id" v-model="where">
                                <el-button type="primary" style="margin: 12px;" @click="search">
                                    查询
                                </el-button>
                                <div slot="opt" style="float: right;margin: 12px 0;">
                                    <a href="{{ route('school_manager.student.add') }}" class="btn btn-primary pull-right">
                                        添加新学生 <i class="fa fa-plus"></i>
                                    </a>
                                </div>
                            </search-bar-new>
                            
                        </div>
                        <div class="table-responsive">
                            <el-table
                                :data="list"
                                class="table table-striped table-bordered table-hover table-checkable order-column valign-middle"
                                style="width: 100%">
                                <el-table-column
                                width="55">
                                    <template slot-scope="scope">
                                        <el-checkbox v-model="scope.row.checked"></el-checkbox>
                                    </template>
                                </el-table-column>
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
                                    label="学生状态">
                                    <template slot-scope="scope">
                                        <span :class="scope.row.status">@{{scope.row.status}}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                width="280"
                                    label="操作">
                                    <template slot-scope="scope">
                                        <el-button type="primary" @click="gokebiao(scope.row)">
                                            <i class="fa fa-calendar"></i> 查看课表
                                        </el-button>
                                        <el-dropdown @command="(e)=>{optCommand(e,scope.row)}">
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
                            <div class="table-footer">
                                <div style="display: inline-flex;">
                                    <el-checkbox style="margin-bottom: 0;margin-right: 12px" o v-model="allchecked">全选</el-checkbox>
                                    <pf-icon @click="updateStu(3, '在校')" title="在校" style="margin-right: 12px; cursor: pointer" iconsrc="stu-zaixiao" width="22px" height="22px"></pf-icon>
                                    <pf-icon @click="updateStu(4, '休学')" title="休学" style="margin-right: 12px; cursor: pointer" iconsrc="stu-xiuxue" width="22px" height="22px"></pf-icon>
                                    <pf-icon @click="updateStu(5, '退学')" title="退学" style="margin-right: 12px; cursor: pointer" iconsrc="stu-tuixue" width="22px" height="22px"></pf-icon>
                                    <pf-icon @click="updateStu(6, '转学')" title="转学" style="margin-right: 12px; cursor: pointer" iconsrc="stu-zhuanxue" width="22px" height="22px"></pf-icon>
                                    <pf-icon @click="updateStu(7, '毕业')" title="毕业" style="margin-right: 12px; cursor: pointer" iconsrc="stu-biye" width="22px" height="22px"></pf-icon>
                                </div>
                                <el-pagination
                                    background
                                    style="float: right"
                                    layout="prev, pager, next"
                                    :page-count="pagination.pageCount"
                                    :current-page="pagination.page"
                                    @current-change="onPageChange"
                                    ></el-pagination>
                            </div>
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
