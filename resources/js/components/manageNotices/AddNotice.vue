<template>
  <div class="drawer_content">
    <div class="card-body p-3">
      <el-form ref="noticeForm" :model="notice" label-width="80px">
          <!-- <div>
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
          </div> -->
          <el-form-item label="可见范围" style="border-top: 1px solid #EAEDF2;border-bottom: 1px solid #eaedf2;">
            <div class="selectBlock">
              <el-button type="primary" size="mini" icon="el-icon-document" v-on:click="tDrawerOpen(notice.organization)">选择教师可见范围</el-button>
              <!-- <div>选择教师可见范围</div> -->
              <div class="dayu">
                <template v-if="teacherTags == 0">
                  <span>所有部门</span>
                </template>
                <template v-else-if="teacherTags == 1">
                  <span>已选择</span>
                </template>
                <i class="el-icon-arrow-right" style="font-size: 20px;"></i>
              </div>
            </div>
            <div class="selectBlock">
              <el-button type="primary" size="mini" icon="el-icon-document" v-on:click="sDrawerOpen(notice.grade)">选择学生可见范围</el-button>
              <!-- <div>选择学生可见范围</div> -->
              <div class="dayu">
                <template v-if="studentTags == 0">
                  <span>所有班级</span>
                </template>
                <template v-else-if="studentTags == 1">
                  <span>已选择</span>
                </template>
                <i class="el-icon-arrow-right" style="font-size: 20px;"></i>
              </div>
            </div>
          </el-form-item>
          <el-form-item label="类型">
              <el-select v-model="notice.type" placeholder="请选择类型">
                  <el-option v-for="(ty, idx) in types" :label="ty" :value="idx" :key="idx"></el-option>
              </el-select>
              <!-- <el-select v-show="showInspectTypesSelectorFlag"
                      v-model="notice.inspect_id"
                      placeholder="请选择检查类型">
                  <el-option
                          v-for="item in inspectTypes"
                          :key="item.id"
                          :label="item.name"
                          :value="item.id">
                  </el-option>
              </el-select> -->
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
                      type="datetime"
                      format="yyyy-MM-dd HH:mm:ss"
                      value-format="yyyy-MM-dd HH:mm:ss"
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
                  <p class=" mb-4" v-for="(atta, idx) in notice.attachments" :key="idx">
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

    <el-drawer
      title="可见范围"
      :append-to-body="true"
      :before-close="handleClose"
      :visible.sync="innerDrawer"
      custom-class="inner-teacher-drawer"
      size="50%"
    >
      <div style="padding:0 20px;">
        <VisibleRangeForT @confrim="confrimT" v-show="showOrganizationsSelectorFlag" :school-id="schoolId" ref="tDrawer" />
        <VisibleRangeForS :school-id="schoolId" @confrim="confrimS" v-show="!showOrganizationsSelectorFlag" ref="sDrawer" />
      </div>
    </el-drawer>

    <el-drawer
      title="我的易云盘"
      :visible.sync="showFileManagerFlag"
      direction="rtl"
      size="100%"
      :append-to-body="true"
      custom-class="e-yun-pan internal-message"
    >
      <file-manager
        :user-uuid="userUuid"
        :allowed-file-types="[]"
        :pick-file="true"
        v-on:pick-this-file="pickFileHandler1"
      ></file-manager>
    </el-drawer>
    <el-drawer
      title="我的易云盘"
      :visible.sync="showAttachmentManagerFlag"
      direction="rtl"
      size="100%"
      :append-to-body="true"
      custom-class="e-yun-pan internal-message"
    >
      <file-manager
        :user-uuid="userUuid"
        :allowed-file-types="[]"
        :pick-file="true"
        v-on:pick-this-file="pickFileHandler2"
      ></file-manager>
    </el-drawer>
  </div>
