<template>
  <div class="notification-box">
    <div class="header">
      <div class="title">
        <pf-icon :iconsrc="`notify/notify`" text="通知消息" />
      </div>
      <div class="count" v-if="count">最新{{count}}条</div>
    </div>
    <div class="scroll-container" ref="scrollbox">
      <div class="item" v-for="(item) in list" :key="item.id">
        <div class="notify-header">
          <pf-icon
            :iconsrc="(getIcon(item.category) || {}).src"
            width="18px"
            height="18px"
            :text="item.type"
          />
          <span class="time">{{item.created_at}}</span>
        </div>
        <div class="content-box">
          <div class="content">
            <span class="text" v-html="item.title"></span>
            <span class="read" v-if="item.read==='未读'">{{item.read}}</span>
          </div>
          <div class="content-tip" v-html="item.content"></div>
        </div>
        <div class="link-info" @click="goDetail(item)">查看详情</div>
      </div>
      <div class="list-end" v-if="loading">
        <i class="el-icon-loading"></i>
        <span>加载中...</span>
      </div>
      <div class="list-end" v-if="nomore">
        <span>--没有了--</span>
      </div>
    </div>
  </div>
</template>
<script>
import { Util } from "../../common/utils";

export default {
  name: "NotificationBox",
  data() {
    return {
      list: [],
      page: 1,
      loading: false,
      nomore: false,
      count: 0
    };
  },
  computed: {
    getIcon() {
      return function(type) {
        switch (type) {
          case 118:
            return { src: "notify/shenpi" };
          case 301:
            return { src: "notify/tongzhi" };
          case 206:
            return { src: "notify/xiangmu" };
          case 306:
            return { src: "notify/xiaoxi" };
          case 302:
            return { src: "notify/gonggao" };
          case 303:
            return { src: "notify/jiancha" };
          case 205:
            return { src: "notify/huiyi" };
          case 304:
            return { src: "notify/wifi" };
          case 208:
            return { src: "notify/neibuxin" };
          case 207:
            return { src: "notify/tasks" };
          case 212:
            return { src: "notify/xuanke" };
          case 213:
            return { src: "notify/shenpi" };
        }
      };
    }
  },
  methods: {
    getList() {
      // /api/school/load-major-grades
      if (this.loading || this.nomore) {
        return;
      }
      this.loading = true;
      axios.get("/api/notification/news-list?page=" + this.page).then(res => {
        if (Util.isAjaxResOk(res)) {
          this.list = this.list.concat(res.data.data.list);
          if (this.page === res.data.data.lastPage) {
            this.nomore = true;
          }
        } else {
          this.$message({
            message: res.data.message,
            type: "error"
          });
        }
        this.loading = false;
      });
    },
    goDetail(notify) {
        // window.open('/teacher/notice/info?notice_id='+notify.id)
      window.open("/" + notify.url + "?notice_id=" + notify.id);
    }
  },
  created() {
    this.getList();
  },
  mounted() {
    let scrolltarget = this.$refs.scrollbox;
    const space = 50;
    let that = this;
    scrolltarget.addEventListener("scroll", function() {
      if (
        scrolltarget.scrollTop + scrolltarget.clientHeight + 50 >=
        scrolltarget.scrollHeight
      ) {
        if (that.loading) {
          return;
        }
        that.page = that.page + 1;
        that.getList();
      }
    });
  }
};
</script>
<style lang="scss" scoped>
.notification-box {
  height: 100%;
  .header {
    display: flex;
    justify-self: center;
    padding: 10px 14px;
    border-bottom: 1px solid #eaedf2;
    margin-bottom: 8px;
    .title {
      flex: 1;
      font-size: 18px;
      color: #414a5a;
    }
    .count {
      flex: none;
      color: #b3bac2;
      font-size: 16px;
    }
  }
  .scroll-container {
    height: calc(100% - 64px);
    overflow-y: auto;
    .item {
      border-bottom: 1px solid #eaedf2;
      .notify-header {
        display: flex;
        justify-self: center;
        padding: 10px 14px;
        .common-icon-box {
          flex: 1;
          font-size: 16px;
          color: #414a5a;
        }
        .time {
          flex: none;
          color: #b7bdc5;
          font-size: 14px;
        }
      }
      .content-box {
        padding: 0 14px;
        font-size: 14px;
        .content {
          color: #414a5a;
          display: flex;
          .text {
            flex: 1;
          }
          .read {
            flex: none;
            color: #4ea5fe;
          }
        }
        .content-tip {
          color: #919aa7;
        }
      }
      .link-info {
        text-align: right;
        color: #b7bdc5;
        padding: 10px 14px;
        cursor: pointer;
      }
    }
    .list-end {
      padding: 16px;
      margin-bottom: 10px;
      text-align: center;
      color: #b7bdc5;
    }
  }
}
</style>
