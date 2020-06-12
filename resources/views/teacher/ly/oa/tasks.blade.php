@extends('layouts.app')
@section('content')
    <div class="row task-common" id="teacher-oa-tasks-app">
        <div class="col-sm-12 col-md-12 col-xl-12">
          <div class="page-header">任务</div>
            <div class="card">
                <div class="card-head">
                    <header class="full-width">
                        <div class="title">
                          <span class="h-title"><span><pf-icon :iconsrc="`teacher/task-${activeName}`" :text="activeNameText"/></span></span>  
                          <el-button type="primary" size="small" @click="()=>{addDrawer=true}">创建任务</el-button>
                        </div>
                    </header>
                </div>
                <div class="card-body">
                  <el-tabs v-model="activeName">
                    <el-tab-pane v-for="(type, index) in taskTypes" :key="index" :label="type.text" :name="type.status">
                      <task-list :mode="type.status" :ref="type.status"/>
                    </el-tab-pane>
                  </el-tabs>
                </div>
            </div>
          <el-drawer
            title="创建任务"
            ref="addTaskDrawer"
            :destroy-on-close="true"
            :before-close="checkClose"
            :visible.sync="addDrawer"
            direction="rtl">
            <template slot="title">
              <pf-icon :iconsrc="`teacher/task-create`" text="创建任务"/>
            </template>
            <task-form @done="onTaskCreated" :currentUserId="currentUserId"/>
          </el-drawer>
        </div>
    <div id="app-init-data-holder"
         data-school="{{ session('school.id') }}"
    ></div>
@endsection
