<template>
  <div v-if="mode === 'student'">
    <el-select v-model="value.year" placeholder="请选择入学年级">
      <el-option v-for="item in options.year" :key="item.value" :label="item.label" :value="item.value"></el-option>
    </el-select>
    <el-select v-model="value.major" placeholder="请选择专业">
      <el-option v-for="item in options.major" :key="item.value" :label="item.label" :value="item.value"></el-option>
    </el-select>
    <el-select v-model="value.grade" placeholder="请选择班级">
      <el-option v-for="item in options.grade" :key="item.value" :label="item.label" :value="item.value"></el-option>
    </el-select>
    <el-select v-model="value.status" placeholder="学生状态">
      <el-option v-for="item in options.status" :key="item.value" :label="item.label" :value="item.value"></el-option>
    </el-select>
    <el-input style="width: initial" placeholder="请输入学生姓名、身份证号" v-model="value.keyword"></el-input>
  </div>
</template>
<script>
export default {
  name: "search-bar-new",
  computed: {
    mode() {
      return "student";
    }
  },
  data(){
      return {
          value: {
              year: '',
              major: ''
          },
          options: {
              year: [],
              major: []
          }
      }
  }
};
</script>