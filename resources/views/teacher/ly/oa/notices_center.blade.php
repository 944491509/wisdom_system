@extends('layouts.app')
@section('content')
<div id="teacher-oa-notices-app">
    <!-- <div class="col-sm-12 col-md-12 col-xl-12"> -->
    <div class="notices-card teacher-oa-notices-app-one">
        <div class="notices-card-header">
            <p>通知公告</p>
            <p class="release" @click="releaseDrawer = true">发布</p>
        </div>
        <div class="teacher-oa-notices-app-one-body" v-for="item in oneList" :key="item.id" @click="oneDetail(item.id)" v-cloak>
            <div class="teacher-oa-notices-app-one-body-title">
                <p>@{{ item.title }}</p>
                <p>@{{ item.is_read == 0 ? '未读' : '已读' }}</p>
            </div>
            <p class="teacher-oa-notices-app-one-body-content">@{{ item.content }}</p>
            <p class="teacher-oa-notices-app-one-body-time">@{{ item.created_at }}</p>
        </div>
    </div>
    <div class="notices-card teacher-oa-notices-app-two">
        <div class="notices-card-header">
            <p>公告</p>
        </div>
        <div class="teacher-oa-notices-app-two-body" v-for="item in twoList" :key="item.id" @click="oneDetail(item.id)" v-cloak>
            <img :src="item.image" alt="">
            <div class="teacher-oa-notices-app-two-body-title">
                <p>@{{ item.is_read == 0 ? '未读' : '已读' }}</p>
                <p>@{{ item.title }}</p>
                <p>@{{ item.created_at }}</p>
            </div>
        </div>
    </div>
    <div class="notices-card teacher-oa-notices-app-three">
        <div class="notices-card-header">
            <p>检查</p>
        </div>
        <div class="teacher-oa-notices-app-three-body" v-for="item in threeList" :key="item.id" @click="oneDetail(item.id)" v-cloak>
            <div class="teacher-oa-notices-app-three-body-title">
                <p>@{{ item.title }}</p>
                <p>@{{ item.is_read == 0 ? '未读' : '已读' }}</p>
            </div>
            <p class="teacher-oa-notices-app-three-body-content">@{{ item.content }}</p>
            <div class="teacher-oa-notices-app-three-body-time">
                <p v-show="item.inspect != ''">@{{ item.inspect }}</p>
                <p>@{{ item.created_at}}</p>
            </div>
        </div>
    </div>
    <el-drawer :title="titleName" :before-close="handleClose" :visible.sync="drawer" custom-class="demo-drawer" v-cloak>
        <div class="demo-drawer__content">
            <p>@{{detail.title}}</p>
            <p>@{{detail.created_at}}</p>
            <p>@{{detail.content}}</p>
            <img :src="detail.image" alt="" v-if="detail.type != 1">
            <div class="demo-drawer__content_enclosure" v-if="attachments.length">
                <p class="word">附件</p>
                <p v-for="att in attachments" :key="att.id" class="enclosure"  >
                    @{{ att.file_name}}
                    <a class="view_more" target="_blank" :href="att.url">查看</a>
                </p>
            </div>
        </div>
    </el-drawer>
    <!-- </div> -->

    <el-drawer
      title="发布通知"
      :before-close="handleClose1"
      :visible.sync="releaseDrawer"
      custom-class="demo-drawer"
      size="40%"
      >
      <div class="drawer_content">
        <el-form :model="form" label-width="80px">
          <el-form-item label="标题" label-width="50px">
            <el-input v-model="form.title" autocomplete="off"></el-input>
          </el-form-item>
          <el-form-item label="内容" label-width="50px">
            <el-input type="textarea" :rows="4" placeholder="请输入通知内容" v-model="form.textarea"></el-input>
          </el-form-item>
          <el-form-item label="选择教师可见范围" label-width="130px">
            <div class="dayu" @click="innerDrawer = true">></div>
          </el-form-item>
          <el-form-item label="选择学生可见范围" label-width="130px">
            <div class="dayu" @click="innerDrawer = true">></div>
          </el-form-item>
          <el-form-item label="附件" label-width="50px" style="font-size: 16px; color: #000;">
            <div class="">(图片格式)</div>
          </el-form-item>
          <div></div>
          <el-button type="primary" size="small" @click="reload">上传附件</el-button>
        </el-form>
        <div class="drawer_footer">
          <el-button type="primary" @click="release" style="padding: 12px 40px;"> 发布 </el-button>
        </div>

        <el-drawer
          title="可见范围"
          :append-to-body="true"
          :before-close="handleClose2"
          :visible.sync="innerDrawer"
          custom-class="inner-teacher-drawer"
          size="40%"
        >
          <el-form :model="form">
            <el-form-item label="搜索" label-width="50px">
              <el-input v-model="form.title" autocomplete="off" style="width: 90%;"></el-input>
            </el-form-item>
            <el-form-item label="部门" label-width="50px">
              <el-cascader popper-class="pip_flow_mamager_cascader" style="width: 90%;" :props="props" :options="organizansList"  v-model="form.organizations"></el-cascader>
            </el-form-item>
            <el-form-item label="便捷操作" label-width="80px" style="margin-top: 50px;">
              <el-button type="primary">所有部门</el-button>
            </el-form-item>
            <div style="padding-left: 20px">
              <p>已选部门</p>
              <div style="display: flex; flex-wrap: wrap;">
                <el-tag
                  v-for="(item, index) in selecttags"
                  :key="index"
                  closable
                  :type="item.type"
                  @close="deleteTag(item)"
                  style="margin-right: 10px;color: #fff;background-color: #409EFF;position: relative;"
                >
                  @{{item.name}}
                  <svg t="1591152186700" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="3250" width="20" height="20"><path d="M544 64C279.04 64 64 279.04 64 544S279.04 1024 544 1024 1024 808.96 1024 544 808.96 64 544 64zM544 960C314.24 960 128 773.76 128 544S314.24 128 544 128C773.76 128 960 314.24 960 544S773.76 960 544 960zM588.16 540.8l168.32-166.4c12.8-12.8 12.8-33.28 0-45.44-12.8-12.8-33.28-12.8-46.08 0L542.08 494.08 376.96 328.32c-12.8-12.8-33.28-12.8-45.44 0-12.8 12.8-12.8 33.28 0 45.44l164.48 165.76-174.72 172.8c-12.8 12.8-12.8 33.28 0 45.44 12.8 12.8 33.28 12.8 46.08 0l174.08-172.16 171.52 172.16c12.8 12.8 33.28 12.8 45.44 0 12.8-12.8 12.8-33.28 0-45.44L588.16 540.8z" p-id="3251" fill="#cdcdcd"></path></svg>
                </el-tag>
              </div>
            </div>
          </el-form>
        </el-drawer>
      </div>
    </el-drawer>

</div>

<div id="app-init-data-holder" data-school="{{ session('school.id') }}"></div>
@endsection
