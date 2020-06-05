<template>
	<el-form>
		<!-- <el-form-item label="搜索" label-width="50px">
                  <el-input v-model="form.title" autocomplete="off" style="width: 90%;"></el-input>
		</el-form-item>-->
		<el-form-item label="部门：" label-width="90px">
			<el-row class="organ-row" v-for="(organ,index) in organizansList" :key="index">
				<template v-for="item in organ">
					<el-tag
						class="organ-item"
						:key="item.id"
						@click="chooseOrgan((index + 1) ,item)"
						effect="plain"
					>{{ item.name }} {{ item.status ? '...': ''}}</el-tag>
				</template>
			</el-row>
		</el-form-item>
		<el-form-item label="便捷操作：" label-width="90px" style="margin-top: 50px;">
			<el-switch v-model="visibleform.allOran" active-text="所有部门" inactive-text></el-switch>
		</el-form-item>
		<el-form-item label="已选部门：" label-width="90px" v-if="!visibleform.allOran">
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
	data() {
		return {
			visibleform: {
				allOran: false
			},
			selectTags: [],
			organizansList: []
		};
	},
	mounted() {
		this.getOrganizansList();
	},
	methods: {
		async getOrganizansList(floor = 0, parent_id) {
			console.log("getOrganizansList");
			await axios
				.post("/Oa/tissue/getOrganization", {
					school_id: this.schoolId,
					parent_id
				})
				.then(res => {
					if (Util.isAjaxResOk(res)) {
						if (!this.organizansList[floor]) {
							this.organizansList.push(res.data.data.organ || []);
						} else {
							this.organizansList.splice(floor, 1, res.data.data.organ || []);
						}
					}
				});
		},
		chooseOrgan(floor, item) {
			if (item.status) {
				this.getOrganizansList(floor, item.id);
			} else {
				let sign = this.selectTags.findIndex(e => e.id == item.id);
				// console.log(sign);
				if (sign != -1) {
					this.selectTags.splice(sign, 1);
				} else {
					this.selectTags.push(item);
				}
			}
		},
		deleteTag(tag) {
			this.selectTags.splice(this.selectTags.indexOf(tag), 1);
    },
    confrim(){
      this.$emit('confrim',this.visibleform.allOran ? '0' : this.selectTags)
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

