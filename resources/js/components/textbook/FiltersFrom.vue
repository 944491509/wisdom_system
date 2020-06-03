<template>
  <div>
    <el-form :inline="true" :model="formInline" class="demo-form-inline">
      <el-form-item label="类型">
        <el-select v-model="formInline.type_id" placeholder="请选择">
          <el-option label="全部" value></el-option>
          <el-option v-for="type in types" :key="type.id" :label="type.type" :value="type.id"></el-option>
        </el-select>
      </el-form-item>
      <el-form-item label="适用年级">
        <el-select v-model="formInline.year" placeholder="请选择">
          <el-option label="全部" value></el-option>
          <el-option v-for="year in years" :key="year.year" :label="year.text" :value="year.year"></el-option>
        </el-select>
      </el-form-item>
      <el-form-item label="学期">
        <el-select v-model="formInline.term" placeholder="请选择">
          <el-option label="全部" value></el-option>
          <el-option label="第一学期" value="1"></el-option>
          <el-option label="第二学期" value="2"></el-option>
        </el-select>
      </el-form-item>
      <!-- <el-form-item label>
        <el-input v-model="formInline.keyword" placeholder="请输入教材名称"></el-input>
      </el-form-item> -->
      <el-form-item>
        <el-button type="primary" @click="onSubmit">查询</el-button>
      </el-form-item>
    </el-form>
    <slot></slot>
  </div>
</template>
<script>
export default {
	props: {
		years: {
			type: Array,
			default: function() {
				return [];
			}
		},
		types: {
			type: Array,
			default: function() {
				return [];
			}
		}
	},
	data() {
		return {
			formInline: {
				term: "",
				year: "",
				type_id: "",
				keyword: ""
			}
		};
	},
	methods: {
		onSubmit() {
			console.log("submit!");
			this.$emit("searchSubmit", this.formInline);
		}
	}
};
</script>
<style lang="scss" scoped>
.demo-form-inline {
	float: left;
}
</style>
