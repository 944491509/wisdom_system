<template>
  <div class="search-bar" style="width: 100%;">
    <div v-if="mode === 'students'">
      <el-select v-model="values.year" clearable placeholder="请选择入学年级">
        <el-option
          v-for="item in options.year"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        ></el-option>
      </el-select>
      <el-select v-model="values.major_id" clearable placeholder="请选择专业">
        <el-option
          v-for="item in options.major"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        ></el-option>
      </el-select>
      <el-select v-model="values.grade_id" clearable placeholder="请选择班级">
        <el-option
          v-for="item in options.grade"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        ></el-option>
      </el-select>
      <el-select v-model="values.status" clearable placeholder="学生状态">
        <el-option
          v-for="item in options.status"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        ></el-option>
      </el-select>
      <el-input style="width: 200px" placeholder="请输入学生姓名、身份证号" v-model="values.keyword"></el-input>
      <slot></slot>
      <slot name="opt"></slot>
    </div>
    <div v-if="mode === 'teachers'">
      <el-select v-model="values.status" clearable placeholder="请选择聘任状态">
        <el-option
          v-for="item in options.status"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        ></el-option>
      </el-select>
      <el-select v-model="values.mode" clearable placeholder="请选择聘任方式">
        <el-option
          v-for="item in options.mode"
          :key="item.value"
          :label="item.label"
          :value="item.label"
        ></el-option>
      </el-select>
      <el-select v-model="values.education" clearable placeholder="请选择学历">
        <el-option
          v-for="item in options.education"
          :key="item.value"
          :label="item.label"
          :value="item.label"
        ></el-option>
      </el-select>
      <el-select v-model="values.title" clearable placeholder="请选择职称">
        <el-option
          v-for="item in options.title"
          :key="item.value"
          :label="item.label"
          :value="item.label"
        ></el-option>
      </el-select>
      <el-input style="width: initial" placeholder="教职工姓名、手机号" v-model="values.keyword"></el-input>
      <slot></slot>
      <slot name="opt"></slot>
    </div>
    <div v-if="mode === 'users'">
      <el-input style="width: 200px" placeholder="请输入学生姓名、身份证号" v-model="values.keyword"></el-input>
      <slot name="opt"></slot>
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
        major_id: null,
        grade_id: null,
        mode: null,
        title: null,
        education: null,
        status: null,
        keyword: ""
      },
      options: {
        year: [],
        major: [],
        grade: [],
        mode: [],
        title: [],
        status: [],
        education: []
      }
    };
  },
  watch: {
    "values.major_id": function(nval) {
      if (!nval) {
        this.values.grade = null;
        this.options.grade = [];
        return;
      }
      axios.post(Constants.API.LOAD_GRADES_BY_MAJOR, { id: nval }).then(res => {
        if (Util.isAjaxResOk(res)) {
          this.options.grade = this.toOptions(
            res.data.data.grades,
            "name",
            "id"
          );
          this.values.grade = null;
        }
      });
    },
    values: {
      deep: true,
      immediate: true,
      handler: function(where) {
        let param = where;
        Object.keys(param).forEach(key => {
          if (!param[key] && param[key] !== 0) {
            delete param[key];
          }
        });
        this.$emit("input", param);
      }
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
      axios.get("/api/notice/school-year?school_id=" + this.schoolid).then(res => {
        if (Util.isAjaxResOk(res)) {
          this.options.year = this.toOptions(res.data.data, "name", "year");
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
      axios.post("/api/pc/get-search-student-status").then(res => {
        if (Util.isAjaxResOk(res)) {
          this.options.status = this.toOptions(
            Object.keys(res.data.data).map(k => {
              return {
                name: res.data.data[k],
                id: k
              };
            }),
            "name",
            "id"
          );
        }
      });
    },
    initTeacherOptions() {
      [
        {
          code: 2,
          field: "education"
        },
        {
          code: 4,
          field: "title"
        },
        {
          code: 6,
          field: "mode"
        },
        {
          code: 5,
          field: "status"
        }
      ].forEach(item => {
        axios
          .post("/api/pc/get-search-config", {
            type: item.code
          })
          .then(res => {
            if (Util.isAjaxResOk(res)) {
              this.options[item.field] = this.toOptions(
                res.data.data,
                "name",
                "id"
              );
            }
          });
      });
    }
  },
  created() {
    if (this.mode === "students") {
      this.initStudentOptions();
    } else {
      this.initTeacherOptions();
    }
  }
};
</script>
<style lang="scss" scoped>
.search-bar {
  margin: 12px 0;
}
</style>
