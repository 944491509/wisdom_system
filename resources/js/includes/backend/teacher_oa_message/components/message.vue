<template>
  <div>
    <div>
      <div class="list-item" v-for="(message, index) in list" :key="index">
        <div class="checker">
          <el-checkbox v-model="message.checked" @change="calcCheckAll" size="medium"></el-checkbox>
        </div>
        <div class="message" @click="checkDetail(message)">
          <div class="header">
            <div class="avatar">
              <avatar :src="message.user_pics" />
            </div>
            <div class="info">
              <div class="title">{{message.title}}</div>
              <div class="sender">
                <span class="desc">发件人</span>
                <span class="name">{{message.user_username}}</span>
              </div>
            </div>
          </div>
          <div class="content">{{message.content}}</div>
          <div class="message-footer">
            <span class="files">
              <i class="el-icon-paperclip"></i> 含附件
            </span>
            <span class="time">{{message.create_time}}</span>
          </div>
        </div>
      </div>
    </div>
    <div class="list-footer">
      <div class="ctrl">
        <div class="checkall">
          <el-checkbox v-model="checkall" @change="doCheckall" size="medium">全选</el-checkbox>
          <el-button :disabled="!checked" @click="deleteMessage">
            <i class="iconfont icon-delete"></i>
          </el-button>
          <el-button v-if="mode==='unread'" :disabled="!checked" @click="readMessage">
            <i class="iconfont icon-email-open"></i>
          </el-button>
        </div>
      </div>
      <div class="footer-pagination">
        <el-pagination
          background
          layout="prev, pager, next"
          :page-count="pagination.pageCount"
          :current-page="pagination.page"
          @current-change="onPageChange"
        ></el-pagination>
      </div>
    </div>

    <el-drawer
      ref="messageDetailDrawer"
      :destroy-on-close="true"
      :visible.sync="showDetail"
      direction="rtl"
    >
      <template slot="title">
        <div class="message-detail-title">
          <span class="title-icon"></span> 详情
        </div>
      </template>
      <message-detail ref="messageDetail" @go="go" @close="closeDetail" />
    </el-drawer>
  </div>
