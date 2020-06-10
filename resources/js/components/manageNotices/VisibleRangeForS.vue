<template>
  <el-form>
    <el-form-item label="搜索" label-width="60px" >
			<el-autocomplete
				v-model="form.title"
				:fetch-suggestions="querySearchAsync"
				placeholder="请输入部门名称"
				style="width:100%;"
				@select="handleSelect"
				>
				<template slot-scope="{ item }">
					<div class="name">{{ item.name }}</div>
				</template>
				</el-autocomplete>
		</el-form-item>
		<el-form-item label="班级" label-width="60px">
      <el-checkbox-group v-model="selectTags">
        <el-cascader style="width:100%;" popper-class="tags_stu_cascader" ref="tags" :props="props" :show-all-levels="false" v-model="form.tags">
          <template slot-scope="{ node, data }">
            <span v-if="node.level == 1">{{ data.name }}</span>
            <el-checkbox v-else :label="data" :value="data">{{data.name}}</el-checkbox>
          </template>
        </el-cascader>
      </el-checkbox-group>
		</el-form-item>
  	<el-form-item label="便捷操作" label-width="90px" style="margin-top: 50px;">
			<el-switch v-model="form.allOran" active-text="所有班级" inactive-text></el-switch>
		</el-form-item>
    <el-form-item label="已选班级：" v-if="!form.allOran" >
      <template>
        <el-tag
          v-for="(item, index) in selectTags"
          :key="index"
          class="Oran-Tag"
          closable
          @close="deleteTag(item)"
          style="margin-right: 10px;color: #fff;background-color: #409EFF;position: relative;"
        >{{item.name}}</el-tag>
      </template>
		</el-form-item>
    <div class="btn-tools">
			<el-button type="primary" @click="confrim" style="padding: 12px 40px;">确认</el-button>
		</div>
  </el-form>

</template>
<script>
import { Util } from "../../common/utils";
export default {
  props: ["schoolId"],
	data() {
		return {
      all:false,
      selectTags: [],
      isAdviser:false,
      years:[],
      grades:[],
      form: {
				allOran: false,
				title:'',
				tags:[]
      },
      props: {
        lazy: true,
        value:'year',
				lazyLoad :async (node, resolve) => {
          console.log(node)
          if(node.level == 0){
            await this.getYears();
            resolve(this.years)
            return;
          }
          if(node.level == 1){
            await this.getGrade(node.data)
            resolve(this.grades)
          }
          resolve()
          
         
				}
			}
		};
	},
	methods: {
    initData() {
      this.form.allOran = false
      this.selectTags = []
    },
    shandleOpen(val) {
      console.log(val)
      if (val[0] && val[0].grade_id === 0) {
        console.log('111')
        this.form.allOran = true
      } else {
        console.log('222')
        this.form.allOran = false
        this.selectTags = val
      }
    },
     LoadisAdviser(){
       axios.post("/api/Oa/is-adviser").then(res => {
					if (Util.isAjaxResOk(res)) {
            console.log(res)
            this.isAdviser = Boolean(Number(res.data.data.is_adviser));
					}
				});
    },
   
    deleteAllTag(){
      this.all = false;
      this.selectTags = [];
    },
		deleteTag(tag) {
			this.selectTags.splice(this.selectTags.indexOf(tag), 1);
    },
    confrim(){
      console.log('confrim')
      this.$emit('confrim',this.form.allOran ? '0' : this.selectTags)
    },
    async getYears(){
       await axios.get(`/api/notice/school-year?school_id=${this.schoolId}`).then(res => {
					if (Util.isAjaxResOk(res)) {
            console.log(res)
            this.years = res.data.data;
					}
				});
    },
    async getGrade(item,keyword =''){
        await axios.get(`/api/notice/grade-list?year=${item.year || ''}&keyword=${keyword}&school_id=${this.schoolId}`).then(res => {
					if (Util.isAjaxResOk(res)) {
            console.log(res)
            this.grades = res.data.data;
					}
        });
        return this.grades;
    },
    confrim(){
      this.$emit('confrim',this.form.allOran ? '0' : this.selectTags)
    },
    handleSelect(item){
			console.log(item)
			if(!this.selectTags.find(e => e.grade_id == item.grade_id)){
				this.selectTags.push(item);
				this.form.tags.push(item.id)
			}

		},
		deleteTag(tag) {
			this.selectTags.splice(this.selectTags.indexOf(tag), 1);
			console.log('deleteTag',tag)
			this.form.tags = this.form.tags.filter(arr => !arr.includes(tag.id))
    },
    async querySearchAsync(queryString, cb){
      if(!queryString) {
        cb();
        return;
      } ;
			let items = await  this.getGrade({},queryString)
			cb(items || [])
		},
	}
};
</script>

<style lang="scss" scoped>
.organ-row {
	border-bottom: 1px solid #ddd;
	margin-bottom: 9px;
	padding-bottom: 10px;
}
.organ-item {
		margin-right: 30px;
		cursor: pointer;
		&:hover {
			background-color: #b3d8ff;
		}
	}
.btn-tools{
  text-align: center;
}

</style>
<style>
.Oran-Tag {
  background-color: rgba(232, 244, 255, 1) !important;
  color: rgba(78, 165, 254, 1) !important;
}
.tags_stu_cascader  .el-icon-check{
  display: none;
}
</style>
