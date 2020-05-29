<template>
  <div>
    <div>
      <div
        class="list-item"
        v-for="(meeting, index) in list"
        :key="index"
        @click="checkDetail(meeting)"
      >
        <div class="meeting">
          <div class="meet-list">
            <div class="item">
              <span class="title">参会主题</span>
              <span class="content">{{ meeting.meet_title }}</span>
              <span
                class="meeting-status"
                v-if="isOneselfCreate"
                :class="'status'+meeting.status"
              >{{getStatusText(meeting.status)}}</span>
            </div>
            <div class="item">
              <span class="title">组织人</span>
              <span class="content">{{ meeting.approve_user }}</span>
            </div>
            <div class="item">
              <span class="title">参会地点</span>
              <span class="content">{{ meeting.room }}</span>
            </div>
            <div class="item">
              <span class="title">会议时间</span>
              <span class="content">{{ meeting.meet_time }}</span>
            </div>
            <div class="item" v-if="meeting.is_signin">
              <span class="title">签到时间</span>
              <span class="content">{{ meeting.signin_time }}</span>
            </div>
            <div class="sign-info" v-if="isAcomplished">
              <div
                class="sign-in"
                v-if="meeting.is_signin"
                :class="{checked: meeting.signin_status === 1, late: meeting.signin_status === 2}"
              >{{signinTextMap[meeting.signin_status] || ''}}</div>
              <div
                class="sign-in"
                v-if="meeting.is_signout"
                :class="{checked: meeting.signin_status}"
              >{{meeting.signin_status?'按时签退':'未签退'}}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <el-pagination
      background
      layout="prev, pager, next"
      :page-count="pagination.pageCount"
      :current-page="pagination.page"
      @current-change="onPageChange"
    ></el-pagination>
  </div>
</template>
<script>
import { MeetingApi } from "../common/api";
import { MeetingMode, MeetingStatus } from "../common/enum";
import { Util } from "../../../../common/utils";
// import MeetingDetail from "./meeting-detail";
export default {
  name: "meeting-list",
  props: {
    mode: {
      type: String,
      required: true,
      default: ""
    }
  },
  computed: {
    isAcomplished() {
      return this.mode === MeetingMode.accomplish.status;
    },
    isOneselfCreate() {
      return this.mode === MeetingMode.oneselfCreate.status;
    },
    getStatusText() {
      return function(status) {
        switch (status) {
          case 0:
            return "审核中";
          case 1:
            return "已拒绝";
          case 2:
            return "待开始";
          case 3:
            return "进行中";
          case 4:
            return "已结束";
          default:
            return "";
        }
      };
    }
  },
  data() {
    return {
      list: [],
      meetingId: "",
      pagination: {
        page: 1,
        pageCount: 0
      },
      signinTextMap: {
        0: "未签到",
        1: "按时签到",
        2: "迟到"
      }
    };
  },
  watch: {
    "pagination.page": page => {
      this.getMeetingList();
    }
  },
  methods: {
    checkDetail(meeting) {
      window.location.href =
        "/teacher/ly/oa/meeting/detail?id=" +
        meeting.meet_id +
        "&type=" +
        this.mode;
    },
    getMeetingList() {
      MeetingApi.excute(
        "list",
        {
          page: this.pagination.page
        },
        { url: this.mode, methods: "get" }
      ).then(res => {
        this.list = res.data.data.list;
        this.pagination.pageCount = res.data.data.lastPage;
      });
    },
    onPageChange(page) {
      this.pagination.page = page;
    }
  }
};
</script>
<style lang="scss" scoped>
.list-item:hover {
  box-shadow: 1px 1px 12px #cccccc;
}
.list-item {
  font-size: 14px;
  border-bottom: 1px solid #eaedf2;
  padding: 12px;
  transition: all 0.5s;
  cursor: pointer;
  display: flex;
  .meeting {
    width: 100%;
    .meet-list {
      flex: 1;
      .item {
        display: flex;
        flex-direction: row;
        padding-bottom: 8px;
        .title {
          width: 90px;
          flex: none;
          color: #aaaaaa;
          font-weight: bold;
        }
        .content {
          flex: 1;
          color: #333333;
        }
        .meeting-status {
          float: right;
          color: #b5b5b5;
        }
        .meeting-status.status0 {
          color: #fe7e21;
        }
        .meeting-status.status2 {
          color: #fe7e21;
        }
        .meeting-status.status3 {
          color: #6dcc58;
        }
      }
      .sign-info {
        .sign-in {
          display: inline-block;
          padding: 8px 24px;
          color: #b7b7b7;
          background-color: #f5f4f4;
          border: 1px dashed #b7b7b7;
          margin-right: 14px;
        }
        .sign-in.checked {
          color: #6dcc58;
          border-color: #6dcc58;
          background-color: #ebfee3;
        }
        .sign-in.late {
          color: #fe7b1c;
          border-color: #fe7b1c;
          background-color: #fbe8d8;
        }
      }
    }
  }
}

.el-pagination {
  float: right;
  padding-top: 16px;
}
.title-icon {
  display: inline-block;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background-size: contain;
  background-repeat: no-repeat;
  margin-right: 5px;
  // background-image: url(/assets/img/teacher/meeting-info.png) !important;
}
</style>
