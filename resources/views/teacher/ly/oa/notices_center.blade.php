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
            <div class="dayu" @click="showOrganizationsSelectorFlag=true">></div>
          </el-form-item>
          <el-form-item label="选择学生可见范围" label-width="130px">
            <div class="dayu" @click="innerDrawer = true">></div>
          </el-form-item>
          <el-form-item label="附件" label-width="50px" style="font-size: 16px; color: #000;">
            <div class="">(图片格式)</div>
          </el-form-item>
          <div></div>
          <el-button type="primary" size="small" @click="showAttachmentManagerFlag=true">上传附件</el-button>
        </el-form>
        <div class="drawer_footer">
          <el-button type="primary" @click="release" style="padding: 12px 40px;"> 发布 </el-button>
        </div>

        <!-- @include(
            'reusable_elements.section.file_manager_component',
            ['pickFileHandler'=>'pickFileHandler']
        )
        @include(
            'reusable_elements.section.file_manager_component',
            ['pickFileHandler'=>'pickAttachmentHandler','syncFlag'=>'showAttachmentManagerFlag']
        )
        @include(
            'reusable_elements.section.organizations_selector',
            ['organizationsSelectedHandler'=>'onOrganizationsSelectedHandler','schoolId'=>$schoolId, 'userRoles'=>$userRoles]
        ) -->

        <el-drawer
          title="可见范围"
          :append-to-body="true"
          :before-close="handleClose2"
          :visible.sync="innerDrawer"
          custom-class="inner-teacher-drawer"
          size="60%"
        >
          <el-form :model="form">
            <!-- <el-form-item label="搜索" label-width="50px">
              <el-input v-model="form.title" autocomplete="off" style="width: 90%;"></el-input>
            </el-form-item> -->
            <el-form-item label="部门" label-width="50px">
                <el-tree
                    ref="tree"
                    :props="props"
                    node-key="id"
                    :load="loadNode"
                    :check-on-click-node="true"
                    :check-strictly="true"
                    @check-change="checkChange"
                    lazy
                    show-checkbox>
                </el-tree>
            </el-form-item>
            <el-form-item label="便捷操作" label-width="80px" style="margin-top: 50px;">
              <el-switch
                v-model="allOran"
                active-text="所有部门"
                inactive-text="">
                </el-switch>
            </el-form-item>
            <div style="padding-left: 20px">
              <p>已选部门</p>
              <div style="display: flex; flex-wrap: wrap;">
                <el-tag
                  v-for="(item, index) in selecttags"
                  :key="index"
                  closable
                  @close="deleteTag(item)"
                  style="margin-right: 10px;color: #fff;background-color: #409EFF;position: relative;"
                >
                  @{{item.name}}</el-tag>
              </div>
            </div>
          </el-form>
        </el-drawer>
      </div>
    </el-drawer>

</div>

<div id="app-init-data-holder" data-school="{{ session('school.id') }}"></div>
@endsection
