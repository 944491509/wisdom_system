<template>
    <el-form :inline="true" :model="formInline" class="demo-form-inline">
    <el-form-item label="适用年级">
        <el-select v-model="formInline.year" placeholder="请选择">
            <el-option label="全部" value=""></el-option>
            <el-option v-for="year in years" :key="year.year" :label="year.text" :value="year.year"></el-option>
        </el-select>
    </el-form-item>
    <el-form-item label="学期">
        <el-select v-model="formInline.term" placeholder="请选择">
            <el-option label="全部" value=""></el-option>
            <el-option label="第一学期" value="1"></el-option>
            <el-option label="第二学期" value="2"></el-option>
        </el-select>
    </el-form-item>
    <el-form-item label="专业">
        <el-select v-model="formInline.major_id" placeholder="请选择">
            <el-option label="全部" value=""></el-option>
            <el-option v-for="major in majors" :key="major.id" :label="major.name" :value="major.id"></el-option>
        </el-select>
    </el-form-item>
    <el-form-item label="">
    <el-input v-model="formInline.keyword" placeholder="请输入课程名称/授课教师姓名"></el-input>
  </el-form-item>
    <el-form-item>
        <el-button type="primary" @click="onSubmit">查询</el-button>
    </el-form-item>
    </el-form>
</template>
<script>
  export default {
    props:{
        years:{
            type: Array,
            default:function(){
                return []
            }
        },
        majors:{
            type: Array,
            default:function(){
                return []
            }
        }
    },
    data() {
      return {
        formInline: {
          term: '',
          year: '',
          major_id:'',
          keyword:''
        }
      }
    },
    methods: {
      onSubmit() {
        console.log('submit!');
        this.$emit('searchSubmit', this.formInline)
      }
    }
  }
</script>
<style lang="scss" scoped>
.demo-form-inline{
    float: left;
}
</style>