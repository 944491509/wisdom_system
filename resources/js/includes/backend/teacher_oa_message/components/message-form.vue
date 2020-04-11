<template>
  <div class="message-form">
    <div class="form">
      <el-form ref="form" :model="form" label-width="60px">
        <!-- 回复 -->
        <template v-if="mode.indexOf('reply') > -1">
          <el-form-item label="回复:">
            <span class="reply-info">{{form.title}}</span>
          </el-form-item>
          <el-form-item label="收信人:">
            <span class="reply-info">{{form.user_username}}</span>
          </el-form-item>
        </template>
        <!-- 发信 -->
        <template v-if="mode.indexOf('send') > -1">
          <el-form-item label="收件人:">
            <div class="selected-member">
              <div class="members" v-for="(member, index) in selectedMembers" :key="index">
                <span>{{member.name}}</span>
                <i class="el-icon-close" @click="removeChosen(member, index)"></i>
              </div>
            </div>
            <div class="set-member">
              <span @click="goSetMember">设置收件人</span>
            </div>
          </el-form-item>
          <el-form-item label="主题:">
            <el-input type="text" v-model="form.title"></el-input>
          </el-form-item>
        </template>
        <el-form-item label="内容:">
          <el-input type="textarea" :rows="10" v-model="form.content"></el-input>
        </el-form-item>
        <el-form-item label>
          <div class="file-box" v-for="(file, index) in filelist" :key="index">
            <div class="name">{{file.name}}</div>
            <div class="info">
              <span class="delete" @click="removeFile(index)">删除</span>
              <span class="size">{{file.size}}</span>
            </div>
          </div>
          <div class="add-files" @click="()=>{showFileManagerFlag = true}">
            <i class="el-icon-paperclip"></i> 添加附件
          </div>
        </el-form-item>
        <!-- 转发分割线 -->
        <template v-if="shareMessage && shareMessage.id">
          <el-divider></el-divider>
          <div class="share-title">转发邮件的内容</div>
          <detail :message="shareMessage" :editshare="mode === 'edit_send'" />
        </template>
      </el-form>
      <div class="btn-box">
        <el-button type="primary" @click="send(false)">发送</el-button>
        <el-button @click="send(true)">存草稿</el-button>
      </div>
    </div>
    <member-select
      @onSelect="setMemberList"
      :disabledList="disabledList"
      :selectedInit="selectedMembers"
      class="member-select"
      :class="{'vie':selectMb}"
    ></member-select>
    <el-drawer
      v-if="currentUserId"
      title="我的易云盘"
      :visible.sync="showFileManagerFlag"
      direction="rtl"
      size="100%"
      :append-to-body="true"
      custom-class="e-yun-pan internal-message"
    >
      <file-manager
        :user-uuid="currentUserId"
        :allowed-file-types="[]"
        :pick-file="true"
        v-on:pick-this-file="pickFileHandler"
      ></file-manager>
    </el-drawer>
  </div>
</template>
<script>
import { MessageApi } from "../common/api";
import { converSize, InnerStorage } from "../common/utils";
import { Util } from "../../../../common/utils";
// import MemberSelect from "./member-chose";
import MemberSelect from "./setmember";
import Detail from "./detail-component";
import moment from "moment";