</template>
<script>
import { Util } from "../../common/utils";
import {Constants} from "../../common/constants";
import VisibleRangeForT from "./VisibleRangeForT";
import VisibleRangeForS from "./VisibleRangeForS";
export default {
  props: ["userUuid", "schoolId"],
  components: {
    VisibleRangeForT,
    VisibleRangeForS
  },
  data() {
    return {
      showOrganizationsSelectorFlag: false,
      showFileManagerFlag: false,
      showAttachmentManagerFlag: false,
      innerDrawer: false,
      form: {
        title: "",
        textarea: "",
        teacherTags: [],
        studentTags: [],
        files: ""
      },
      teacherTags: 2,
      studentTags: 2,
      rules: {
        title: [
          { required: true, message: "请输入标题", trigger: "blur" },
          { min: 1, max: 50, message: "请输入标题", trigger: "blur" }
        ],
        textarea: [
          { required: true, message: "请输入内容", trigger: "blur" },
          { min: 2, message: "请输入内容", trigger: "blur" }
        ],
        files: [{ required: true, message: "请选择附件" }]
      },
      notice:{
        id:'',
        // school_id:'',
        title:'',
        content:'',
        image:'',
        release_time:'',
        note:'',
        inspect_id:'',
        type:'1',
        user_id:'',
        status:false,
        attachments:[],
        // selectedOrganizations:[],
        organization: [],
        grade: [],
        organization_id: [],
        grade_id: []
      },
    };
  },
  created(){
      const dom = document.getElementById('app-init-data-holder');
      // this.notice.school_id = dom.dataset.school;
      this.types = JSON.parse(dom.dataset.types);
  },
  watch: {
    'notice.title': {
      handler(val) {
        if (val.length > 30) {
          this.$message({
            message: '最多输入30字！',
            type: "warning"
          });
        }
      }
    },
    'notice.content': {
      handler(val) {
        if (val.length > 500) {
          this.$message({
            message: '最多输入500字！',
            type: "warning"
          });
        }
      }
    }
  },
  methods: {
    tDrawerOpen(val) {
      this.innerDrawer = true; 
      this.showOrganizationsSelectorFlag = true;
      this.$nextTick(() => {
        this.$refs.tDrawer.thandleOpen(val)
      })
    },
    sDrawerOpen(val) {
      this.innerDrawer = true; 
      this.showOrganizationsSelectorFlag = false;
      this.$nextTick(() => {
        this.$refs.sDrawer.shandleOpen(val)
      })
    },
    addhandleOpen(val) {
      delete this.notice.selectedOrganizations
      delete this.notice.selected_organizations
      this.notice.id = '';
      this.notice.title = '';
      this.notice.type = '1';
      this.notice.content = '';
      this.notice.image = '';
      this.notice.release_time = '';
      this.notice.note = '';
      this.notice.inspect_id = '';
      this.notice.user_id = '';
      this.notice.status = false;
      this.notice.attachments = [];
      this.notice.organization_id = []
      this.notice.grade_id = []
      this.notice.organization = []
      this.notice.grade = []
      this.form.studentTags = []
      this.form.teacherTags = []
      this.teacherTags = 2
      this.studentTags = 2
    },
    handleOpen(val) {
      this.notice = val;
      this.notice.type = val.type + '';
      this.notice.status = this.notice.status === 1 ? true : false
      delete this.notice.selectedOrganizations
      delete this.notice.selected_organizations
      delete this.notice.school_id
      if (this.notice.organization.length > 0) {
        this.notice.organization[0].organization_id == 0 ? this.teacherTags = 0 : this.teacherTags = 1
      } else {
        this.teacherTags = 2
      }
      if (this.notice.grade.length > 0) {
        this.notice.grade[0].grade_id == 0 ? this.studentTags = 0 : this.studentTags = 1
      } else {
        this.studentTags = 2
      }
      this.form.studentTags = this.notice.grade
      this.form.teacherTags = this.notice.organization
      
    },
    handleClose(done) {
      // this.releaseDrawer = true
      this.$refs.tDrawer.initData()
      this.$refs.sDrawer.initData()
      done();
    },
    pickFileHandler1(payload) {
      console.log("pickFileHandler", payload);
      this.notice.image = payload.file.url;
      this.showFileManagerFlag = false;
    },
    pickFileHandler2(payload) {
      console.log("pickFileHandler", payload);
      this.notice.attachments.push(payload.file);
      this.showAttachmentManagerFlag = false;
    },
    confrimT(value) {
      this.form.teacherTags = value;
      if (this.form.teacherTags === '0') {
        this.teacherTags = 0
        this.notice.organization = [{name: "",organization_id: 0}]
      } else if (this.form.teacherTags.length > 0) {
        this.teacherTags = 1
        this.notice.organization = value
      } else {
        this.teacherTags = 2
        this.notice.organization = []
      }
      this.innerDrawer = false;
    },
    confrimS(value) {
      this.form.studentTags = value;
      if (this.form.studentTags === '0') {
        this.studentTags = 0
        this.notice.grade = [{name: "",grade_id: 0}]
      } else if (this.form.studentTags.length > 0) {
        this.studentTags = 1
        this.notice.grade = value
      } else {
        this.studentTags = 2
        this.notice.grade = []
      }
      this.innerDrawer = false;
    },
    deleteFile(index) {
      this.form.files.splice(index, 1);
    },

    onSubmit: function(){
      delete this.notice.grade
      delete this.notice.organization
      delete this.notice.scope
      if(this.notice.title.trim() === ''){
          this.$message.error('标题必须填写');
          return false;
      }
      if(Constants.NOTICE_TYPE_INSPECT === parseInt(this.notice.type)){
          if(this.notice.inspect_id === ''){
              this.$message.error('请指定检查的类型');
              return false;
          }
      }
      else{
          this.notice.inspect_id = '';
      }
      if (this.form.teacherTags == "0" || this.form.teacherTags.length) {
        this.notice.organization_id =
          this.form.teacherTags == 0
            ? [0]
            : this.form.teacherTags.map(e => e.id || e.organization_id);
      }
      if (this.form.studentTags == "0" || this.form.studentTags.length) {
        this.notice.grade_id =
          this.form.studentTags == 0
            ? [0]
            : this.form.studentTags.map(e => e.id || e.grade_id);
      }
      if ((this.notice.organization_id && this.notice.organization_id.length === 0) && (this.notice.grade_id && this.notice.grade_id.length === 0)) {
        this.$message({
          message: '请选择可见范围',
          type: "warning"
        });
        return
      }
      if (!this.notice.content) {
        this.$message({
          message: '请填写文字说明！',
          type: "warning"
        });
        return
      }
      if (this.notice.content.length > 500) {
        this.$message({
          message: '文字说明最多可输入500字！',
          type: "warning"
        });
        return
      }
      if (!this.notice.title) {
        this.$message({
          message: '请填写标题！',
          type: "warning"
        });
        return
      }
      if (this.notice.title.length > 30) {
        this.$message({
          message: '标题最多可输入30字！',
          type: "warning"
        });
        return
      }
      if (!this.notice.release_time) {
        this.$message({
          message: '请选择发布时间',
          type: "warning"
        });
        return
      }
      this.isLoading = true;
      axios.post(
          '/school_manager/notice/save-notice',
          {notice: this.notice}
      ).then(res => {
          if(Util.isAjaxResOk(res)){
              window.location.reload();
          }
          else{
              this.$message.error(res.data.message);
          }
          this.isLoading = false;
      })
    },
    deleteNoticeMedia: function(id){
        this.isLoading = true;
        axios.post(
            '/school_manager/notice/delete-media',
            {id: id}
        ).then(res => {
            if(Util.isAjaxResOk(res)){
                const idx = Util.GetItemIndexById(id, this.notice.attachments);
                this.notice.attachments.splice(idx, 1);
                this.$message({
                    type:'success',
                    message:'删除成功'
                });
            }
            else{
                this.$message.error(res.data.message);
            }
            this.isLoading = false;
        });
    },
  }
};
</script>

<style lang="scss" scoped>
.btn-tools {
  text-align: center;
  margin-top: 50px;
}
.selectBlock {
  display: flex;
  justify-content: space-between;
  // border-bottom: 1px solid #eaedf2;
  padding: 15px 10px;
}
.dayu {
  color: #ccc;
  // font-size: 32px;
  // border-bottom: 1px solid #ccc;
  cursor: pointer;
  // text-align: left;
  // padding-bottom: 10px;
}
.dayu:after {
  content: "";
  display: table;
  clear: both;
}
.fujian {
  padding: 15px 0;
  .text1 {
    margin-bottom: 10px;
  }
  .text2 {
    color: #90939a;
    font-size: 13px;
  }
}
</style>

<style >
.Oran-Tag {
  margin-right: 10px;
  color: #fff;
  background-color: #409eff;
  position: relative;
}
.Oran-Tag .el-tag .el-tag__close {
  color: #fff;
}
</style>
