@extends('layouts.app')
@section('content')
    <div class="row meeting-common" id="teacher-oa-meeting-app">
        <div class="col-sm-12 col-md-12 col-xl-12">
          <div class="page-header">会议</div>
            <div class="card">
                <div class="card-head">
                    <header class="full-width">
                        <div class="title">
                          <span class="h-title">
                              <span class="icon" :class="activeName"></span>
                              <span class="title-text">@{{activeNameText}}</span>
                          </span>  
                          <el-button type="primary" size="small" @click="formbroad(false)">创建会议</el-button>
                        </div>
                    </header>
                </div>
                <div class="card-body">
                  <el-tabs v-model="activeName">
                    <el-tab-pane v-for="(type, index) in meetingTypes" :key="index" :label="type.text" :name="type.status">
                      <meeting-list :mode="type.status" :ref="type.status" @formbroad="formbroad"/>
                    </el-tab-pane>
                  </el-tabs>
                </div>
            </div>
        </div>
    <div id="app-init-data-holder"
         data-school="{{ session('school.id') }}"
    ></div>
@endsection
