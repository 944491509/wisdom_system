@php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
@endphp

@extends('layouts.app')
@section('content')
<div class="row" id="notice-manager-app">
    <div class="col-sm-12 col-md-12 col-xl-12">
        <div class="card">
            <div style="margin-top: 20px;margin-left: 10px;" class="aa">
              <el-form :inline="true" :model="screen" class="">
                <el-form-item label="类型">
                  <el-select v-model="screen.type" placeholder="请选择类型" clearable>
                    <el-option label="全部" value=""></el-option>
                    <el-option label="通知" value="1"></el-option>
                    <el-option label="公告" value="2"></el-option>
                    <el-option label="检查" value="3"></el-option>
                  </el-select>
                </el-form-item>
                <el-form-item label="接收对象">
                  <el-select v-model="screen.range" placeholder="请选择接收对象" clearable>
                    <el-option label="全部" value=""></el-option>
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
                      <span class="editIcon" @click="edit(scope.row.id)"><i class="el-icon-edit"></i></span>
                      <span class="deleteIcon"><i class="el-icon-delete" @click="deleteNotice(scope.row.id)"></i></span>
                      <!-- <el-button type="text" size="small" icon="el-icon-edit" @click="edit(scope.row.id)" style="font-size: 20px;"></el-button>
                      <el-button type="text" size="small" icon="el-icon-delete" @click="deleteNotice(scope.row.id)" style="font-size: 20px;"></el-button> -->
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
      border-top: 1px dashed #ccc;
    }
    .editIcon {
      padding: 1px 8px;
      border: 1px solid #ccc;
      border-radius: 3px;
      cursor: pointer;
    }
    .editIcon .el-icon-edit {
      font-size: 13px;
      color: #443f3f;
    }
    .deleteIcon {
      padding: 1px 8px;
      background-color: rgb(245,108,108);
      border: 1px solid rgb(245,108,108);
      border-radius: 3px;
      margin-left: 10px;
      cursor: pointer;
    }
    .deleteIcon .el-icon-delete {
      font-size: 13px;
      color: #fff;
    }
</style>
<div id="app-init-data-holder"
     data-school="{{ session('school.id') }}"
     data-types="{{ json_encode(\App\Models\Notices\Notice::allType()) }}"
     data-inspecttypes="{{ json_encode($inspect_types->toArray()) }}"
></div>
@endsection
