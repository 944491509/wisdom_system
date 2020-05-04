<template>
  <div class="container">
    <div v-if="isCreateByMe">
      <div class="mine-summary" v-for="(summary, index) in allSummary" :key="index">
        <div class="top">
          <div class="title">{{summary.user_name}}</div>
          <div class="time">{{summary.created_at}}</div>
        </div>
        <div class="imgs">
          <div
            class="item summary-image"
            v-for="(item, index) in summary.summaries"
            :key="index"
            :style="{backgroundImage: `url(${item.url})`}"
          ></div>
        </div>
      </div>
    </div>
    <template v-else>
      <div class="one-summary">
        <div class="item" v-for="(item, index) in oneSummary" :key="'common'+index">
          <div class="img summary-image" :style="{backgroundImage: `url(${item.url})`}"></div>
          <div class="name">{{item.file_name}}</div>
        </div>
        <div class="item" v-for="(item, index) in oneSummaryTosubmit" :key="'upload'+index">
          <div class="img" :style="{backgroundImage: `url(${item.src})`}"></div>
          <div class="name">{{item.name}}</div>
          <div class="delete" @click="remove(index)">删除</div>
        </div>
        <div class="upload" v-if="oneSummaryTosubmit.length + oneSummary.length < 7">
          <label for="fileInput">上传纪要</label>
          <input
            type="file"
            id="fileInput"
            accept="image/gif, image/jpeg, image/jpg, image/png, image/svg"
            @change="onFileSelected"
            hidden
            ref="referenceUpload"
            multiple="multiple"
            style="display: none"
          />
        </div>
      </div>
      <div
        class="btn-box"
        v-if="(oneSummaryTosubmit.length + oneSummary.length < 7) || oneSummaryTosubmit.length"
      >
        <el-button
          type="primary"
          :loading="submiting"
          :disabled="!oneSummaryTosubmit.length"
          @click="submit"
        >确定</el-button>
      </div>
    </template>
  </div>
</template>
<script>
import { MeetingMode } from "../common/enum";
import { MeetingApi, submitSummary } from "../common/api";
import { getFileURL } from "../../teacher_oa_tasks/common/utils";
export default {
  name: "MeetingInfo",
  data() {
    return {
      type: "",
      submiting: false,
      allSummary: [],
      oneSummary: [],
      oneSummaryTosubmit: []
    };
  },
  computed: {
    isCreateByMe() {
      return this.type === MeetingMode.oneselfCreate.status;
    }
  },
  methods: {
    onFileSelected(e) {
      if (
        e.target.files.length +
          this.oneSummary.length +
          this.oneSummaryTosubmit.length >
        7
      ) {
        this.$message.error("最多上传7张图");
      } else {
        for (let index = 0; index < e.target.files.length; index++) {
          const file = e.target.files[index];
          this.oneSummaryTosubmit.push({
            src: getFileURL(file),
            name: file.name,
            file
          });
        }
      }
      this.$refs.referenceUpload.value = null;
    },
    remove(index) {
      this.oneSummaryTosubmit.splice(index, 1);
    },
    submit() {
      this.submiting = true;
      let form = new FormData();
      form.append("meet_id", this.meetid);
      this.oneSummaryTosubmit.forEach(img => {
        form.append("summary[]", img.file);
      });
      submitSummary(form).then(res => {
        this.init();
        this.submiting = false;
        this.oneSummaryTosubmit = [];
      });
    },
    init() {
      MeetingApi.excute(
        this.type === MeetingMode.oneselfCreate.status
          ? "myMeetSummary"
          : "getMeetSummary",
        { meet_id: this.meetid },
        { methods: "get" }
      ).then(res => {
        if (this.type === MeetingMode.oneselfCreate.status) {
          this.allSummary = res.data.data;
        } else {
          this.oneSummary = res.data.data;
        }
      });
    }
  },
  created() {
    this.type = this.$attrs.type;
    this.meetid = this.$attrs.meetid;
    this.init();
  }
};
</script>
<style lang="scss" scoped>
.container {
  padding: 20px;
  display: flex;
  height: 100%;
  flex-direction: column;
  .upload {
    flex: none;
    margin-top: 12px;
  }
  .upload label {
    color: #4ea5fe;
    cursor: pointer;
  }
  .upload label:hover {
    text-decoration: underline;
  }
  .one-summary {
    flex: 1;
    .item {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
      background-color: #f2f9ff;
      padding: 9px;
      .img {
        width: 56px;
        height: 56px;
        flex: none;
        margin-right: 13px;
      }
      .name {
        flex: none;
        color: #414a5a;
      }
      .delete {
        flex: 1;
        text-align: right;
        color: #4ea5fe;
        cursor: pointer;
      }
    }
  }
  .btn-box {
    flex: none;
    padding: 12px;
    text-align: center;
  }
  .mine-summary {
    border-bottom: 1px solid #eaedf2;
    padding-top: 12px;
    .top {
      display: flex;
      .title {
        color: #414a5a;
        font-size: 16px;
      }
      .time {
        font-size: 14px;
        color: #d2d5de;
        flex: 1;
        text-align: right;
      }
    }
    .imgs {
      .item {
        margin-right: 14px;
        margin-bottom: 12px;
        width: 56px;
        height: 56px;
        display: inline-block;
      }
      .item:last-child {
        margin-right: 0;
      }
    }
  }
  .mine-summary:last-child {
    border-bottom: none;
  }
}
.summary-image {
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
}
</style>