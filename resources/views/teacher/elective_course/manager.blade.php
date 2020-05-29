@extends('layouts.app')
@section('content')
    <div class="row course-common" id="teacher-elective-course-manage">
        <div class="col-sm-12 col-md-12 col-xl-12">
          <div class="page-header">选课</div>
            <div class="card">
                <!-- <div class="card-head">
                    <header class="full-width">
                        <div class="title">
                          <span class="h-title">@{{activeNameText}}</span>  
                          <el-button type="primary" size="small" @click="()=>{addDrawer=true}">创建任务</el-button>
                        </div>
                    </header>
                </div> -->
                <div class="card-body">
                  <el-tabs v-model="activeName">
                    <el-tab-pane v-for="(type, index) in courseTypes" :key="index" :label="type.text" :name="type.status">
                      <course-list :mode="type.status" :ref="type.status" @detail="checkDetail"/>
                    </el-tab-pane>
                  </el-tabs>
                </div>
            </div>
          <el-drawer
            title="创建任务"
            ref="courseDetailDrawer"
            :destroy-on-close="true"
            :before-close="checkClose"
            :visible.sync="coursedetailshow"
            direction="rtl">
            <template slot="title">
                <div class="course-detail-title"><span class="title-icon"><span class="icon-pic"></span></span> @{{detailTypeText}}-详情</div>
            </template>
            <course-detail :courseid="courseId" :mode="courseMode"/>
          </el-drawer>
        </div>
    <div id="app-init-data-holder"
         data-school="{{ session('school.id') }}"
    ></div>
@endsection
