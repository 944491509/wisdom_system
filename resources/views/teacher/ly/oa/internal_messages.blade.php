@extends('layouts.app')
@section('content')
    <div class="row message-common" id="teacher-oa-message-app">
        <div class="col-sm-12 col-md-12 col-xl-12">
          <div class="page-header">内部信</div>
            <div class="card">
                <div class="card-head">
                    <header class="full-width">
                        <div class="title">
                          <span class="h-title">
                              <span class="icon" :class="activeName"></span>
                              <span class="title-text">@{{activeNameText}}</span>
                          </span>  
                          <el-button type="primary" size="small" @click="formbroad(false)">发信</el-button>
                        </div>
                    </header>
                </div>
                <div class="card-body">
                  <el-tabs v-model="activeName">
                    <el-tab-pane v-for="(type, index) in messageTypes" :key="index" :label="type.text" :name="type.status">
                      <message-list :mode="type.status" :ref="type.status" @formbroad="formbroad"/>
                    </el-tab-pane>
                  </el-tabs>
                </div>
            </div>
          <el-drawer
            ref="addMessageDrawer"
            :destroy-on-close="true"
            :before-close="checkClose"
            :visible.sync="addDrawer"
            direction="rtl">
            <template slot="title">
                <div class="add-message-title"><span class="title-icon" :class="formTitleIcon"></span> @{{formTitle}}</div>
            </template>
            <message-form ref="addMessageForm" @done="onMessageCreated"/>
          </el-drawer>
        </div>
    <div id="app-init-data-holder"
         data-school="{{ session('school.id') }}"
    ></div>
@endsection
