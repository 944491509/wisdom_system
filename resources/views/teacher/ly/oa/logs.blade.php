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
                    <el-form :model="log" label-position="left" label-width="80px">
                        <el-form-item label="标题">
                            <el-input v-model="log.title" placeholder="请输入标题" autocomplete="off" :disabled="isDisabled"></el-input>
                        </el-form-item>
                        <el-form-item label="内容">
                            <el-input type="textarea" :autosize="{ minRows: 2, maxRows: 6 }" placeholder="请输入日志内容..." v-model="log.content" :disabled="isDisabled"></el-input>
                        </el-form-item>
                        <div v-if="show === 2 && isFromEdit!='add'">
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
                        <div v-if="show === 1 && isFromEdit!='add'">
                            <el-form-item label="接收时间">
                                <el-input v-model="log.created_at" autocomplete="off" :disabled="isDisabled"></el-input>
                            </el-form-item>
                            <el-form-item label="发送人">
                                <el-input v-model="log.send_user_name" autocomplete="off" :disabled="isDisabled"></el-input>
                            </el-form-item>
                        </div>
                    </el-form>
                    <div class="demo-drawer__footer">
                        <el-button type="primary" @click="addlog" v-if="show === 3 || isFromEdit == 'add'" :style="{backgroundColor: (isEdit? 'rgb(255,102,0)' : '')}">@{{isEdit ? '编辑' : '保存'}}</el-button>
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
                <el-checkbox v-model="item.sele" v-show="show == 3"></el-checkbox>
                <div>
                    <div class="teacher-oa-logs-card-list-top">
                        <img :src="item.avatar" alt="">
                        <div class="teacher-oa-logs-card-list-top-right">
                            <p @click="turnDetailDrawer(item, 2)" style="cursor: pointer;">@{{ item.title }}</p>
                            <p>@{{ item.created_at }}</p>
                        </div>
                    </div>
                    <div class="teacher-oa-logs-card-list-bottom">
                        <p>@{{ item.content }}</p>
                    </div>
                </div>
            </div>
            <!-- list -->
            <div v-show="show == 3 && logList.length > 0" class="teacher-oa-logs-card-button">
                <el-button @click="handleCheckAllChange">@{{btnText}}</el-button>
                <el-button type="primary" @click="openSendDrawer">发送至</el-button>
            </div>
            <el-drawer :title="sendDrawerType == 1?'发送至':'确认接收人'" :before-close="handleClose"  :visible.sync="sendDrawer" custom-class="demo-drawer" ref="sendDrawer">
                <div class="demo-drawer__send">
                  <el-form :model="log" v-if="sendDrawerType == 1">
                    <el-form-item class="form-item-send">
                      <el-input placeholder="请输入关键字" class="teacher-oa-logs-card_send" v-model="teacherKeyword">
                        <el-button slot="append" @click="teatherSearch()">搜索</el-button>
                      </el-input>
                    </el-form-item>
                  </el-form>
                    <div class="checked_send_organ_area" v-if="sendDrawerType == 1">
                        <div class="organ_item" v-for="(organ,index) in organizationList" :key="index" >
                            <el-radio-group  v-model="sendOrganChecked[index]" @change="changeOrgan(index)">
                                <el-radio-button class="label-send-drawer" v-for="item in organ" :key="item.id" :label="item.id">
                                    <template>
                                        <span class="t_name">@{{item.name}} </span>
                                    </template>
                                </el-radio-button>
                            </el-radio-group>
                        </div>
                        <div class="organ_item"  >
                            <el-checkbox-group v-model="memberCheckedList" @change="changeMember">
                                <el-checkbox-button class="label-send-drawer"  v-for="item in memberList" :label="item.id" :key="item.id">
                                    <template>
                                        <span class="t_name">@{{item.name}} </span>
                                    </template>
                                </el-checkbox-button>
                            </el-checkbox-group>
                        </div>
                    </div>
                    <ul class="checked_send_teacher_area" v-if="sendDrawerType == 2">
                        <template v-for="item in memberCheckedList" >
                            <li v-if="item == key" v-for="(value,key) in memberCheckedDetailList" :key="key" >
                                <img  class="t_avatar":src="value.avatar"/>
                                <span class="t_name">@{{value.name}}</span>
                                <span class="t_title">（@{{value.title}}）</span>
                                <span class="t_toash" @click="deleteMember(item)">删除</span>
                            </li>
                        </template>
                    </ul>

                    <div class="demo-drawer_send_footer">
                        <el-button type="primary" @click="sendDrawerType=1" v-if="sendDrawerType == 2">上一步</el-button>
                        <el-button type="primary" @click="sendlog">@{{sendDrawerType == 1?'确认':'发送'}}</el-button>
                    </div>
                  </div>
                </div>
            </el-drawer>
        </div>
    </div>
</div>

<div id="app-init-data-holder" data-school="{{ session('school.id') }}"></div>
@endsection
