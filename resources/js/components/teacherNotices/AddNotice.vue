<template>
  <div class="drawer_content">
    <el-form :model="form" label-width="55px" :rules="rules" ref="ruleForm">
      <el-form-item label="标题" prop="title">
        <el-input v-model="form.title" autocomplete="off" maxlength="30" show-word-limit></el-input>
      </el-form-item>
      <el-form-item label="内容" prop="textarea">
        <el-input type="textarea" :rows="4" placeholder="请输入通知内容" v-model="form.textarea" maxlength="500" show-word-limit></el-input>
      </el-form-item>
    </el-form>
    <div class="selectBlock" style="border-top: 1px solid #EAEDF2">
      <div>选择教师可见范围</div>
      <div class="dayu" @click="innerDrawer = true; showOrganizationsSelectorFlag = true;">
        <template v-if="form.teacherTags == '0'">
          <!-- <el-tag class="Oran-Tag" style>所有部门</el-tag> -->
          <span>所有部门</span>
        </template>
        <template v-else-if="form.teacherTags.length">
          <!-- <el-tag class="Oran-Tag">已选择</el-tag> -->
          <span>已选择</span>
        </template>
        <i class="el-icon-arrow-right" style="font-size: 20px;"></i>
      </div>
    </div>
    <div class="selectBlock">
      <div>选择学生可见范围</div>
      <div class="dayu" @click="innerDrawer = true; showOrganizationsSelectorFlag = false;">
        <template v-if="form.studentTags == '0'">
          <span>所有班级</span>
        </template>
        <template v-else-if="form.studentTags.length">
          <span>已选择</span>
        </template>
        <i class="el-icon-arrow-right" style="font-size: 20px;"></i>
      </div>
    </div>
    <div class="fujian">
      <p class="text1">
        附件
        <span class="text2">(图片格式)</span>
      </p>
      <div class="fileList">
        <ul>
          <li v-for="(file, index) in form.files" :key="file.id">
            <a :href="file.url">{{file.file_name}}</a>
            <i @click="deleteFile(index)" class="el-icon-close"></i>
          </li>
        </ul>
      </div>
      <el-button type="primary" size="small" @click="showFileManager" style="margin-top: 10px">上传附件</el-button>
    </div>
    <div class="btn-tools">
      <el-button type="primary" @click="release" style="padding: 12px 40px;">发布</el-button>
    </div>
    <el-drawer
      title="可见范围"
      :append-to-body="true"
      :before-close="handleClose"
      :visible.sync="innerDrawer"
      custom-class="inner-teacher-drawer"
      size="60%"
    >
      <div style="padding:0 20px;">
        <VisibleRangeForT @confrim="confrimT" v-show="showOrganizationsSelectorFlag" />
        <VisibleRangeForS
          :school-id="schoolId"
          @confrim="confrimS"
          v-show="!showOrganizationsSelectorFlag"
        />
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
        v-on:pick-this-file="pickFileHandler"
      ></file-manager>
    </el-drawer>
  </div>
</template>
<script>
import { Util } from "../../common/utils";
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
      innerDrawer: false,
      form: {
        title: "",
        textarea: "",
        teacherTags: [],
        studentTags: [],
        files: ""
      },
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
      }
    };
  },

  methods: {
    handleClose(done) {
      done();
    },
    showFileManager() {
      this.showFileManagerFlag = true;
    },
    release() {
      this.$refs["ruleForm"].validate(valid => {
        console.log("发布通知");
        if (valid) {
          let params = {
            title: this.form.title,
            content: this.form.textarea,
            attachments: (this.form.files||[]).map(e => {
              return {
                media_id: e.id,
                file_name: e.file_name,
                url: e.url
              };
            })
          };
          if (this.form.teacherTags == "0" || this.form.teacherTags.length) {
            params.organization_id =
              this.form.teacherTags == 0
                ? [0]
                : this.form.teacherTags.map(e => e.id);
          }
          if (this.form.studentTags == "0" || this.form.studentTags.length) {
            params.grade_id =
              this.form.studentTags == 0
                ? [0]
                : this.form.studentTags.map(e => e.id);
          }
          if (!(params.organization_id) && !(params.grade_id)) {
            this.$message({
              message: '请选择可见范围',
              type: "warning"
            });
            return
          }
          // if (!(params.organization_id === 0 || params.organization_id)) {
          //   this.$message({
          //     message: '请选择教师可见范围',
          //     type: "warning"
          //   });
          //   return
          // }
          // if (!(params.grade_id === 0 || params.grade_id)) {
          //   this.$message({
          //     message: '请选择学生可见范围',
          //     type: "warning"
          //   });
          //   return
          // }
          axios.post("/api/notice/issue-notice", params).then(res => {
            if (Util.isAjaxResOk(res)) {
              this.$message({
                message: "发布成功！正在刷新数据...",
                type: "success"
              });
              window.location.reload();
            } else {
              this.$message({
                message: res.data.message,
                type: "error"
              });
              // window.location.reload()
            }
          });
        }
      });
    },
    pickFileHandler(payload) {
      console.log("pickFileHandler", payload);
      if (!this.form.files) this.form.files = [];
      if (!this.form.files.find(e => e.id == payload.file.id)) {
        this.form.files.push(payload.file);
      }
      this.showFileManagerFlag = false;
    },
    confrimT(value) {
      // console.log('confrimT')
      this.form.teacherTags = value;
      this.innerDrawer = false;
    },
    confrimS(value) {
      this.form.studentTags = value;
      this.innerDrawer = false;
    },
    deleteFile(index) {
      this.form.files.splice(index, 1);
    }
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
  border-bottom: 1px solid #eaedf2;
  padding: 15px 0;
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
