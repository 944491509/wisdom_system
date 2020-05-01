<template>
  <div>
    <div v-if="isCreateByMe">
      <div class="mine-summary" v-for="(summary, index) in allSummary" :key="index">
        <div class="top">
          <div class="title"></div>
          <div class="time"></div>
        </div>
        <div class="imgs"></div>
      </div>
    </div>
    <div class="add" v-if="isCreateByMe">
      <label for="upload">上传纪要</label>
      <input
        type="file"
        id="fileInput"
        @change="onFileSelected"
        hidden
        ref="referenceUpload"
        multiple="multiple"
        style="display: none"
      />
    </div>
  </div>
</template>
<script>
import { MeetingMode } from "../common/enum";
import { MeetingApi } from "../common/api";
export default {
  name: "MeetingInfo",
  data() {
    return {
      type: "",
      allSummary: [],
      oneSummary: []
    };
  },
  computed: {
    isCreateByMe() {
      return this.type === MeetingMode.oneselfCreate;
    }
  },
  methods: {
    onFileSelected(e) {
      for (let index = 0; index < e.target.files.length; index++) {
        const file = e.target.files[index];
        this.filelist.push({
          name: file.name,
          size: converSize(file.size),
          file
        });
      }
      this.$refs.referenceUpload.value = null;
    }
  },
  created() {
    this.type = this.$attrs.type;
    this.meetid = this.$attrs.meetid;
    MeetingApi.excute(
      this.type === MeetingMode.oneselfCreate
        ? "myMeetSummary"
        : "getMeetSummary",
      { meet_id: this.meetid },
      { methods: "get" }
    ).then(res => {
      if (this.type === MeetingMode.oneselfCreate) {
        this.allSummary = res.data.data;
      } else {
        this.oneSummary = res.data.data;
      }
    });
  }
};
</script>