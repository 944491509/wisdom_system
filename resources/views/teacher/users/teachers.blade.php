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
                        <span class="pull-left pt-2">{{ $parent->name??session('school.name') }} 教职工列表: (总数: <span id="veri-list-total">0</span>)</span>
                        <div class="table-padding col-12">
                            @include('school_manager.school.reusable.nav_new',['highlight'=>'teacher'])
                        </div>
                    </header>
                </div>

                <div class="card-body" id="verify-list" name="teachers">
                    <div class="row">
                        <search-bar-new mode="teachers" :schoolid="school_id" v-model="where">
                            <el-button type="primary" style="margin: 12px;" @click="search">
                                查询
                            </el-button>
                            <div slot="opt" style="float: right;margin: 12px 0;">
                                <a href="{{ route('school_manager.teachers.add-new') }}" class="btn btn-primary pull-right">
                                    添加新教职工 <i class="fa fa-plus"></i>
                                </a>
                                <a href="{{ route('school_manager.teachers.export') }}" class="btn btn-primary pull-right">
                                    导出教职工 <i class="fa fa-plus"></i>
                                </a>
                            </div>
                        </search-bar-new>
                        
                        <div class="table-responsive">
                            <el-table
                                :data="list"
                                class="table table-striped table-bordered table-hover table-checkable order-column valign-middle"
                                style="width: 100%">
                                <el-table-column
                                    prop="hired"
                                    label="是否聘用"
                                    width="120">
                                </el-table-column>
                                <el-table-column
                                    prop="name"
                                    label="姓名">
                                </el-table-column>
                                <el-table-column
                                    label="头像"
                                    width="180">
                                    <template slot-scope="scope">
                                        <img :src="scope.row.avatar || '/assets/img/dp.jpg'" style="width: 60px;border-radius: 50%;" />
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    prop="organization"
                                    label="行政职务">
                                </el-table-column>
                                <el-table-column
                                    prop="year_manger"
                                    label="教学职务">
                                </el-table-column>
                                <el-table-column
                                width="328"
                                    label="操作">
                                    <template slot-scope="scope">
                                        <div style="line-height: 46px;">
                                            <a :href="`/school_manager/teachers/edit-avatar?uuid=${scope.row.user_id}`" id="" class="btn btn-round btn-default "><i class="fa fa-picture-o"></i>照片</a>
                                            <a :href="`/school_manager/teachers/edit-profile?uuid=${scope.row.user_id}`" id="" class="btn btn-round btn-default "><i class="fa fa-edit"></i>档案管理</a>
                                            <a :href="`/teacher/profile/update-password?uuid=${scope.row.user_id}`" id="" class="btn btn-round btn-default "><i class="fa fa-key"></i>修改密码</a>
                                        </div>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <div class="table-footer">
                                <el-pagination
                                    background
                                    style="float: right"
                                    layout="prev, pager, next"
                                    :page-count="pagination.pageCount"
                                    :current-page="pagination.page"
                                    @current-change="onPageChange"
                                    ></el-pagination>
                            </div>
                        </div>
                        <!-- <div class="table-responsive"> -->
                            <!-- <table class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                                <thead>
                                <tr>
                                    <th>是否聘用</th>
                                    <th>姓名</th>
                                    <th>头像</th>
                                    <th>行政职务</th>
                                    <th>教学职务</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td>
                                            <img style="width: 60px;border-radius: 50%;" src="" alt="">
                                        </td>
                                        <td>

                                        </td>
                                        <td>

                                        </td>
                                        <td class="text-center">
                                            {{ Anchor::Print(['text'=>'照片','href'=>route('school_manager.teachers.edit-avatar',['uuid'=>'user_id'])], Button::TYPE_DEFAULT,'picture-o') }}
                                            {{ Anchor::Print(['text'=>'档案管理','href'=>route('school_manager.teachers.edit-profile',['uuid'=>'user_id'])], Button::TYPE_DEFAULT,'edit') }}
                                            {{ Anchor::Print(['text'=>'修改密码','href'=>route('teacher.profile.update-password',['uuid'=>'user_id'])], Button::TYPE_DEFAULT,'key') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table> -->
                        <!-- </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