</template>
<script>
import avatar from "./avatar";
import { MessageApi } from "../common/api";
import { MessageMode, MessageStatus } from "../common/enum";
import { Util } from "../../../../common/utils";
import MessageDetail from "./message-detail";
export default {
  name: "message-list",
  props: {
    mode: {
      type: String,
      required: true,
      default: ""
    }
  },
  components: {
    avatar,
    MessageDetail
  },
  data() {
    return {
      list: [],
      checkall: false,
      checked: false,
      showDetail: false,
      messageId: "",
      pagination: {
        page: 1,
        pageCount: 0
      }
    };
  },
  watch: {
    "pagination.page": page => {
      this.getMessageList();
    }
  },
  methods: {
    checkDetail(message) {
      this.getDetail(message.id).then(detail => {
        if (detail.type === MessageMode.temp.value) {
          if (detail.title.startsWith("回复：")) {
            this.$emit("formbroad", "edit_reply", detail);
          } else {
            this.$emit("formbroad", "edit_send", detail);
          }
        } else {
          this.showDetail = true;
          this.$nextTick(() => {
            this.$refs.messageDetailDrawer.$children[0].updateDetail(detail);
            if (this.mode === MessageMode.unread.status) {
              MessageApi.excute("updateTag", {
                id: detail.id,
                type: 2
              });
            }
          });
        }
      });
    },
    getDetail(id) {
      return new Promise(resolve => {
        MessageApi.excute("getMessageDetail", {
          id
        }).then(res => {
          resolve(res.data.data);
        });
      });
    },
    getMessageList() {
      MessageApi.excute("getOaMessageListInfo", {
        page: this.pagination.page,
        type: MessageMode[this.mode].value
      }).then(res => {
        this.checkall = false;
        this.list = res.data.data;
        this.pagination.pageCount = res.data.lastPage;
        this.checkall = false;
      });
    },
    onPageChange(page) {
      this.pagination.page = page;
    },
    calcCheckAll(checked) {
      try {
        if (checked) {
          this.list.forEach(item => {
            if (!item.checked) {
              throw new Error("false");
            }
          });
          this.checkall = true;
        } else {
          this.list.forEach(item => {
            if (item.checked) {
              throw new Error("false");
            }
          });
          this.checkall = false;
        }
      } catch (e) {
        this.checkall = e.message === "false" ? false : true;
      } finally {
        this.checked =
          this.list.filter(message => message.checked === true).length > 0;
      }
    },
    doCheckall: function(val) {
      if (val === false) {
        this.list.forEach(item => {
          item.checked = false;
        });
        this.checked = false
      } else {
        this.list.forEach(item => {
          item.checked = true;
        });
        this.checked = true
      }
    },
    deleteMessage() {
      let checkedList = this.list.filter(message => message.checked === true);
      if (checkedList.length === 0) {
        return;
      }
      let ids = checkedList.map(message => {
        return message.id;
      });
      this.$confirm("确认删除信件?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning"
      }).then(() => {
        const pros = [];
        ids.forEach(id => {
          pros.push(
            MessageApi.excute("updateTag", {
              id,
              type: 1
            })
          );
        });
        Promise.all(pros).then(() => {
          this.getMessageList();
        });
      });
    },
    readMessage() {
      let checkedList = this.list.filter(message => message.checked === true);
      if (checkedList.length === 0) {
        return;
      }
      let ids = checkedList.map(message => {
        return message.id;
      });
      const pros = [];
      ids.forEach(id => {
        pros.push(
          MessageApi.excute("updateTag", {
            id,
            type: 2
          })
        );
      });
      Promise.all(pros).then(() => {
        this.getMessageList();
      });
    },
    go(mode, message) {
      if (mode === "reply") {
        this.$emit("formbroad", "reply", message);
      } else if (mode === "share") {
        this.$emit("formbroad", "send", {}, message);
      }
    },
    closeDetail(refresh) {
      this.$refs.messageDetailDrawer.closeDrawer();
      this.showDetail = false;
      if (refresh) {
        this.getMessageList();
      }
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
  .checker {
    flex: 0;
    padding: 50px 16px 0 0;
    .el-checkbox {
      height: 100%;
    }
  }
  .message {
    flex: 1;
    display: flex;
    flex-direction: column;
    .header {
      display: flex;
      .avatar {
        margin-right: 6px;
      }
      .info {
        .title {
          color: #313b4c;
          font-size: 16px;
        }
        .sender {
          margin-top: 6px;
          .desc {
            color: #9199a6;
          }
          .name {
            color: #323c4d;
          }
        }
      }
    }
    .content {
      color: #9199a6;
      margin-top: 5px;
    }
    .message-footer {
      display: flex;
      margin-top: 6px;
      .files {
        i {
          font-size: 20px;
          float: left;
        }
        flex: none;
        color: #9199a6;
      }
      .time {
        flex: 1;
        text-align: right;
        font-size: 12px;
        color: #d2d5de;
      }
    }
  }
}
.list-item:hover {
  box-shadow: 0 0 6px #ccc;
}
.list-footer {
  display: flex;
  padding-top: 16px;
  .ctrl {
    flex: none;
    display: flex;
    align-items: center;
    .checkall {
      display: flex;
      align-items: center;
      padding-left: 12px;
      label {
        margin: 0;
      }
      .el-button {
        margin-left: 12px;
        background-size: contain;
        background-repeat: no-repeat;
        padding: 6px 10px;
        border: none;
        .iconfont {
          font-size: 20px;
        }
      }
    }
  }
  .footer-pagination {
    flex: 1;
    display: flex;
    justify-content: flex-end;
  }
}
.message-detail-title {
  display: flex;
  align-items: center;
}
.title-icon {
  display: inline-block;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background-size: contain;
  background-repeat: no-repeat;
  margin-right: 5px;
  background-image: url(../assets/message-info.png) !important;
}
</style>
