@extends('layouts.app')
@section('content')
<div id="teacher-assistant-material-app" class="material-box">
  <div class="teacher-container">
    <div class="blade_title">教学资料</div>
    <div class="blade_container" :class="{'bg-none':activeIndex === 3}">

      <!--我的课程/教学资料--->
      <div v-show="activeIndex === 1 || activeIndex == 2 " >
        <el-row  class="blade_container_tit">
          <el-col :span="24">课程/资料</el-col>
        </el-row>
        <el-row class="blade_container_tab">
          <el-col :span="12" class="table-left"><div @click="changeMeans(1)">我的课程</div></el-col>
          <el-col :span="12" class="table-right"><div @click="changeMeans(2)">教学资料</div></el-col>
        </el-row>
        <!--教学资料-->
        <el-collapse  v-model="activeNames" v-show="activeIndex === 2">
          <el-collapse-item v-for="(item,key) in myMaterialsList" :key="key" :name="key" >
            <template slot="title">
            <svg t="1586695842810" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1307" width="18" style="margin-left: 20px;
    margin-right: 7px;" ><path d="M893.614907 120.84472c-19.080745 0-31.801242 12.720497-31.801242 31.801242l0 737.78882c0 31.801242-31.801242 63.602484-63.602484 63.602484L238.509317 954.037267c-38.161491 0-82.68323-31.801242-82.68323-63.602484l0-69.962733c0-31.801242 38.161491-50.881988 76.322981-50.881988l534.26087 0c19.080745 0 31.801242-12.720497 31.801242-31.801242L798.21118 114.484472c0-69.962733-50.881988-114.484472-127.204969-114.484472L238.509317 0C168.546584 0 98.583851 38.161491 98.583851 114.484472l0 782.310559c0 0 0 0 0 6.360248 0 6.360248 0 6.360248 0 12.720497 6.360248 57.242236 63.602484 101.763975 120.84472 108.124224 0 0 6.360248 0 6.360248 0 0 0 0 0 6.360248 0l566.062112 0c69.962733 0 127.204969-57.242236 127.204969-120.84472L925.416149 152.645963C925.416149 139.925466 912.695652 120.84472 893.614907 120.84472zM772.770186 833.192547 251.229814 833.192547c-19.080745 0-31.801242 12.720497-31.801242 31.801242s12.720497 31.801242 31.801242 31.801242l521.540373 0c19.080745 0 31.801242-12.720497 31.801242-31.801242S791.850932 833.192547 772.770186 833.192547z" p-id="1308" fill="#4EA5FE"></path></svg>
              <span class="dataTitle">@{{item.name}}</span>
            </template>
            <el-row class="blade_container_box">
              <el-table :data="(item.list || [])" style="width: 100%" :show-header="false">
                <el-table-column>
                  <template slot-scope="scope">
                    <span class="cloumn-a">@{{ scope.row.desc }}</span>
                  </template>
                </el-table-column>
                <el-table-column>
                  <span class="cloumn-b">第25节课课前预习需要</span>
                </el-table-column>
                <el-table-column>
                  <template slot-scope="scope">
                      <div slot="reference" class="name-wrapper">
                        <el-tag v-for="(item,key) in scope.row.grades">@{{ item.grade_name }}</el-tag>
                      </div>
                  </template>
                </el-table-column>
                <el-table-column>
                  <template slot-scope="scope">
                  <!-- <el-button type="primary" icon="el-icon-edit" size="mini" class="button-edit"></el-button> -->
                  <el-button type="primary" size="mini"><a :href="scope.row.url" download style="color: #fff">下载</a></el-button>
                  <el-button size="mini" type="danger" @click="deleteRow(scope.row)">删除</el-button>
                  </template>
                </el-table-column>
              </el-table>
            </el-row>
          </el-collapse-item>
          <!-- <el-collapse-item title="反馈 Feedback" name="2">
            <div>控制反馈：通过界面样式和交互动效让用户可以清晰的感知自己的操作；</div>
            <div>页面反馈：操作后，通过页面元素的变化清晰地展现当前状态。</div>
          </el-collapse-item>
          <el-collapse-item title="效率 Efficiency" name="3">
            <div>简化流程：设计简洁直观的操作流程；</div>
            <div>清晰明确：语言表达清晰且表意明确，让用户快速理解进而作出决策；</div>
            <div>帮助用户识别：界面简单直白，让用户快速识别而非回忆，减少用户记忆负担。</div>
          </el-collapse-item>
          <el-collapse-item title="可控 Controllability" name="4">
            <div>用户决策：根据场景可给予用户操作建议或安全提示，但不能代替用户进行决策；</div>
            <div>结果可控：用户可以自由的进行操作，包括撤销、回退和终止当前操作等。</div>
          </el-collapse-item> -->
        </el-collapse>
        <!-- <el-tabs v-model="activeName" @tab-click="activeTable" v-show="activeIndex === 2" stretch>
          <el-tab-pane v-for="(item,key) in myMaterialsList" :label="item.name"></el-tab-pane>
        </el-tabs> -->

        <!--我的课程-->
        <el-row class="blade_container_box" v-show="activeIndex === 1">
          <div class="blade_container_cont" v-for="(item,key) in myCourseList">
            <h4 class="title">@{{ item.course_name }}（@{{ item.duration }}课时）</h4>
            <p class="content">@{{ item.desc }}</p>
            <div class="tags">
              <el-tag v-for="(item1,key1) in item.types" @click="showDrawer(key, key1)">@{{ item1.name }}@{{ item1.num }}</el-tag>
            </div>
            <el-row class="button">
              <el-button type="primary" icon="el-icon-edit" size="mini" class="button-edit" @click="changeMeans(3,item)"></el-button>
              <el-button type="primary" size="mini" @click="changeMeans(4,item)">添加资料</el-button>
            </el-row>
          </div>
        </el-row>
        <el-drawer
          :title="drawerTitle"
          :visible.sync="drawer"
          :before-close="handleClose"
          custom-class="drawerClass"
        >
            <el-table :data="drawerList" style="width: 100%" :show-header="false">
              <el-table-column>
                <template slot-scope="scope">
                  <span class="cloumn-a">@{{ scope.row.desc }}</span>
                </template>
              </el-table-column>
              <el-table-column>
                <span class="cloumn-b">第25节课课前预习需要</span>
              </el-table-column>
              <el-table-column>
                <template slot-scope="scope">
                    <div slot="reference" class="name-wrapper">
                      <el-tag v-for="(item,key) in scope.row.grades">@{{ item.grade_name }}</el-tag>
                    </div>
                </template>
              </el-table-column>
            </el-table>
        </el-drawer>
        <!--我的课程-->
        <!-- <el-row class="blade_container_box" v-show="activeIndex === 2">
         <el-table :data="myMaterialsListData" style="width: 100%">
           <el-table-column>
             <template slot-scope="scope">
               <span class="cloumn-a">@{{ scope.row.desc }}</span>
             </template>
           </el-table-column>
          <el-table-column>
            <span class="cloumn-b">第25节课课前预习需要</span>
          </el-table-column>
           <el-table-column>
             <template slot-scope="scope">
                 <div slot="reference" class="name-wrapper">
                   <el-tag v-for="(item,key) in scope.row.grades">@{{ item.grade_name }}</el-tag>
                 </div>
             </template>
           </el-table-column>
          <el-table-column>
            <template slot-scope="scope">
            <el-button type="primary" icon="el-icon-edit" size="mini" class="button-edit"></el-button>
            <el-button type="primary" size="mini">下载</el-button>
            <el-button size="mini" type="danger">删除</el-button>
            </template>
          </el-table-column>
          </el-table>
        </el-row> -->
      </div>

      <!--添加计划日志--->
      <div class="row" v-show="activeIndex === 3 " >
        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
          <div class="card">
            <div class="card-body">
              <h2>教学计划</h2>
              <hr>
              <Redactor v-model="notes.teacher_notes" placeholder="请输入内容" :config="configOptions" ></Redactor>
              <div class="mt-3" >
                <el-button type="primary" @click="saveNotes">保存</el-button>
              </div>
              <hr>
            </div>
          </div>
        </div>
        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
          <div class="card">
            <div class="card-body">
              <h2>教学日志 <el-button class="pull-right" type="primary" size="small" icon="el-icon-edit" @click="showLogEditorHandler()">写日志</el-button></h2>
              <hr>
              <div v-show="showLogEditor">
                <el-form :model="logModel" label-width="80px" class="course-form" style="margin-top: 20px;">
                  <el-form-item label="标题">
                    <el-input placeholder="必填: 标题" v-model="logModel.title"></el-input>
                  </el-form-item>
                  <el-form-item label="内容">
                    <el-input placeholder="必填: 日志内容" type="textarea" v-model="logModel.content"></el-input>
                  </el-form-item>
                  <el-button style="margin-left: 10px;" size="small" type="success" @click="saveLog">保存</el-button>
                  <el-button style="margin-left: 10px;" size="small" @click="showLogEditor=false">关闭</el-button>
                </el-form>
                <hr>
              </div>
              <div style="height:554px;overflow-y:auto;">
                <el-card class="box-card mb-4" v-for="log in logs" :key="log.id" shadow="hover">
                  <div class="text item pb-3">
                    <h4>标题: @{{ log.title }}</h4>
                    <p style="color: #ccc; font-size: 10px;">最后更新于: @{{ log.updated_at ? log.updated_at : '刚刚' }}</p>
                    <p>内容: @{{ log.content }}</p>
                    <el-button style="float: left;" size="mini" type="primary" @click="showLogEditorHandler(log)">编辑</el-button>
                    <el-button style="float: right;" size="mini" type="danger" @click="deleteLog(log)">删除</el-button>
                  </div>
                </el-card>
              </div>
            </div>
          </div>
        </div>
      </div>

    <div  v-if="activeIndex === 4 ">
        <material :course="course" :grades="grades" :lecture="lecture" v-if="lecture" :loading="loadingData" user-uuid="{{ $teacher->uuid }}"></material>
    </div>

    </div>
  </div>
