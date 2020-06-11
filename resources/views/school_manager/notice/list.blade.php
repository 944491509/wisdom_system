@php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
@endphp

@extends('layouts.app')
@section('content')
<div class="row" id="notice-manager-app">
    <!-- <div class="col-sm-12 col-md-4 col-xl-4">
        <div class="card">
            <div class="card-head">
                <h4 class="text-center">通知申请表 <i class="el-icon-loading" v-if="isLoading"></i></h4>
            </div>

            <div class="card-body p-3">
                <el-form ref="noticeForm" :model="notice" label-width="80px">
                    <div>
                        <el-form-item label="可见范围" style="margin-bottom: 3px;">

                            <el-button type="primary" size="mini" icon="el-icon-document" v-on:click="showOrganizationsSelectorFlag=true">管理可见范围</el-button>
                        </el-form-item>
                        <el-form-item v-if="notice.selectedOrganizations.length > 0">
                            <el-tag
                                    v-for="item in notice.selectedOrganizations"
                                    :key="item.id"
                                    type="info"
                                    effect="plain"
                                    class="m-2"
                            >
                                @{{ item.name }}
                            </el-tag>
                        </el-form-item>
                        <el-divider></el-divider>
                    </div>
                    <el-form-item label="类型">
                        <el-select v-model="notice.type" placeholder="请选择类型">
                            <el-option v-for="(ty, idx) in types" :label="ty" :value="idx" :key="idx"></el-option>
                        </el-select>
                        <el-select v-show="showInspectTypesSelectorFlag"
                                v-model="notice.inspect_id"
                                placeholder="请选择检查类型">
                            <el-option
                                    v-for="item in inspectTypes"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id">
                            </el-option>
                        </el-select>
                    </el-form-item>

                    <el-form-item label="标题">
                        <el-input placeholder="必填: 标题" v-model="notice.title"></el-input>
                    </el-form-item>

                    <el-form-item label="发布">
                        <el-switch
                                v-model="notice.status"
                                active-text="发布"
                                inactive-text="暂不发布">
                        </el-switch>
                    </el-form-item>

                    <el-form-item label="文字说明">
                        <el-input rows="5" placeholder="选填: 通知内容" type="textarea" v-model="notice.content"></el-input>
                    </el-form-item>
                    <el-form-item label="发布日期">
                        <el-date-picker
                                v-model="notice.release_time"
                                type="date"
                                format="yyyy-MM-dd"
                                value-format="yyyy-MM-dd"
                                placeholder="选择日期">
                        </el-date-picker>
                    </el-form-item>

                    <div>
                        <el-form-item label="封面图片">
                            <el-button type="primary" size="mini" icon="el-icon-document" v-on:click="showFileManagerFlag=true">选择封面图片</el-button>
                        </el-form-item>
                        <div v-if="notice.image">
                            <p class="text-center mb-4">
                                <img :src="notice.image" width="200">
                            </p>
                        </div>
                    </div>

                    <div>
                        <el-form-item label="附件">
                            <el-button size="mini" icon="el-icon-document" v-on:click="showAttachmentManagerFlag=true">选择附件</el-button>
                        </el-form-item>
                        <div v-if="notice.attachments && notice.attachments.length > 0">
                            <p class="text-center mb-4" v-for="(atta, idx) in notice.attachments" :key="idx">
                                <span class="">
                                    <a :href="atta.url" target="_blank">附件@{{ idx + 1 }}: @{{ atta.file_name }}</a>
                                </span>
                                <el-button type="text" class="pt-1 text-danger pull-right" @click="deleteNoticeMedia(atta.id)">删除</el-button>
                            </p>
                        </div>
                    </div>

                    <el-form-item>
                        <el-button type="primary" @click="onSubmit">立即保存</el-button>
                        <el-button>取消</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
        @include(
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
        )
    </div> -->
    <div class="col-sm-12 col-md-12 col-xl-12">
        <div class="card">
            <div style="margin-top: 20px;margin-left: 10px;" class="aa">
              <el-form :inline="true" :model="screen" class="">
                <el-form-item label="类型">
                  <el-select v-model="screen.type" placeholder="请选择类型" clearable>
                    <el-option label="通知" value="1"></el-option>
                    <el-option label="公告" value="2"></el-option>
                    <el-option label="检查" value="3"></el-option>
                  </el-select>
                </el-form-item>
                <el-form-item label="接收对象">
                  <el-select v-model="screen.range" placeholder="请选择接收对象" clearable>
                    <el-option label="教师" value="1"></el-option>
                    <el-option label="学生" value="2"></el-option>
                  </el-select>
                </el-form-item>
                <el-form-item label="发布时间">
                <el-date-picker
                  v-model="screen.start_time"
                  type="date"
                  value-format="yyyy-MM-dd"
                  placeholder="起始时间">
                </el-date-picker>
                至
                <el-date-picker
                  v-model="screen.end_time"
                  type="date"
                  value-format="yyyy-MM-dd"
                  placeholder="结束时间">
                </el-date-picker>
                </el-form-item>
                <el-form-item label="">
                  <el-input v-model="screen.keyword" placeholder="请输入标题"></el-input>
                </el-form-item>
                <el-form-item>
                  <el-button type="primary" @click="getTableList">查询</el-button>
                </el-form-item>
                <el-form-item style="float: right;">
                  <el-button class="pull-right" type="primary" @click="newNotice">添加</el-button>
                </el-form-item>
              </el-form>
            </div>
            <div class="card-body">
              <div class="row">
                <el-table
                  :data="tableData.table"
                  stripe
                  height="650"
                  style="width: 100%">
                  <template v-for="(item, index) in tableData.tableHolder">
                    <el-table-column
                      v-if="item.type === 1"
                      :prop="item.prop"
                      :label="item.label"
                      :width="item.width">
                      <template slot-scope="scope">
                        <div v-for="(hold, i) in scope.row[item.prop]">@{{ hold }}</div>
                      </template>
                    </el-table-column>
                    <el-table-column
                      v-else-if="item.type === 2"
                      :prop="item.prop"
                      :label="item.label"
                      :width="item.width">
                      <template slot-scope="scope">
                        <div v-for="(hold, i) in scope.row[item.prop]" :class="i === 1 ? 'borTop' : '' ">@{{ hold }}</div>
                      </template>
                    </el-table-column>
                    <el-table-column
                      v-else
                      :prop="item.prop"
                      :label="item.label"
                      :width="item.width">
                    </el-table-column>
                  </template>
                  <el-table-column
                    fixed="right"
                    label="操作"
                    width="100">
                    <template slot-scope="scope">
                      <el-button type="text" size="small" icon="el-icon-edit" @click="edit(scope.row.id)" style="font-size: 20px;"></el-button>
                      <el-button type="text" size="small" icon="el-icon-delete" @click="deleteNotice(scope.row.id)" style="font-size: 20px;"></el-button>
                    </template>
                  </el-table-column>
                </el-table>
              </div>
              <page :getpagedata.sync="tableData" v-on:get-list-fun="getTableList"></page>
              <!-- <div class="pageBlock" style="margin-top: 20px;">
                <el-pagination
                  layout="prev, pager, next, total"
                  :page-size="10"
                  @current-change="handleUrl"
                  :total="1000">
                </el-pagination>
              </div> -->
                <!-- <div class="row">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                            <tr>
                                <th>可见范围</th>
                                <th>标题</th>
                                <th>类型</th>
                                <th>封面图片</th>
                                <th>发布时间</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($data as $val)
                                <tr>
                                    <td>
                                        @foreach($val->selectedOrganizations as $so){{ $so->organization->name??'全部' }}
                                        @endforeach
                                        @if($val->selectedOrganizations->count()===0)
                                            全部
                                        @endif
                                    </td>
                                    <td>{{ $val->title }}</td>
                                    <td>
                                        {{ $val->getTypeText() }}
                                        {{ $val->getInspectTypeText() }}
                                    </td>
                                    <td>
                                        <img src="{{ $val->image }}" width="200">
                                    </td>
                                    <td>{{ _printDate($val->release_time) }}</td>
                                    <td>
                                        @if($val['status'] == 1)
                                        <span class="label label-sm label-success"> 已发布 </span>
                                            @else
                                        <span class="label label-sm label-danger"> 暂不发布 </span>
                                        @endif
                                    </td>
                                     <td class="text-center">
                                         <el-button size="mini" icon="el-icon-edit" @click="edit({{ $val }})"></el-button>
                                         <el-button type="danger" size="mini" icon="el-icon-delete" @click="deleteNotice({{ $val->id }})"></el-button>
                                     </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $data->links() }}
                </div> -->
            </div>
        </div>
    </div>
    <!-- @open="handleOpen({{ $val }})" -->
    <el-drawer
      title="添加"
      :before-close="handleClose"
      :visible.sync="releaseDrawer"
      custom-class="demo-drawer"
      size="50%"
      style="overflow: auto;"
      >
        <add-notice :user-uuid="userUuid" :school-id="notice.schoolId" ref="childDrawer" />
    </el-drawer>
</div>
<style>
    #notice-manager-app .el-drawer__body{
        overflow-y:auto;
    }
    .aa .el-form-item {
      margin-bottom: 0;
    }
    .borTop {
      border-top: 1px dashed #000;
    }
</style>
<div id="app-init-data-holder"
     data-school="{{ session('school.id') }}"
     data-types="{{ json_encode(\App\Models\Notices\Notice::allType()) }}"
     data-inspecttypes="{{ json_encode($inspect_types->toArray()) }}"
></div>
@endsection
