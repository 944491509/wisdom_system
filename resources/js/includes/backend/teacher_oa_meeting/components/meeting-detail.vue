<template>
  <div class="detail detail-panel">
    <div class="title">
      <!-- <span>{{statusText}}</span> -->
      <span class="title-text"><title-icon :type="type" /> <span>详情</span></span>
      <span class="return-back" @click="goback"></span>
    </div>
    <div class="detail-item">
      <span class="title">会议主题</span>
      <span class="content">{{meeting.meet_title}}</span>
    </div>
    <div class="detail-item">
      <span class="title">会议地点</span>
      <span class="content">{{meeting.room}}</span>
    </div>
    <div class="detail-item">
      <span class="title">会议时间</span>
      <span class="content">{{meeting.meet_time}}</span>
    </div>
    <div class="detail-item">
      <span class="title">会议负责人</span>
      <span class="content">{{meeting.approve}}</span>
    </div>
    <div class="detail-item">
      <span class="title">参会人</span>
      <span class="content">{{meeting.user_num}}人</span>
    </div>
    <div class="detail-item" v-if="meeting.signin_status">
      <span class="title">开始签到时间</span>
      <span class="content">{{meeting.signin_time}}</span>
    </div>
    <div class="detail-item" v-if="meeting.signout_status">
      <span class="title">结束签退时间</span>
      <span class="content">{{meeting.signout_time}}</span>
    </div>
    <div class="detail-item">
      <span class="title">会议说明</span>
      <span class="content">{{meeting.meet_content}}</span>
    </div>
    <div class="detail-item" v-if="meeting.fields && meeting.fields.length > 0">
      <span class="title">附件</span>
      <span class="content">
        <div class="message-files">
          <div class="files">
            <div class="file" v-for="(file, index) in meeting.fields" :key="index">
              <div class="file-name">{{file.file_name}}</div>
              <div class="file-info">
                <span class="download" @click="downloadFile(file)"></span>
                <!-- <span class="size">{{file.size}}</span> -->
              </div>
            </div>
          </div>
        </div>
      </span>
    </div>
    <div class="btn-box">
      <el-button size="small" class="info" v-if="isMine || finished" @click="check('info', '会议纪要')">会议纪要</el-button>
      <el-button
        @click="check('record', '签到记录')"
        size="small"
        class="record"
        v-if="(meeting.signin_status||meeting.signout_status) && (isMine || finished)"
      >签到记录</el-button>
      <el-button
        size="small"
        class="signin"
        v-if="isMine && meeting.signin_status"
        @click="check('signinQr', '签到二维码')"
      >签到二维码</el-button>
      <el-button
        size="small"
        class="signout"
        v-if="isMine && meeting.signout_status"
        @click="check('signoutQr', '签退二维码')"
      >签退二维码</el-button>
    </div>

    <el-drawer
      ref="meetingDetailDrawer"
      :destroy-on-close="true"
      :visible.sync="showDetail"
      direction="rtl"
    >
      <template slot="title">
        <div class="meeting-detail-title">
          <title-icon :type="infoType" /><span>{{infoTitle}}</span>
        </div>
      </template>
      <info :type="infoType" :stateType="type" :meetid="meetingId" :detail="meeting" />
    </el-drawer>
  </div>
</template>
<script>
import { MeetingApi } from "../common/api";
import { MeetingMode } from "../common/enum";
import Info from "../components/info";
import TitleIcon from "./title-icon";
import { getQueryString } from "../../teacher_oa_tasks/common/utils";
import { DownLoadUtil } from "../../teacher_oa_message/common/utils";
export default {
  name: "meeting-detail",
  components: {
    Info,
    TitleIcon
  },
  data() {
    return {
      meeting: {},
      statusText: "",
      type: "",
      infoType: "",
      infoTitle: '',
      showDetail: false
    };
  },
  computed: {
    isMine() {
      return this.type === MeetingMode.oneselfCreate.status;
    },
    finished() {
      return this.type === MeetingMode.accomplish.status;
    }
  },
  methods: {
    getInfo() {
      if (!this.meetingId) {
        return;
      }
      MeetingApi.excute(
        "meetDetails",
        { meet_id: this.meetingId },
        { methods: "get" }
      ).then(res => {
        this.meeting = res.data.data;
      });
    },
    check(type, title) {
      this.infoType = type;
      this.infoTitle = title;
      this.showDetail = true;
    },
    downloadFile(file) {
      window
        .axios({
          url: file.url,
          method: "GET",
          responseType: "blob" // important
        })
        .then(response => {
          DownLoadUtil.download(new Blob([response.data]), file.name);
        })
        .catch(e => {
          if (e.response.status === 404) {
            this.$message.error("附件不存在");
          } else {
            this.$message.error("附件下载失败");
          }
        });
    },
    goback() {
      window.localStorage.setItem('meetingMode', this.type)
      window.location.href = "/teacher/ly/oa/meetings"
    }
  },
  created() {
    this.meetingId = getQueryString("id");
    this.type = getQueryString("type");
    this.statusText = (MeetingMode[getQueryString("type")] || {}).text;
    this.getInfo();
  }
};
</script>
<style lang="scss" scoped>
.detail-panel {
  background-color: #ffffff;
  min-height: 50%;
  margin-right: 14px;
  border-radius: 4px;
  width: 570px;
  > .title {
    font-size: 18px;
    color: #4ea5fe;
    padding: 14px;
    .title-text{
      display: inline-flex;
      align-items: center;
    }
    .el-button {
      float: right;
    }
    .return-back {
      background-image: url(/assets/img/teacher/icon-return.png);
      height: 30px;
      width: 30px;
      float: right;
      background-size: contain;
      cursor: pointer;
      transition: all 0.4s;
    }
    .return-back:hover {
      transform: scale(1.1);
    }
  }

  .detail-item {
    display: flex;
    border-top: 1px solid #eaedf2;
    padding-left: 22px;
    font-size: 14px;
    padding: 12px;

    .title {
      display: inline-block;
      width: 100px;
      color: #8a93a1;
    }

    .content {
      flex: 1;
      word-break: break-all;
      word-wrap: break-word;
      color: #313b4c;
    }
  }
}

.btn-box {
  text-align: center;
  padding: 32px 0;
  .info {
    background-color: #4ea5fe;
    border-color: #4ea5fe;
    color: #ffffff;
  }
  .record {
    background-color: #66d9ff;
    border-color: #66d9ff;
    color: #ffffff;
  }
  .signin {
    background-color: #4ea5fe;
    border-color: #4ea5fe;
    color: #ffffff;
  }
  .signout {
    background-color: #fe7b1c;
    border-color: #fe7b1c;
    color: #ffffff;
  }
}
.message-files {
  .files {
    .file {
      display: flex;
      margin-top: 12px;
      background: #f2f9ff;
      padding: 14px;

      .file-name {
        flex: 1;
        color: #414a5a;
        align-self: center;
      }

      .file-info {
        flex: none;
        display: flex;
        flex-direction: column;
        align-items: center;

        .download {
          background-image: url("/assets/img/teacher/download.png");
          background-size: contain;
          background-repeat: no-repeat;
          display: inline-block;
          width: 22px;
          height: 22px;
          cursor: pointer;
        }

        .size {
          color: #c5cad0;
          margin-top: 6px;
          font-size: 12px;
        }
      }
    }
  }
}
.meeting-detail-title {
  display: flex;
  align-items: center;
}
</style>