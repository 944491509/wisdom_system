@extends('layouts.app')
@section('content')
<div id="teacher-oa-logs-app">
    <div class="col-sm-12 col-md-12 col-xl-12">
        <div class="teacher-oa-logs-card">
            <div class="teacher-oa-logs-card_title">
                <p>日志</p>
                <p @click="add" type="primary">添加</p>
            </div>
            <el-drawer :title="drawerTitle" :before-close="handleClose" :visible.sync="drawer" custom-class="demo-drawer" ref="drawer">
                <div class="demo-drawer__content">
                    <el-form :model="log" label-position="left" label-width="70px">
                        <el-form-item label="标题">
                            <el-input v-model="log.title" placeholder="请输入标题" autocomplete="off" :disabled="isDisabled"></el-input>
                        </el-form-item>
                        <el-form-item label="内容">
                            <el-input type="textarea" :autosize="{ minRows: 2, maxRows: 6 }" placeholder="请输入日志内容..." v-model="log.content" :disabled="isDisabled"></el-input>
                        </el-form-item>
                        <div v-if="show === 2">
                            <el-form-item label="保存时间">
                                <el-input v-model="log.created_at" autocomplete="off" :disabled="isDisabled"></el-input>
                            </el-form-item>
                            <el-form-item label="发送时间">
                                <el-input v-model="log.updated_at" autocomplete="off" :disabled="isDisabled"></el-input>
                            </el-form-item>
                            <el-form-item label="接收人">
                                <el-input v-model="log.collect_user_name" autocomplete="off" :disabled="isDisabled"></el-input>
                            </el-form-item>
                        </div>
                        <div v-if="show === 1">
                            <el-form-item label="接收时间">
                                <el-input v-model="log.updated_at" autocomplete="off" :disabled="isDisabled"></el-input>
                            </el-form-item>
                            <el-form-item label="发送人">
                                <el-input v-model="log.send_user_name" autocomplete="off" :disabled="isDisabled"></el-input>
                            </el-form-item>
                        </div>
                    </el-form>
                    <div class="demo-drawer__footer">
                        <el-button type="primary" @click="addlog" v-if="show === 3">@{{isEdit ? '编辑' : '保存'}}</el-button>
                    </div>
                </div>
            </el-drawer>
            <ul class="teacher-oa-logs-card_type">
                <li v-for="item in nav" :key="item.type" @click="list_click(item.type)" :class="{'bgred':show==item.type}">@{{item.tit}}</li>
            </ul>
            <el-input placeholder="请输入标题关键字" class="teacher-oa-logs-card_search" v-model="keyword">
                <el-button slot="append" @click="getlogList(show)">搜索</el-button>
            </el-input>
            <!-- list -->
            <div class="teacher-oa-logs-card-list" v-for="item in logList" :key="item.id">
                <el-checkbox v-model="item.sele" v-show="nav[2].type"></el-checkbox>
                <div>
                    <div class="teacher-oa-logs-card-list-top">
                        <img :src="item.avatar" alt="">
                        <div class="teacher-oa-logs-card-list-top-right">
                            <p @click="turnDetailDrawer(item, 2)">@{{ item.title }}</p>
                            <p>@{{ item.created_at }}</p>
                        </div>
                    </div>
                    <div class="teacher-oa-logs-card-list-bottom">
                        <p>@{{ item.content }}</p>
                    </div>
                </div>
            </div>
            <!-- list -->
            <div v-show="nav[2].type == 1" class="teacher-oa-logs-card-button">
                <el-button @click="handleCheckAllChange">@{{btnText}}</el-button>
                <el-button type="primary" @click="openSendDrawer">发送至</el-button>
            </div>
            <el-drawer :title="sendTitle" :before-close="handleClose" :visible.sync="sendDrawer" custom-class="demo-drawer" ref="drawer">
                <div class="demo-drawer__send">
                  <el-form :model="log" >
                    <el-form-item class="form-item-send">
                      <el-input placeholder="请输入关键字" class="teacher-oa-logs-card_send" v-model="teacherKeyword">
                        <el-button slot="append" @click="teatherSearch()">搜索</el-button>
                      </el-input>
                    </el-form-item>
                  </el-form>
                  <ul class="checked_send_teacher_area">
                        <el-checkbox-group v-model="sendTeacherCheckedList">
                            <li v-for="item in teachterList" :key="item.id">
                                <el-checkbox class="d-block"  :label="item.id">
                                    <template>
                                        <img :src="item.avatar" />
                                        <span class="t_name">@{{item.name}} </span>
                                    </template>
                                </el-checkbox>
                            </li>
                        </el-checkbox-group>
                    </ul>
                    <div class="demo-drawer_send_footer">
                        <el-button type="primary" @click="sendlog">确定</el-button>
                    </div>
                  </div>
                </div>
            </el-drawer>
        </div>
    </div>
</div>

<div id="app-init-data-holder" data-school="{{ session('school.id') }}"></div>
@endsection
