<template>
  <el-form>
    <el-form-item label="搜索" label-width="60px" >
			<el-autocomplete
				v-model="form.title"
				:fetch-suggestions="querySearchAsync"
				placeholder="请输入班级名称"
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
        <el-cascader style="width:100%;" popper-class="tags_stu_cascader" ref="tags" :disabled="form.allOran" :props="props" @change="changeCascader" v-model="form.tags">
          <template slot-scope="{ node }">
            <span style="display:block;" @click="hanldeClick(node.data)" v-if="node.level == 1">{{ node.data.name }}</span>
            <el-checkbox v-else-if="currentNode.year == node.data.year"  @click="changeCascader" :label="node.data" :value="node.data">{{node.data.name}}</el-checkbox>
          </template>
        </el-cascader>
      </el-checkbox-group>
		</el-form-item>
  	<el-form-item label="便捷操作" label-width="90px" style="margin-top: 50px;">
			<el-switch v-model="form.allOran" active-text="所有班级" inactive-text @change="switchChange"></el-switch>
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
      currentNode: [],
      props: {
        lazy: true,
        value:'year',
				lazyLoad :async (node, resolve) => {
          console.log("node",node)
          if(node.level == 0){
            let years = await this.getYears();
            resolve(years)
            return;
          }

          if(node.level == 1){
            this.currentNode = node.data;
            let grades = await this.getGrade(node.data)
            grades.map(e =>{
              e.leaf = true
            })
            resolve(grades)
          }
          resolve([])


				}
			}
		};
	},
	methods: {
    switchChange(val){
      if(val){
        this.selectTags = []
				this.form.tags = []
      }
    },
    hanldeClick(data){
      console.log('handleClick',data)
      this.currentNode = data;
    },
    initData() {
      this.form.allOran = false
      this.selectTags = []
    },
    shandleOpen(val) {
      if (val[0] && val[0].grade_id === 0) {
        this.form.allOran = true
      } else {
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
      this.$emit('confrim',this.form.allOran ? '0' : this.selectTags)
    },
    async getYears(){
      let res = await axios.get(`/api/notice/school-year?school_id=${this.schoolId}`)
      if (Util.isAjaxResOk(res)) {
        console.log(res)
        this.years = res.data.data;
        return res.data.data;
      }
      return [] ;
    },
    async getGrade(item,keyword =''){
       let res =  await axios.get(`/api/notice/grade-list?year=${item.year || ''}&keyword=${keyword}&school_id=${this.schoolId}`)
        if (Util.isAjaxResOk(res)) {
          console.log(res)
          this.grades = res.data.data;
          return res.data.data;
        }
        return [] ;
    },
    confrim(){
      this.$emit('confrim',this.form.allOran ? '0' : this.selectTags)
    },
    handleSelect(item){
			if(!this.selectTags.find(e => e.id == item.id)){
				this.selectTags.push(item);
				this.form.tags.push(item.id)
			}

		},
		deleteTag(tag) {
			this.selectTags.splice(this.selectTags.indexOf(tag), 1);
			this.form.tags = this.form.tags.filter(arr => !arr.includes(tag.id))
    },
    async querySearchAsync(queryString, cb){
      if(!queryString) {
        cb([]);
        return;
      } ;
			let items = await  this.getGrade({},queryString)
			cb(items || [])
    },
    changeCascader(){
       this.$refs.tags.toggleDropDownVisible()
    }
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
