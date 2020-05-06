<template>
  <div class="qr-container">
    <img :src="qrcode" alt />
  </div>
</template>
<script>
import { MeetingApi } from "../common/api";

export default {
  name: "QrInfo",
  data() {
    return {
      type: "",
      meetid: "",
      qrcode: ""
    };
  },
  methods: {
    init() {
      MeetingApi.excute(
        this.type === "signinQr" ? "signInQrCode" : "signOutQrCode",
        { meet_id: this.meetid },
        { methods: "get" }
      ).then(res => {
        this.qrcode = res.data.data.qrcode;
      });
    }
  },
  created() {
    this.type = this.$attrs.view;
    this.meetid = this.$attrs.meetid;
    this.init();
  }
};
</script>
<style lang="scss" scoped>
.qr-container {
  text-align: center;
}
</style>