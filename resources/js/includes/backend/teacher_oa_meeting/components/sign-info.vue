<template>
  <div v-if="isCreateByMe" style="height: 100%">
    <div class="record-stat">
      <div class="sign-panel statistic">
        <div class="stat">
          <div class="title">未签到</div>
          <div class="count">{{signStat.un_sign_in}}</div>
        </div>
        <div class="stat">
          <div class="title">按时签到</div>
          <div class="count checked">{{signStat.sign_in}}</div>
        </div>
        <div class="stat">
          <div class="title">迟到</div>
          <div class="count late">{{signStat.late}}</div>
        </div>
        <div class="stat">
          <div class="title">未签退</div>
          <div class="count">{{signStat.un_sign_out}}</div>
        </div>
        <div class="stat">
          <div class="title">按时签退</div>
          <div class="count checked">{{signStat.sign_out}}</div>
        </div>
      </div>
    </div>
    <div class="sign-record">
      <div class="record-title">签到记录</div>
      <div class="sign-record-list">
        <div class="sign-list">
          <div class="sign-item" v-for="(sign, index) in (record.list || [])" :key="index">
            <div class="avatar-box">
              <avatar :src="sign.avatar" />
            </div>
            <span class="name">{{sign.user_name}}</span>
            <div class="sign-desc" :style="!detail.signin_time?{visibility: 'hidden'}:{}">
              <div class="sign-title">会前</div>
              <div
                class="status"
                :class="{'checked': sign.signin_status === 1, 'late': sign.signin_status === 2}"
              >{{sign.signin_status?(sign.signin_status===1?'已签到':'迟到'):'未签到'}}</div>
            </div>
            <div class="sign-desc" :style="!detail.signout_time?{visibility: 'hidden'}:{}">
              <div class="sign-title">会后</div>
              <div
                class="status"
                :class="{'checked': sign.signout_status}"
              >{{sign.signout_status?'已签退':'未签退'}}</div>
            </div>
          </div>
        </div>
      </div>
      <el-pagination
        background
        layout="prev, pager, next"
        :page-count="record.lastPage"
        :current-page="page"
        @current-change="onPageChange"
      ></el-pagination>
    </div>
  </div>
  <div v-else>
    <div class="sign-panel sign-in">
      <div class="title">会前</div>
      <div class="info">
        <div
          class="status"
          :class="{'signed': signInfo.signin_status===1,'late': signInfo.signin_status===2}"
        >{{signinTextMap[signInfo.signin_status]}}</div>
        <div class="time" v-if="signInfo.signin_status">{{signInfo.signin_time}}</div>
      </div>
    </div>
    <div class="sign-panel sign-out">
      <div class="title">会后</div>
      <div class="info">
        <div
          class="status"
          :class="{'signed': signInfo.signout_status}"
        >{{signInfo.signout_status?'已签退': '未签退'}}</div>
        <div class="time" v-if="signInfo.signout_status">{{signInfo.signout_time}}</div>
      </div>
    </div>
  </div>
</template>
<script>
import { MeetingMode } from "../common/enum";
import { MeetingApi } from "../common/api";
import Avatar from "../../teacher_oa_message/components/avatar";

export default {
  name: "SignInfo",
  components: {
    Avatar
  },
  data() {
    return {
      type: "",
      meetid: "",
      signInfo: {},
      signStat: {},
      record: [],
      page: 1,
      detail: {},
      signinTextMap: {
        0: "未签到",
        1: "按时签到",
        2: "迟到"
      }
    };
  },
  computed: {
    isCreateByMe() {
      return this.type === MeetingMode.oneselfCreate.status;
    }
  },
  watch: {
    page: function() {
      this.getMRecords();
    }
  },
  methods: {
    init() {
      MeetingApi.excute(
        this.type === MeetingMode.oneselfCreate.status
          ? "mySignInRecord"
          : "signInRecord",
        { meet_id: this.meetid, page: this.page },
        { methods: "get" }
      ).then(res => {
        if (this.type === MeetingMode.oneselfCreate.status) {
          this.signStat = res.data.data.stat;
          this.record = res.data.data.record;
        } else {
          this.signInfo = res.data.data;
        }
      });
    },
    onPageChange(page) {
      this.page = page;
    },
    getMRecords() {
      MeetingApi.excute(
        "mySignInRecord",
        { meet_id: this.meetid, page: this.page },
        { methods: "get" }
      ).then(res => {
        this.signStat = res.data.data.stat;
        this.record = res.data.data.record;
      });
    }
  },
  created() {
    this.type = this.$attrs.type;
    this.meetid = this.$attrs.meetid;
    this.detail = this.$attrs.detail || {};
    this.init();
  }
};
</script>
<style lang="scss" scoped>
.record-stat {
  padding: 20px;
  .sign-panel {
    margin: 0;
  }
}
.sign-panel {
  margin: 20px;
  /* display: flex; */
  box-shadow: 0 0 12px #efefef;
  padding: 13px 26px;
  border-radius: 4px;
  .title {
    color: #475b6d;
    font-size: 16px;
  }
  .info {
    display: flex;
    margin-top: 12px;
    .status {
      font-size: 16px;
      flex: 1;
      color: #b9bcc5;
    }
    .status.signed {
      color: #6dcc58;
    }
    .status.late {
      color: #fa7921;
    }
    .time {
      font-size: 14px;
      color: #d2d5de;
    }
  }
}
.statistic {
  display: flex;
  .stat {
    flex: 1;
    margin-right: 12px;
    text-align: center;
    line-height: 30px;
    .title {
      color: #475b6d;
      font-size: 14px;
    }
    .count {
      font-size: 20px;
      color: #ababab;
    }
    .count.late {
      color: #fa7921;
    }
    .count.checked {
      color: #6dcc58;
    }
  }
  .stat:last-child {
    margin-right: 0;
  }
}
.sign-record {
  height: calc(100% - 126px);
  .record-title {
    padding: 20px;
    color: #414a5a;
  }
  .sign-record-list {
    height: calc(100% - 117px);
    overflow-y: auto;
    .sign-item:last-child {
      border-bottom: none;
    }
    .sign-item {
      display: flex;
      align-items: center;
      border-bottom: 1px solid #eaedf2;
      background-color: #f6f6f6;
      .avatar-box {
        border-right: 1px solid #eaedf2;
        height: 80px;
        width: 80px;
        display: flex;
        justify-content: center;
        align-items: center;
        .avatar {
          margin-right: 0;
        }
      }
      .name {
        margin-left: 16px;
        flex: 1;
        color: #414a5a;
      }
      .sign-desc {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        line-height: 32px;
        .sign-title {
          color: #414a5a;
        }
        .status {
          color: #ababab;
        }
        .status.late {
          color: #fa7921;
        }
        .status.checked {
          color: #6dcc58;
        }
      }
    }
  }
  .el-pagination {
    padding: 12px;
    text-align: right;
  }
}
</style>