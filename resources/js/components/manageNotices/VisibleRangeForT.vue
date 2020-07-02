<template>
	<el-form>
		<el-form-item label="搜索" label-width="60px" >
			<el-autocomplete
				v-model="visibleform.title"
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
		<el-form-item label="部门" label-width="60px">
			<el-cascader style="width:100%;" popper-class="tags_cascader" ref="tags" :props="props" v-model="visibleform.tags" @change="changeTag"></el-cascader>
		</el-form-item>
		<el-form-item label="便捷操作" label-width="90px" style="margin-top: 50px;">
			<el-switch v-model="visibleform.allOran" active-text="所有部门" inactive-text></el-switch>
		</el-form-item>
		<el-form-item label="已选部门" label-width="90px" v-if="!visibleform.allOran"></el-form-item>
		<el-form-item v-if="!visibleform.allOran">
			<el-tag
				v-for="(item, index) in selectTags"
				:key="index"
        		class="Oran-Tag"
				closable
				@close="deleteTag(item)"
				style="margin-right: 10px;color: #fff;background-color: #409EFF;position: relative;"
			>{{item.name}}</el-tag>
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
			visibleform: {
				allOran: false,
				title:'',
				tags:[]
			},
			selectTags: [],
			organizansList: [],
			props: {
				lazy: true,
				label:'name',
				value:'id',
				multiple: true,
        leaf:'status',
        checkStrictly:true,
				lazyLoad :async (node, resolve) => {
					let organizansList = [];
					if(node.level == 0){
						organizansList = await this.getOrganizansList();
					}else{
						organizansList = await this.getOrganizansList(node.data.id);
					}
					organizansList.forEach(e=>e.status = !e.status)
					resolve(organizansList)
				}
			}
		};
	},

	methods: {
    initData() {
      this.visibleform.allOran = false
      this.selectTags = []
    },
    thandleOpen(val) {
      if ( val && val[0] && val[0].organization_id === 0) {
        this.visibleform.allOran = true
      } else {
        this.visibleform.allOran = false
        this.visibleform.tags = val.map(e=>[e.organization_id])
        this.selectTags = val
      }
    },
		async getOrganizansList( parent_id = 0,queryString) {
			let organizansList  = [];
			await axios
				.post("/Oa/tissue/getOrganization", {
					school_id: this.schoolId,
					parent_id,
					keyword:queryString
				})
				.then(res => {
					if (Util.isAjaxResOk(res)) {
						organizansList = res.data.data.organ || []
					}
				});
			return organizansList;
		},
		async querySearchAsync(queryString, cb){
      if(!queryString) {
        cb([]);
        return;
      } ;
			let organ = await  this.getOrganizansList(0,queryString)
			organ = organ.filter(e => !e.status)
			cb(organ || [])
		},
		handleSelect(item){
			if(!this.selectTags.find(e => e.id == item.id)){
				this.selectTags.push(item);
				this.visibleform.tags.push(item.id)
			}

		},
		changeTag() {
			setTimeout(()=>{
        let tags = this.$refs.tags.getCheckedNodes().map(e=>e.data);
        console.log(tags)
				if(tags.length){
					this.selectTags = tags
				}
			},500)
		},
		deleteTag(tag) {
			this.selectTags.splice(this.selectTags.indexOf(tag), 1);
			this.visibleform.tags = this.visibleform.tags.filter(arr => !arr.includes(tag.id))
		},
		confrim(){
      this.$emit('confrim',this.visibleform.allOran ? '0' : this.selectTags)
      this.selectTags = []
		}
	}
};
</script>

<style lang="scss" scoped>
.organ-row {
	border-bottom: 1px solid #ddd;
	margin-bottom: 9px;
	padding-bottom: 10px;
	.organ-item {
		margin-right: 30px;
		cursor: pointer;
		&:hover {
			background-color: #b3d8ff;
		}
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
 .tags_cascader  .el-checkbox{
	position: absolute;
	left: 19px;
	right: 30px;
}
.tags_cascader  .el-cascader-node__label{
	padding-left: 24px;
}
/* .el-tag .el-icon-close {
	    background-color:#fff;
} */
</style>

