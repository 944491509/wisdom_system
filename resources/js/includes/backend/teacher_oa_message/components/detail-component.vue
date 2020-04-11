<template>
  <div class="detail-container" v-if="message.relay && message.relay.length > 1">
    <div v-for="(msg, index) in messageRelays" :key="msg.id" v-show="!(editshare && index === 0)">
      <div class="detail detail-panel">
        <div class="detail-item">
          <span class="title">发件人</span>
          <span class="content">{{msg.user_username}}</span>
        </div>
        <div class="detail-item">
          <span class="title">时间</span>
          <span class="content">{{msg.created_at}}</span>
        </div>
        <div class="detail-item">
          <span class="title">收件人</span>
          <span class="content">{{msg.collect_user_name}}</span>
        </div>
        <div class="detail-item">
          <span class="title">主题</span>
          <span class="content">{{msg.title}}</span>
        </div>
        <div class="detail-item">
          <span class="title"></span>
          <span class="content">{{msg.content}}</span>
        </div>
        <div class="detail-item" v-if="msg.file && msg.file.length > 0">
          <span class="title">附件</span>
          <span class="content">
            <div class="message-files">
              <div class="files">
                <div class="file" v-for="(file, index) in msg.file" :key="index">
                  <div class="file-name">{{file.name}}</div>
                  <div class="file-info">
                    <span class="download" @click="downloadFile(file)"></span>
                    <span class="size">{{file.size}}</span>
                  </div>
                </div>
              </div>
            </div>
          </span>
        </div>
      </div>
      <template v-if="index !== messageRelays.length - 1">
        <el-divider></el-divider>
        <div class="share-title">转发邮件的内容</div>
      </template>
    </div>
  </div>
  <div class="detail-container" v-else>
    <div class="detail detail-panel">
      <div class="detail-item">
        <span class="title">发件人</span>
        <span class="content">{{message.title}}</span>
      </div>
      <div class="detail-item">
        <span class="title">时间</span>
        <span class="content">{{message.created_at}}</span>
      </div>
      <div class="detail-item">
        <span class="title">收件人</span>
        <span class="content">{{message.collect_user_name}}</span>
      </div>
      <div class="detail-item">
        <span class="title">主题</span>
        <span class="content">{{message.title}}</span>
      </div>
      <div class="detail-item" v-if="message.file && message.file.length > 0">
        <span class="title">附件</span>
        <span class="content">
          <div class="message-files">
            <div class="files">
              <div class="file" v-for="(file, index) in message.file" :key="index">
                <div class="file-name">{{file.name}}</div>
                <div class="file-info">
                  <span class="download" @click="downloadFile(file)"></span>
                  <span class="size">{{file.size}}</span>
                </div>
              </div>
            </div>
          </div>
        </span>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  name: "msg-detail",
  props: {
    message: {
      type: Object,
      default: Object
    },
    editshare: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    messageRelays() {
      let relays = this.message.relay;
      relays = JSON.parse(JSON.stringify(relays));
      return relays && relays.length > 1 ? relays.reverse() : null;
    }
  },
  methods: {
    downloadFile(file) {
      window
        .axios({
          url: file.path,
          method: "GET",
          responseType: "blob" // important
        })
        .then(response => {
          DownLoadUtil.download(new Blob([response.data]), file.name);
        });
    }
  }
};
</script>
<style lang="scss" scoped>
.detail-container {
  flex: 1;
  overflow-y: auto;
  .share-title {
    font-size: 20px;
    color: #333333;
    font-weight: 500;
    padding-left: 16px;
  }
  .detail-panel {
    flex: 1;
    background-color: #ffffff;
    border-radius: 4px;

    > .title {
      font-size: 18px;
      color: #4ea5fe;
      padding: 14px;

      .el-button {
        float: right;
      }
    }

    // .detail-item:first-child {
    // border-top: none;
    // }
    .detail-item {
      display: flex;
      // border-top: 1px solid #eaedf2;
      padding-left: 22px;
      font-size: 14px;
      padding: 12px 0;
      margin: 0 20px;

      .title {
        display: inline-block;
        width: 72px;
        color: #8a93a1;
      }

      .content {
        flex: 1;
        word-break: break-all;
        word-wrap: break-word;
        color: #414a5a;
      }
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
            background-image: url("../assets/download.png");
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
}
</style>