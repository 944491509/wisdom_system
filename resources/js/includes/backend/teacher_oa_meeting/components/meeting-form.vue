<template>
  <div class="task-form">
    <div class="form">
      <el-form ref="form" :model="form" label-width="120px">
        <el-form-item class="is-required" label="会议主题">
          <el-input v-model="form.meet_title" placeholder="请输入会议主题"></el-input>
        </el-form-item>
        <el-form-item label="会议时间">
          <date-time-range v-model="form.timeRange" />
        </el-form-item>
        <el-form-item label="会议地点">
          <el-select v-model="form.room" placeholder="请输入">
            <el-option
              v-for="item in addressOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item class="is-required" label="会议负责人">
          <el-select
            v-model="form.approve_userid"
            remote
            filterable
            :remote-method="remoteLeader"
            placeholder="请输入（必填）"
          >
            <el-option
              v-for="item in leaderOptions"
              :disabled="item.disabled"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item class="is-required" label="参会人">
          <el-input
            placeholder="请输入"
            :value="
              form.user.length > 0
                ? form.user.length + '人'
                : ''
            "
            @click.native="goSetMember"
            readonly
          ></el-input>
        </el-form-item>
        <el-form-item label="会议开始签到">
          <el-radio-group v-model="form.signin_status">
            <el-radio :label="0">不需要</el-radio>
            <el-radio :label="1">需要</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="开始签到时间" v-if="form.signin_status">
          <date-time-range v-model="form.signinRange" />
        </el-form-item>
        <el-form-item label="会议结束签退">
          <el-radio-group v-model="form.signout_status">
            <el-radio :label="0">不需要</el-radio>
            <el-radio :label="1">需要</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="结束签退时间" v-if="form.signout_status">
          <date-time-range v-model="form.signoutRange" />
        </el-form-item>
        <el-form-item label="会议说明">
          <el-input type="textarea" v-model="form.meet_content" placeholder="请输入会议内容"></el-input>
        </el-form-item>
        <el-form-item label>
          <div class="file-box" v-for="(file, index) in filelist" :key="index">
            <div class="name">{{file.name}}</div>
            <div class="info">
              <span class="delete" @click="removeFile(index)">删除</span>
              <span class="size">{{file.size}}</span>
            </div>
          </div>
          <div class="add-files">
            <label for="fileInput">
              <i class="el-icon-paperclip"></i> 添加附件
            </label>
            <input
              type="file"
              id="fileInput"
              @change="onFileSelected"
              hidden
              ref="referenceUpload"
              multiple="multiple"
              style="display: none"
            />
          </div>
        </el-form-item>
      </el-form>
      <div class="btn-box">
        <el-button type="primary" @click="onSubmit">立即创建</el-button>
      </div>
    </div>
    <member-select @onSelect="setMemberList" class="member-select" :class="{'vie':selectMb}"></member-select>
  </div>
</template>
<script>
import { MeetingApi, addMeeting } from "../common/api";
import { Util } from "../../../../common/utils";
// import MemberSelect from "./member-chose";
import MemberSelect from "../../teacher_oa_tasks/components/setmember";
import { searchMemberDebounce } from "../../teacher_oa_tasks/common/utils";
import { deepClone } from "../common/utils";
import { converSize } from "../../teacher_oa_message/common/utils";
import moment from "moment";
import DateTimeRange from "./date-time-range";

