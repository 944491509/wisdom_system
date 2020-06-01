<template>
  <div class="search-bar">
    <div v-if="mode === 'students'">
      <el-select v-model="values.year" placeholder="请选择入学年级">
        <el-option
          v-for="item in options.year"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        ></el-option>
      </el-select>
      <el-select v-model="values.major" placeholder="请选择专业">
        <el-option
          v-for="item in options.major"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        ></el-option>
      </el-select>
      <el-select v-model="values.grade" placeholder="请选择班级">
        <el-option
          v-for="item in options.grade"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        ></el-option>
      </el-select>
      <el-select v-model="values.status" placeholder="学生状态">
        <el-option
          v-for="item in options.status"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        ></el-option>
      </el-select>
      <el-input style="width: initial" placeholder="请输入学生姓名、身份证号" v-model="values.keyword"></el-input>
    </div>
  </div>
</template>
<script>
import { Util } from "../../common/utils";
import { Constants } from "../../common/constants";

export default {
  name: "search-bar-new",
  props: {
    mode: {
      type: String,
      default: "students"
    },
    schoolid: {
      required: true
    }
  },
  data() {
    return {
      values: {
        year: null,
        major: null
      },
      options: {
        year: [],
        major: []
      }
    };
  },
  watch: {
    "values.major": function(nval) {
      axios
        .post(Constants.API.LOAD_GRADES_BY_MAJOR, { id: nval })
        .then(res => {
          if (Util.isAjaxResOk(res)) {
            this.options.grade = this.toOptions(
              res.data.data.grades,
              "name",
              "id"
            );
          }
        });
    }
  },
  methods: {
    toOptions(list, label, value) {
      return list.map((item, index) => {
        return {
          label: item[label],
          value: item[value],
          key: index
        };
      });
    },
    initStudentOptions() {
      // api/school/load-config-year
      axios
        .get("/api/school/load-config-year?school_id=" + this.schoolid)
        .then(res => {
          if (Util.isAjaxResOk(res)) {
            this.options.year = this.toOptions(res.data.data, "text", "year");
          }
        });
      axios
        .post(Constants.API.LOAD_MAJORS_BY_SCHOOL, {
          id: this.schoolid,
          pageNumber: 0
        })
        .then(res => {
          if (Util.isAjaxResOk(res)) {
            this.options.major = this.toOptions(
              res.data.data.majors,
              "name",
              "id"
            );
          }
        });
    }
  },
  created() {
    if (this.mode === "students") {
      this.initStudentOptions();
    }
  }
};
</script>
<style lang="scss" scoped>
.search-bar {
  margin: 12px 0;
}
</style>