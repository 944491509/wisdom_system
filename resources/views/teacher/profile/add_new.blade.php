<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
?>

@extends('layouts.app')
@section('content')
    <style>
        .teacher-edit-form .el-select{
            width: 100%;
        }
        .teacher-edit-form .el-input{
            width: 100%;
        }
        .teacher-edit-form .el-cascader{
            width: 100%;
        }
        .teacher-edit-form .el-col.col--5{
            width: 20%;
            float: left;
        }
        .teacher-edit-form .el-divider--horizontal{
            margin-top: 12px;
        }
        .teacher-edit-form .form-divider{
            font-size: 18px;
            color: #313B4C;
            margin-top: 12px;
        }
    </style>
    <div id="school-add-teacher-app">
        <div id="app-init-data-holder" style="display: none" data-school="{{ session('school.id') }}"></div>
        <div class="row">
            <div class="col-sm-12 col-md-12 col-xl-12">
                <div class="card">
                    <div class="card-head">
                        <header class="full-width">
                            创建新的教职工档案
                        </header>
                    </div>
                    <div class="teacher-edit-form">
                        <el-form label-position="top" :model="ruleForm" :rules="rules" ref="ruleForm" label-width="100px" style="padding: 20px">
                            <el-row :gutter="20" v-for="(group, index) in form">
                                <div class="form-divider" v-if="group.title">
                                    <span>@{{group.title}}</span>
                                    <el-divider></el-divider>
                                </div>
                                <el-col :span="isNaN(group.span)?null:group.span" v-for="(field, index) in group.fields" :class="group.span ==='x'?'col--5':''">
                                    <el-form-item :label="field.name" :prop="field.key">
                                        <el-input v-if="field.type==='text'" v-model="field.value"></el-input>
                                        <el-select v-else-if="field.type==='select'" v-model="field.value" :filterable="field.filterable">
                                            <el-option
                                            v-for="(item, index) in field.options"
                                            :key="index"
                                            :label="item.label"
                                            :value="item.value">
                                            </el-option>
                                        </el-select>
                                        <el-date-picker
                                            v-else-if="field.type==='date'"
                                            v-model="field.value"
                                            type="date"
                                            format="yyyy-MM-dd"
                                            value-format="yyyy-MM-dd"
                                            :picker-options="{disabledDate(time) {
                                                return time.getTime() > Date.now();
                                            }}"
                                            placeholder="选择日期">
                                        </el-date-picker>
                                        <number-input v-else-if="field.type==='number'" :decimalLen="0" v-model="field.value"></number-input>
                                        <area-selector
                                            v-else-if="field.type==='areas'" 
                                            v-model="field.value"
                                            ></area-selector>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                            <el-form-item>
                                <el-button type="primary" @click="submitForm">提交</el-button>
                            </el-form-item>
                        </el-form>
                    </div>
                    <!-- <div class="card-body">
                        <form action="{{ route('school_manager.teachers.save-profile') }}" method="post">
                            @csrf
                            <input type="hidden" name="profile[school_id]" value="{{ session('school.id') }}">
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>姓名</label>
                                        <input required type="text" class="form-control" value="" placeholder="教职工姓名" name="teacher[name]">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>手机号码</label>
                                        <input required type="text" class="form-control" value="" placeholder="手机号码" name="teacher[mobile]">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>身份证号</label>
                                        <input required type="text" class="form-control" value="" placeholder="身份证号" name="profile[id_number]">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-3">
                                    <div class="form-group">
                                        <label>教师编号</label>
                                        <input type="text" class="form-control" value="" placeholder="选填: 教师编号" name="profile[serial_number]">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label>政治面貌</label>
                                        <input required type="text" class="form-control" value="" placeholder="政治面貌" name="profile[political_name]">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label>民族</label>
                                        <input required type="text" class="form-control" value="" placeholder="民族" name="profile[nation_name]">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label>出生年月</label>
                                        <input required type="date" class="form-control" value="" placeholder="出生年月" name="profile[birthday]">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label>所属学院</label>
                                        <select name="institute_id" class="form-control" @click="getInstitutes">
                                          <option value="">请选择</option>
                                          <option v-for="(item, key) in institutes"  :value="item.id" >@{{item.name}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h4>教育/工作履历</h4>
                            <hr>

                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>参加工作时间</label>
                                        <input required type="date" class="form-control" value="" placeholder="参加工作时间" name="profile[work_start_at]">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>第一学历</label>
                                        <input required type="text" class="form-control" value="" placeholder="第一学历" name="profile[education]">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>第一学历专业</label>
                                        <input required type="text" class="form-control" value="" placeholder="第一学历专业" name="profile[major]">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>最高学历</label>
                                        <input required type="text" class="form-control" value="" placeholder="最高学历" name="profile[final_education]">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>最高学历专业</label>
                                        <input required type="text" class="form-control" value="" placeholder="最高学历专业" name="profile[final_major]">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>学位</label>
                                        <input required type="text" class="form-control" value="" placeholder="学位" name="profile[degree]">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>职称取得时间</label>
                                        <input required type="date" class="form-control" value="" placeholder="职称取得时间" name="profile[title_start_at]">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>是否聘任</label>
                                        <select required type="date" class="form-control" name="profile[hired]">
                                            <option value="1">是</option>
                                            <option value="0">否</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>聘任时间</label>
                                        <input required type="date" class="form-control" value="" placeholder="聘任时间" name="profile[hired_at]">
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>原职称取得时间</label>
                                        <input required type="date" class="form-control" value="" placeholder="原职称取得时间" name="profile[title1_at]">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>原职称聘任时间</label>
                                        <input required type="date" class="form-control" value="" placeholder="原职称聘任时间" name="profile[title1_hired_at]">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>现任专业技术职务名称</label>
                                        <input required type="text" class="form-control" value="" placeholder="现任专业技术职务名称" name="profile[title]">
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>授课类别</label>
                                        <select class="form-control" name="profile[category_teach]">
                                            <option value=""></option>
                                            <option value="1">文化课教师</option>
                                            <option value="2">公共课教师</option>
                                            <option value="3">专业课教师</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>职业授课类别</label>
                                        <select class="form-control" name="profile[category_major]">
                                            <option value=""></option>
                                            <option value="1">交通运输</option>
                                            <option value="2">农林牧渔</option>
                                            <option value="3">旅游服务</option>
                                            <option value="4">土木水利</option>
                                            <option value="5">文化教育</option>
                                            <option value="6">信息技术</option>
                                            <option value="7">财经商贸</option>
                                            <option value="8">医药卫生</option>
                                        </select>
                                    </div>
                                </div>
                            </div>



                            <div class="form-group">
                                <label>备注</label>
                                <textarea class="form-control" placeholder="选填: 备注" name="profile[notes]"></textarea>
                            </div>
                            <div id="app-init-data-holder" data-school="{{ session('school.id') }}"></div>
                        </form>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
@endsection
