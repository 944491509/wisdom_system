@extends('layouts.app')
@section('content')
<div id="teacher-assistant-index-app" v-cloak>
    <div class="banner-list" v-for="(item, i) in bannerData">
        <div class="blade_title" v-html="item.name"></div>
        <div class="card">
            <div class="card-body clearfix">
                <div class="banner-item clearfix" v-for="(item2, i2) in item.helper_page">
                    <a :href="item2.url">
                        <dl>
                            <dt><img :src="item2.icon" alt=""></dt>
                            <dd v-html="item2.name" style="color: #414a5a"></dd>
                        </dl>
                    </a>
                    <div class="bunner-line" v-if="i2+1 != item.helper_page.length">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="blade_title">
        学生审批
    </div>
    <div class="card bottom-card">
        <div class="card-head">
            <el-tabs style="width: 100%;" :stretch="true" @tab-click="handleClick">
                <el-tab-pane label="待审批" name="first"></el-tab-pane>
                <el-tab-pane label="已审批" name="second"></el-tab-pane>
                <el-tab-pane label="我抄送的" name="third"></el-tab-pane>
            </el-tabs>
        </div>
        <div class="card-body" v-cloak>
            <el-input placeholder="请输入内容" v-model="input">
                <el-button slot="append" @click="serach" style="background-color: #4ea5fe;">搜索</el-button>
            </el-input>
            <div class="bottom-table" v-if="tableData.length > 0">
                <div class="table-item" v-for="item in tableData" :key="item.id">
                    <img :src="item.avatar" alt="" width="45px" style="height: 45px;border-radius: 50%">
                    <span style="color: #313B4C;" class="type">@{{ item.flow.name }}</span>
                    <span style="color: #99A0AD;">申请人：@{{ item.user_name }}</span>
                    <span style="color: #D5D7E0;">申请日期：@{{ item.created_at }}</span>
                    <span v-bind:class="{
                                'status_orange': item.done == 0,
                                'status_green': item.done == 1,
                                'status_red': item.done == 2,
                                'status_gray': item.done == 3
                            }">
                        @{{statusMap[item.done]}}</span>
                    <img src="{{ asset('assets/img/teacher_blade/eye.png') }}" class="icon-image" style="cursor: pointer;" @click="viewAction(item.id)">
                </div>
                <el-pagination background layout="prev, pager, next" :total="total" @current-change="handleCurrentChange"></el-pagination>
            </div>
            <div v-else style="color: #D5D7E0;text-align: center;padding-top: 20px;background: #fff;">暂无数据~</div>
        </div>
    </div>
    <view-action ref="showAction" />
</div>


<div id="app-init-data-holder" data-school="{{ session('school.id') }}"></div>

@endsection