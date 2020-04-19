<template>
  <el-select v-model="value" filterable  remote placeholder="请选择班级或搜索" class="search-grade" :remote-method="searchGrade" :loading="loading" :loading-text="loading_text">
    <el-option
      v-for="item in options"
      :label="item.name"
      :value="item.id"
      :key="item.id"
      >
    </el-option>
  </el-select>

</template>

<script>
  import {Util} from "../../common/utils";
  import {Constants} from "../../common/constants";

  export default {
    data() {
      return {
        options: [],
        value: '',
        loading: false,
        loading_text: '搜索中......'
      }
    },
    methods:{
        searchGrade: function (queryString) {
            const _queryString = queryString.trim();
            if(Util.isEmpty(_queryString)){
                // 如果视图搜索空字符串, 那么不执行远程调用, 而是直接回调一个空数组
                this.options = [];
            }
            else{
                this.loading = true;
                axios.post(
                    Constants.API.LOAD_GRADES_BY_NAME,
                    {grade_name: _queryString, school_id: 1}
                ).then(res => {
                    if(Util.isAjaxResOk(res)){
                        this.loading = false;
                        console.log(res.data.data);
                        this.options = res.data.data;
                    }
                })
            }
        }

     }
  }
</script>

<style scoped>
.search-grade {
  width: 800px;
}
</style>

