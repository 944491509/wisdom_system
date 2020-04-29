<template>
  <div class="task-form">
    <div class="form">
      <el-form ref="form" :model="form" label-width="120px">
        <el-form-item class="is-required" label="会议主题">
          <el-input v-model="form.meet_title" placeholder="请输入会议主题"></el-input>
        </el-form-item>
        <el-form-item label="会议时间">
          <el-date-picker
            v-model="form.timeRange"
            type="datetimerange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          ></el-date-picker>
        </el-form-item>
        <el-form-item label="会议地点">
          <el-select v-model="form.room_id" placeholder="请输入">
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
              form.member_userids.length > 0
                ? form.member_userids.length + '人'
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

        <el-form-item label="开始签到时间">
          <el-date-picker
            v-model="form.signinRange"
            type="datetimerange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          ></el-date-picker>
        </el-form-item>
        <el-form-item label="会议结束签退">
          <el-radio-group v-model="form.signout_status">
            <el-radio :label="0">不需要</el-radio>
            <el-radio :label="1">需要</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="结束签退时间">
          <el-date-picker
            v-model="form.signoutRange"
            type="datetimerange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          ></el-date-picker>
        </el-form-item>

        <el-form-item label="会议说明">
          <el-input type="textarea" v-model="form.meet_content" placeholder="请输入会议内容"></el-input>
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
import { MeetingApi } from "../common/api";
import { Util } from "../../../../common/utils";
// import MemberSelect from "./member-chose";
import MemberSelect from "../../teacher_oa_tasks/components/setmember";
import { searchMemberDebounce } from "../../teacher_oa_tasks/common/utils";
import moment from "moment";

export default {
  name: "task-form",
  components: {
    MemberSelect
  },
  methods: {
    onSubmit() {
      if (!this.form.task_title) {
        this.$message.error("请输入任务标题");
        return;
      }
      if (!this.form.end_time) {
        this.$message.error("请选择任务时间");
        return;
      }
      if (!this.form.leader_userid) {
        this.$message.error("请选择负责人");
        return;
      }
      if (!this.form.member_userids || this.form.member_userids.length < 1) {
        this.$message.error("请选择任务成员");
        return;
      }
      let formdata = JSON.parse(JSON.stringify(this.form));
      formdata.end_time = moment(formdata.end_time).format(
        "YYYY-MM-DD hh:mm:ss"
      );
      formdata.member_userids = formdata.member_userids.toString();
      MeetingApi.excute("addOaTaskInfo", formdata).then(res => {
        this.$emit("done");
      });
    },
    goSetMember() {
      this.selectMb = true;
    },
    setMemberList(list) {
      this.form.member_userids = list;
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
    }
  },
  data() {
    let that = this;
    return {
      form: {
        member_userids: []
      },
      selectMb: false,
      leaderOptions: [],
      addressOptions: [],
      currentUserId: 0
    };
  },
  created() {
    MeetingApi.excute("getMeetRoomList").then(res => {
      debugger
      this.addressOptions = res.data.data.map(pro => {
        return {
          label: pro.project_title,
          value: pro.projectid
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
  }

  .btn-box {
    flex: none;
    padding: 12px;
    text-align: center;
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
