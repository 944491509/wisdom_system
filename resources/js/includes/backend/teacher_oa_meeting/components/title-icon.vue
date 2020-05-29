<template>
  <div class="bg-icon" :class="type"></div>
</template>
<script>
export default {
  name: "title-icon",
  props: {
    type: {
      default: String,
      type: String
    }
  }
};
</script>
<style lang="scss" scoped>
.bg-icon {
  display: inline-block;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background-size: contain;
  background-repeat: no-repeat;
  margin-right: 5px;
}
.bg-icon.info {
  background-image: url(/assets/img/teacher/icon-summary.png);
}
.bg-icon.record {
  background-image: url(/assets/img/teacher/icon-sign.png);
}

.bg-icon.signinQr {
  background-image: url(/assets/img/teacher/icon-qrin.png);
}
.bg-icon.signoutQr {
  background-image: url(/assets/img/teacher/icon-qrout.png);
}
.bg-icon.unfinished {
  background-image: url(/assets/img/teacher/icon-unfinished.png);
}
.bg-icon.accomplish {
  background-image: url(/assets/img/teacher/icon-accomplish.png);
}
.bg-icon.oneselfCreate {
  background-image: url(/assets/img/teacher/icon-oneselfCreate.png);
}
</style>