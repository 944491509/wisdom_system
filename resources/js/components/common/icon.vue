<template>
  <span class="common-icon-box" @click="click">
    <span class="icon" :style="{'background-image': `url(/assets/img/${iconsrc}.${type})`, width,height}"></span>
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
      },
      height: {
        type: String,
        default: '34px'
      },
      width: {
        type: String,
        default: '34px'
      }
    },
    methods: {
      click(){
        this.$emit('click')
      }
    }
  };
</script>
<style lang="scss" scoped>
  .common-icon-box {
    .icon {
      display: inline-block;
      // border-radius: 50%;
      background-size: contain;
      background-repeat: no-repeat;
      margin-right: 5px;
    }

    display: inline-flex;
    align-items: center;
  }
</style>
