<template>
  <span class="common-icon-box">
    <span class="icon" :style="{'background-image': `url(/assets/img/${iconsrc}.${type})`}"></span>
    <span class="text" v-if="text">{{text}}</span>
  </span>
</template>
<script>
export default {
  name: "icon",
  props: {
    iconsrc: {
      type: String,
      required: true
    },
    type: {
      type: String,
      default: "png"
    },
    text: {
      type: String,
      default: null
    }
  }
};
</script>
<style lang="scss" scoped>
.common-icon-box {
  .icon {
    display: inline-block;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background-size: contain;
    background-repeat: no-repeat;
    margin-right: 5px;
  }
  display: inline-flex;
  align-items: center;
}
</style>