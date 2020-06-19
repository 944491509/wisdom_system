<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
use App\User;
?>
@extends('layouts.app')
@section('content')
    <style>
        .table-responsive .el-table__header{
            border-bottom: 1px solid #dee2e6
        }
    </style>
    <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card">
                <div class="card-head">
                    <header class="full-width">
                        {{ $parent->name??session('school.name') }}
                        (未认证用户总数: <span id="veri-list-total">0</span>)
                        <div class="table-padding col-12 pt-0">
                            @include('school_manager.school.reusable.nav_new',['highlight'=>'users'])
                        </div>
                    </header>
                </div>

                <div class="card-body" id="verify-list" name="users">
                    <div class="row">
                        <div style="width: 100%;">
                            <search-bar-new mode="users" :schoolid="school_id" v-model="where">
                                <div slot="opt" style="margin: 12px;display: inline-block;">
                                    <el-button type="primary" style="margin: 12px;" @click="search">
                                        查询
                                    </el-button>
                                </div>
                                <div slot="opt" style="float: right;margin: 12px 0;">
                                    <a href="{{ route('school_manager.student.add') }}?status=1" class="btn btn-primary pull-right">
                                        添加新用户 <i class="fa fa-plus"></i>
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
                                    prop="student_number"
                                    label="#"
                                    width="60">
                                    <template slot-scope="scope">
                                        @{{(scope.$index + 1)+ (pagination.page - 1) * 20}}
                                    </template>
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
                                width="280"
                                    label="操作">
                                    <template slot-scope="scope">
                                        <el-button type="primary" @click="gokeEdit(scope.row)">
                                            <i class="el-icon-edit"></i> 编辑
                                        </el-button>
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