export default {
  name: "message-form",
  components: {
    MemberSelect,
    Detail
  },
  computed: {
    disabledList() {
      return [
        this.currentUserId,
        ...(this.selectedMembers || []).map(mem => {
          return parseInt(mem.id);
        })
      ];
    },
    converSize() {
      return function(size) {
        return converSize(size);
      };
    }
  },
  methods: {
    send(isTemp) {
      const { title, content } = this.form;
      if (this.mode.indexOf("send") > -1) {
        // 发信、转发信
        if (!this.form.title) {
          this.$message.error("请输入信件主题");
          return;
        }
        if (!this.form.content) {
          this.$message.error("请填写信件内容");
          return;
        }
        if (this.selectedMembers.length === 0) {
          this.$message.error("请选择收信人");
          return;
        }
        let formdata = {
          title,
          content
        };
        let collectUser = [];
        let collectId = [];
        this.selectedMembers.forEach(mem => {
          collectId.push(mem.id);
          collectUser.push(mem.name);
        });
        formdata.collectId = collectId.toString();
        formdata.collectUser = collectUser.toString();
        formdata.type = isTemp ? 4 : 3; // 是否存草稿
        formdata.isRelay = this.shareMessage && this.shareMessage.id ? 1 : 0;
        if (formdata.isRelay) {
          formdata.relayId = this.shareMessage.id;
        }
        formdata.isFile = this.filelist.length > 0 ? 1 : 0;
        if(this.filelist.length>0){
          formdata.image = this.filelist
        }
        if (this.mode === "send") {
          MessageApi.excute("addOaMessageInfo", formdata).then(res => {
            this.$emit("done");
          });
        } else {
          // 草稿更新
          formdata.id = this.messageId;
          MessageApi.excute("updateOaMessageInfo", formdata).then(res => {
            this.$emit("done");
          });
        }
      } else {
        // 回复信件
        let formdata = {
          content,
          title: "回复：" + title
        };
        formdata.isRelay = 0;
        formdata.isFile = this.filelist.length > 0 ? 1 : 0;
        if(this.filelist.length>0){
          formdata.image = this.filelist
        }
        // formdata.relayId = this.messageId;
        formdata.type = isTemp ? 4 : 3; // 是否存草稿
        formdata.collectId = this.message.user_id;
        formdata.collectUser = this.message.user_username;
        if(this.mode === 'reply'){
          MessageApi.excute("addOaMessageInfo", formdata).then(res => {
            this.$emit("done");
          });
        }else{
          formdata.id = this.messageId;
          MessageApi.excute("updateOaMessageInfo", formdata).then(res => {
            this.$emit("done");
          });
        }
      }
    },
    goSetMember() {
      this.selectMb = true;
    },
    setMemberList(idlist, memberList) {
      this.selectedMembers = memberList;
      this.selectMb = false;
    },
    removeChosen(member, index) {
      this.selectedMembers.splice(index, 1);
    },
    removeFile(index) {
      this.filelist.splice(index, 1);
    },
    pickFileHandler(payload) {
      if (payload && payload.file) {
        this.showFileManagerFlag = false;
        this.filelist.push({
          name: payload.file.file_name,
          size: converSize(payload.file.size),
          path: payload.file.url,
          type: payload.file.file_name.split('.').pop()
        });
        // this.files.push(payload.file);
      }
    },
    setData(mode, message, shareMessage) {
      this.mode = mode;
      this.message = message;
      if (shareMessage && shareMessage.id) {
        this.shareMessage = shareMessage || {};
      }
      if (message.relay && message.relay.length > 0) {
        this.shareMessage = message.relay[message.relay.length - 2];
      }
      this.messageId = message.id;
      this.form.title = message.title || "";
      if (this.mode === "edit_reply" && this.form.title.startsWith("回复：")) {
        this.form.title = this.form.title.substring(3, this.form.title.length);
      }
      this.form.content = message.content || "";
      if (this.mode === "reply") {
        this.form.content = ""; //回复信息 不回写content
      }
      this.form.user_username = message.user_username;
      this.filelist = message.file || [];
      let collect_user_ids = message.collect_user_id
        ? message.collect_user_id.split(",")
        : [];
      let collect_user_names = message.collect_user_id
        ? message.collect_user_name.split(",")
        : [];
      this.selectedMembers =
        collect_user_ids.length > 0
          ? collect_user_ids.map((id, index) => {
              return {
                id,
                name: collect_user_names[index]
              };
            })
          : [];
    }
  },
  data() {
    let that = this;
    return {
      form: {
        title: "",
        content: ""
      },
      showFileManagerFlag: false,
      selectedMembers: [],
      selectMb: false,
      filelist: [],
      shareMessage: {}, //转发的信件
      currentUserId: 0,
      mode: "send" //send:新信息(包括转发)  edit_send:编辑草稿(包括转发)  reply:回复  edit_reply:编辑回复草稿
    };
  },
  created() {
    this.currentUserId = InnerStorage.get("userId");
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
    overflow-y: auto;
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
      i {
        font-size: 20px;
      }
    }
  }
  .share-title {
    font-size: 20px;
    color: #333333;
    font-weight: 500;
    padding-left: 16px;
  }
  .btn-box {
    flex: none;
    padding: 12px;
    text-align: center;
  }
}

.message-form {
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
.selected-member {
  line-height: 28px;
  .members {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 8px 4px 12px;
    color: #409eff;
    border: 1px solid #409eff;
    margin: 6px;
    border-radius: 4px;
    cursor: pointer;
    .el-icon-close {
      float: right;
      margin-left: 8px;
      font-size: 20px;
    }
  }
}
.set-member {
  text-decoration: underline;
  color: #4ea5fe;
  span {
    cursor: pointer;
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
</style>

<style lang="scss">
.e-yun-pan.internal-message {
  .el-drawer__header {
    .el-drawer__close-btn {
      z-index: 20;
    }
  }
}
</style>