</div>

<div id="app-init-data-holder"
     data-school="{{ session('school.id') }}"
     data-teacher='{!! $teacher !!}'
></div>
<style>
  .row {
    display: flex;
    flex-wrap: wrap;
    margin-right: -15px;
    margin-left: -15px;
  }
  .material-box{
    /* display: flex; */
    padding: 20px 10px;
    background: #fff;
    align-items: center;
    justify-content: space-around;
  }
  .teacher-container{
    /* flex: 1; */
    padding: 21px;
    border-radius: 10px;
    margin: 40px 10px 20px;
    background-color: #eaedf2;
  }
  .blade_container{
    background: #fff;
    border-radius: 10px;
    min-height: 700px;
  }
  .blade_container.bg-none{
    background:none;
  }
  .blade_container_tit{
    color: #414A5A;
    font-size: 24px;
    font-weight: bold;
    padding: 15px 30px;
    border-bottom: #EAEDF2 1px solid;
  }
  .blade_container_tab{
    height: 60px;
    font-size: 24px;
	cursor:pointer;
    font-weight: bold;
    text-align: center;
    line-height: 60px;
    color: rgba(78,165,254,1);
    border-bottom: #EAEDF2 1px solid;
  }
  .blade_container_cont{
    padding: 20px 30px;
    border-bottom: #EAEDF2 1px solid;

  }
  .blade_container_box .title{
    font-size: 14px;
    font-weight: bold;
    letter-spacing:1px;
    color:rgba(49,59,76,1);
  }
  .blade_container_box .content{
    line-height:30px;
    letter-spacing:1px;
    color:rgba(49,59,76,1);
  }
  .blade_container_cont .tags > span{
    margin: 10px 20px 10px 0px;
  }
  .blade_container_cont .button{
    margin: 10px 0px;
  }
  .blade_container_cont .button-edit{
    border: 0px;
    background-color:#FE7B1C;
  }
  .cloumn-a{
	cursor:pointer;
	font-size:14px;
	margin-left: 10px;
	font-family:MicrosoftYaHei;
	color:rgba(78,165,254,1);
	letter-spacing:1px;
	text-decoration:underline;
  }
  .cloumn-b{
	font-size:14px;
	font-family:MicrosoftYaHei-Bold,MicrosoftYaHei;
	font-weight:bold;
	color:rgba(49,59,76,1);
	letter-spacing:1px;
  }
  .dataTitle {
    font-size: 16px;
    font-weight: 600;
  }
  .name-wrapper > span {
    margin-left: 10px;
    margin-bottom: 5px;
  }
  .redactor-box .redactor-styles{
    height: 400px;
    overflow-y: auto;
  }
  .drawerClass {
    width: 50% !important;
  }
</style>

@endsection
