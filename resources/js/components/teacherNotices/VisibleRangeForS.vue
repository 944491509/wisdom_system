<template>
  <el-form label-width="90px">
    <el-form-item>
      <el-tag
        @click="checkAll"
        class="organ-item"
        effect="plain">
        所有班级
      </el-tag>
      <el-tag
        v-if="isAdviser"
        class="organ-item"
        @click="loadYear"
        effect="plain">
        我的班级
      </el-tag>
    </el-form-item>
    <el-form-item label="年级：" v-if="!all">
       <el-tag
        v-for="item in years"
        class="organ-item"
        :key="item.year"
        @click="loadGrade(item)"
        effect="plain">
        {{item.text}}
      </el-tag>
    </el-form-item>
    <el-form-item label="班级：" v-if="!all">
       <el-tag
        v-for="item in grades"
        :key="item.grade_id"
        class="organ-item"
        @click="checkGrade(item)"
        effect="plain">
        {{item.name}}
      </el-tag>
    </el-form-item>
    <el-form-item label="已选班级：" >
      <template v-if="!all">
        <el-tag
          v-for="(item, index) in selectTags"
          :key="index"
          class="Oran-Tag"
          closable
          @close="deleteTag(item)"
          style="margin-right: 10px;color: #fff;background-color: #409EFF;position: relative;"
        >{{item.name}}</el-tag>
      </template>
      <template v-else>
        <el-tag
          class="Oran-Tag"
          closable
          @close="deleteAllTag"
          style="margin-right: 10px;color: #fff;background-color: #409EFF;position: relative;"
        >所有班级</el-tag>
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
      grades:[]
		};
	},
	mounted() {
    this.LoadisAdviser()
    this.getYears()
	},
	methods: {
     LoadisAdviser(){
       axios.post("/api/Oa/is-adviser").then(res => {
					if (Util.isAjaxResOk(res)) {
            console.log(res)
            this.isAdviser = Boolean(Number(res.data.data.is_adviser));
					}
				});
    },
    checkAll(){
      this.all = true
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
      this.$emit('confrim',this.visibleform.allOran ? 'all' : this.selectTags)
    },
    getYears(){
        axios.get(`/api/school/load-config-year?school_id=${this.schoolId}`).then(res => {
					if (Util.isAjaxResOk(res)) {
            console.log(res)
            this.years = res.data.data;
					}
				});
    },
    getGrade(item){
        axios.get(`/api/notice/grade-list?year=${item.year}`).then(res => {
					if (Util.isAjaxResOk(res)) {
            console.log(res)
            this.grades = res.data.data;
					}
				});
    },
    loadYear(){
      this.all = false;
      this.selectTags = [];
    },
    loadGrade(item){
      this.getGrade(item)
    },
    checkGrade(item){
     	let sign = this.selectTags.findIndex(e => e.grade_id == item.grade_id);
      if (sign != -1) {
        this.selectTags.splice(sign, 1);
      } else {
        this.selectTags.push(item);
      }
    },
    confrim(){
      this.$emit('confrim',this.all ? '0' : this.selectTags)
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

