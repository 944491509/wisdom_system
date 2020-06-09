<template>
  <el-cascader
    ref="areaSelector"
    :props="{
        label:'name',
        value:'id'
    }"
    separator="/"
    :options="options"
    v-model="value"
    filterable
  ></el-cascader>
</template>
<script>
import cities from "../../includes/backend/auto-flow/common/cityies";

export default {
  data() {
    return {
      value: [],
      options: cities
    };
  },
  watch: {
    value: function(val) {
      setTimeout(() => {
        this.$emit("input", this.$refs.areaSelector.inputValue);
      }, 50);
    }
  }
};
</script>