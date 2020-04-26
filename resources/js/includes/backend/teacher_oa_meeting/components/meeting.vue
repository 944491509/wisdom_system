<template>
  <div>
    <div>
      <div class="list-item" v-for="(meeting, index) in list" :key="index">
        <div class="meeting" @click="checkDetail(meeting)">
          <div class="meet-list">
            <div class="item">
              <span class="title">参会主题</span>
              <span class="content">{{ meeting.meet_title }}</span>
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
            <div class="item">
              <span class="title">签到时间</span>
              <span class="content">{{ meeting.signin_time }}</span>
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

    <el-drawer
      ref="meetingDetailDrawer"
      :destroy-on-close="true"
      :visible.sync="showDetail"
      direction="rtl"
    >
      <template slot="title">
        <div class="meeting-detail-title">
          <span class="title-icon"></span> 详情
        </div>
      </template>
      <!-- <meeting-detail ref="meetingDetail" @go="go" @close="closeDetail" /> -->
    </el-drawer>
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
  components: {
    // avatar,
    // MeetingDetail
  },
  data() {
    return {
      list: [],
      showDetail: false,
      meetingId: "",
      pagination: {
        page: 1,
        pageCount: 0
      }
    };
  },
  watch: {
    "pagination.page": page => {
      this.getMeetingList();
    }
  },
  methods: {
    getDetail(id) {
      return new Promise(resolve => {
        MeetingApi.excute("getMeetingDetail", {
          id
        }).then(res => {
          resolve(res.data.data);
        });
      });
    },
    getMeetingList() {
      MeetingApi.excute(
        "list",
        {
          page: this.pagination.page,
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
.list-item {
  font-size: 14px;
  border-bottom: 1px solid #eaedf2;
  padding: 12px;
  transition: all 0.5s;
  cursor: pointer;
  display: flex;
  .meeting {
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
  // background-image: url(../assets/meeting-info.png) !important;
}
</style>
