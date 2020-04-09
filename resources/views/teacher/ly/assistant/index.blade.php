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
                            <dd v-html="item2.name"></dd>
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
            <el-tabs style="width: 726px;" :stretch="true" @tab-click="handleClick">
                <el-tab-pane label="待审批" name="first"></el-tab-pane>
                <el-tab-pane label="已审批" name="second"></el-tab-pane>
                <el-tab-pane label="我抄送的" name="third"></el-tab-pane>
            </el-tabs>
        </div>
        <div class="card-body" v-cloak>
            <el-input placeholder="请输入内容" v-model="input">
                <el-button slot="append" @click="serach">搜索</el-button>
            </el-input>
            <div class="bottom-table" v-if="tableData.length > 0">
                <div class="table-item" v-for="item in tableData" :key="item.id">
                    <img :src="item.avatar" alt="" width="50px" style="border-radius: 50%">
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
                    <img src="{{ asset('assets/img/teacher_blade/eye.png') }}" class="icon-image">
                </div>
                <el-pagination background layout="prev, pager, next" :total="total" @current-change="handleCurrentChange"></el-pagination>
            </div>
            <div v-else style="color: #D5D7E0;text-align: center;padding-top: 20px;background: #fff;">暂无数据~</div>
            <!-- <div class="bottom-table">
                <el-table :show-header="false" :data="tableData" stripe style="width: 100%">
                    <el-table-column prop="state" width="60">
                        <template slot-scope="scope">
                            <img class="blade_listImage" v-if="scope.row.iconState" src="{{asset('assets/img/teacher_blade/qingjia@2x.png')}}" alt="">
                            <img class="blade_listImage" v-if="!scope.row.iconState" src="{{asset('assets/img/teacher_blade/tingzhi@2x.png')}}" alt="">
                        </template>
                    </el-table-column>
                    <el-table-column prop="state" label="状态" width="110">
                    </el-table-column>
                    <el-table-column prop="name" label="姓名">
                    </el-table-column>
                    <el-table-column prop="date" label="日期" width="200">
                    </el-table-column>
                    <el-table-column prop="status" width="80px">
                        <template slot-scope="scope">
                            <span v-bind:class="{
                                'status_red': scope.row.status == 0,
                                'status_green': scope.row.status == 1,
                                'status_yellow': scope.row.status == 2,
                                'status_gray': scope.row.status == 3,
                                'status_black': scope.row.status == 4
                            }">
                                @{{statusMap[scope.row.status]}}
                            </span>
                        </template>
                    </el-table-column>
                    <el-table-column width="80px">
                        <img src="{{ asset('assets/img/teacher_blade/eye.png') }}" class="icon-image">
                    </el-table-column>
                </el-table>
            </div> -->
        </div>
    </div>
</div>


<div id="app-init-data-holder" data-school="{{ session('school.id') }}"></div>

@endsection