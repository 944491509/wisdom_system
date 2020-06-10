<template>
  <el-cascader
    ref="areaSelector"
    :props="{
        label:'name',
        value:'id'
    }"
    separator="/"
    :options="options"
    v-model="areas"
    filterable
  ></el-cascader>
</template>
<script>
import cities from "../../includes/backend/auto-flow/common/cityies";

export default {
  data() {
    return {
      areas: [],
      options: cities
    };
  },
  props: {
    value: {
      default: ""
    }
  },
  watch: {
    areas: function(val) {
      setTimeout(() => {
        this.$emit("input", this.$refs.areaSelector.inputValue);
      }, 50);
    },
    value: function(str) {
      this.$refs.areaSelector.inputValue = str;
    }
  }
};
</script>