export default {
  name: "task-form",
  components: {
    MemberSelect,
    DateTimeRange
  },
  methods: {
    onSubmit() {
      if (!this.form.meet_title) {
        this.$message.error("请输入会议主题");
        return;
      }
      if (!this.form.approve_userid) {
        this.$message.error("请选择负责人");
        return;
      }
      if (!this.form.user || this.form.user.length < 1) {
        this.$message.error("请选择参会人员");
        return;
      }
      if (this.form.signin_status && !this.form.signinRange) {
        this.$message.error("请选择签到时间");
      }
      if (this.form.signout_status && !this.form.signoutRange) {
        this.$message.error("请选择签退时间");
        return;
      }
      if (!this.form.meet_content) {
        this.$message.error("请填写会议说明");
        return;
      }
      let formdata = deepClone(this.form);
      formdata.meet_start = this.form.timeRange[0];
      formdata.meet_end = this.form.timeRange[1];
      if (this.form.signin_status) {
        formdata.signin_start = this.form.signinRange[0];
        formdata.signin_end = this.form.signinRange[1];
      }
      if (this.form.signout_status) {
        formdata.signout_start = this.form.signoutRange[0];
        formdata.signout_end = this.form.signoutRange[1];
      }
      delete formdata.signoutRange;
      delete formdata.signinRange;
      delete formdata.timeRange;
      delete formdata.user;
      formdata.type = 1;
      let form = new FormData();
      form.append("type", 1);
      Object.keys(formdata).forEach(key => {
        form.append(key, formdata[key]);
      });
      if (this.filelist.length > 0) {
        this.filelist.forEach(file => {
          form.append("file[]", file.file);
        });
      }
      this.form.user.forEach(id => {
        form.append("user[]", id);
      });
      addMeeting(form).then(res => {
        if (res.data && res.data.code == 1000) {
          this.$emit("done");
        } else {
          this.$message.error((res.data && res.data.message) || "");
        }
      });
    },
    goSetMember() {
      this.selectMb = true;
    },
    setMemberList(list) {
      this.form.user = list;
      this.selectMb = false;
    },
    remoteLeader(val) {
      searchMemberDebounce(val, res => {
        if (Util.isAjaxResOk(res)) {
          this.memberListSearch = res.data.data.members;
          this.leaderOptions = res.data.data.members.map(per => {
            return {
              label: per.name,
              value: per.id
            };
          });
        }
      });
    },
    onFileSelected(e) {
      for (let index = 0; index < e.target.files.length; index++) {
        const file = e.target.files[index];
        this.filelist.push({
          name: file.name,
          size: converSize(file.size),
          file
        });
      }
      this.$refs.referenceUpload.value = null;
    },
    removeFile(index) {
      this.filelist.splice(index, 1);
    }
  },
  data() {
    let that = this;
    return {
      form: {
        user: [],
        signout_status: 0,
        signin_status: 0,
        meet_content: ""
      },
      selectMb: false,
      leaderOptions: [],
      addressOptions: [],
      currentUserId: 0,
      filelist: []
    };
  },
  created() {
    MeetingApi.excute("getMeetRoomList", {}, { methods: "get" }).then(res => {
      this.addressOptions = res.data.data.map(room => {
        return {
          label: room.name,
          value: room.building_id
        };
      });
    });
    this.currentUserId = this.$attrs.currentuserid;
  }
};
</script>
<style lang="scss" scoped>
.form {
  display: flex;
  height: 100%;
  flex-direction: column;

  .el-form {
    flex: 1;
    padding: 12px;

    .el-select {
      width: 100%;
    }

    .el-form-item__label {
      font-weight: bold;
      color: #666666;
    }

    .el-dropdown {
      width: 100%;
    }

    .el-date-editor {
      width: 100%;
    }

    .add-files {
      color: #4ea5fe;
      display: flex;
      align-items: center;
      cursor: pointer;
      label {
        cursor: pointer;
      }
      i {
        font-size: 20px;
      }
    }
  }

  .btn-box {
    flex: none;
    padding: 12px;
    text-align: center;
  }
}

.file-box {
  display: flex;
  flex-direction: row;
  padding: 2px 12px;
  background: #f3f9ff;
  margin-bottom: 8px;
  line-height: 30px;
  .name {
    flex: auto;
    align-self: center;
    color: #666666;
    /* white-space: nowrap; */
    text-overflow: ellipsis;
    overflow: hidden;
    word-break: break-all;
    padding-right: 24px;
  }
  .info {
    flex: 1;
    flex-direction: column;
    display: flex;
    text-align: right;
    .delete {
      cursor: pointer;
      color: #409eff;
      font-size: 14px;
    }
    .size {
      font-size: 12px;
      color: #cccccc;
    }
  }
}
.task-form {
  position: relative;
  height: 100%;
  .member-select {
    position: absolute;
    top: 0;
    right: -100%;
    width: 100%;
    transition: right 0.4s;
    background-color: #ffffff;
  }
  .member-select.vie {
    right: 0;
  }
}
</style>
