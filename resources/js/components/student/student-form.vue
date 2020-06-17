<template>
  <div class="teacher-edit-form">
    <el-form
      label-position="top"
      :model="ruleForm"
      :rules="rules"
      ref="ruleForm"
      label-width="100px"
      style="padding: 20px"
    >
      <el-row
        :gutter="20"
        v-for="(group, index) in form"
        :key="index"
        :style="group.hidden === 'none'?{'display':'none'}:{}"
      >
        <div class="form-divider" v-if="group.title">
          <span>{{group.title}}</span>
          <el-divider></el-divider>
        </div>
        <el-col
          :span="isNaN(group.span)?null:group.span"
          v-for="(field, index_) in group.fields"
          :class="field.type === 'arearemote' ? ('arealevel'+field.level): (group.span ==='x'?'col--5':'')"
          :style="field.hidden === 'none'?{'display':'none'}:((field.type === 'empty' || field.hidden === 'hidden')? {'visibility':'hidden'}:{})"
          :key="index_"
        >
          <el-form-item :label="field.type === 'empty'?'empty':field.name" :prop="field.key">
            <el-input
              v-if="field.type==='text'"
              v-model="field.value"
              :maxlength="field.maxlength || 50"
            ></el-input>
            <el-select
              v-else-if="field.type==='select'"
              v-model="field.value"
              :filterable="field.filterable"
            >
              <el-option
                v-for="(item, index) in field.options"
                :key="index"
                :label="item.label"
                :value="item.value"
              ></el-option>
            </el-select>
            <el-date-picker
              v-else-if="field.type==='date'"
              v-model="field.value"
              type="date"
              format="yyyy-MM-dd"
              value-format="yyyy-MM-dd"
              :picker-options="{disabledDate(time) {
                                                return time.getTime() > Date.now();
                                            }}"
              placeholder="选择日期"
            ></el-date-picker>
            <number-input v-else-if="field.type==='number'" :decimalLen="0" v-model="field.value"></number-input>
            <area-selector v-else-if="field.type==='areas'" v-model="field.value"></area-selector>
            <area-selector-remote
              v-else-if="field.type==='arearemote'"
              :level="field.level"
              v-model="field.value"
              :ref="field.key+'arearemote'"
            ></area-selector-remote>
            <el-input v-else readonly></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row :gutter="20" v-if="$attrs.status != 1">
        <div class="form-divider">
          <span>奖惩信息</span>
          <el-divider></el-divider>
        </div>
        <el-col :span="24">
          <el-form-item label="奖励记录" prop="reward">
            <el-input type="textarea" v-model="reward" :maxlength="500"></el-input>
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="惩罚记录" prop="punishment">
            <el-input type="textarea" v-model="punishment" :maxlength="500"></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <el-form-item>
        <el-button :loading="pending" type="primary" @click="submitForm">提交</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>
<script>
import NumberInput from "../common/number-input";
import AreaSelector from "../common/area-selector";
import AreaSelectorRemote from "../common/area-each-selector";
import { Util } from "../../common/utils";
import { Constants } from "../../common/constants";

// const BASE_URL = "http://localhost:9999";
const BASE_URL = "";

export default {
  name: "teacher-form",
  components: {
    NumberInput,
    AreaSelector,
    AreaSelectorRemote
  },
  methods: {
    submitForm() {
      this.$refs.ruleForm.validate();
      try {
        this.form.forEach(group => {
          if (group.hidden) {
            return;
          }
          group.fields.forEach(field => {
            if (
              field.validator &&
              field.validator[0] &&
              !field.hidden &&
              field.key &&
              ((field.validator[0].required &&
                !field.value &&
                field.value !== 0) ||
                field.validator[0].validator(null, field.value) === false)
            ) {
              this.$message({
                message: field.validator[0].message,
                type: "error"
              });
              throw new Error("校验未通过");
            }
            if (field.key === "profile$resident") {
              if (
                !field.value ||
                !field.value[0] ||
                !field.value[1] ||
                !field.value[2]
              ) {
                this.$message({
                  message: field.validator[0].message,
                  type: "error"
                });
                throw new Error("校验未通过");
              }
            }
            if (field.key === "profile$source_place") {
              if (!field.value || !field.value[0] || !field.value[1]) {
                this.$message({
                  message: field.validator[0].message,
                  type: "error"
                });
                throw new Error("校验未通过");
              }
            }
          });
        });
        this.pending = true;
        let params = {
          status: this.status, // 2 学生  1 未认证
          ...this.ruleForm
        };
        Object.keys(params).forEach(key => {
          if (key.indexOf("$") > -1) {
            let tk = key.split("$");
            if (!params[tk[0]]) {
              params[tk[0]] = {};
            }
            params[tk[0]][tk[1]] = params[key];
            delete params[key];
          }
        });
        let url = "/school_manager/student/create";
        if (this.student_id) {
          params.student_id = this.student_id;
          url = "/school_manager/student/update";
        }
        if (!params.addition) {
          params.addition = {};
        }
        if (params.profile && params.profile.resident) {
          params.profile.resident_state = params.profile.resident[0];
          params.profile.resident_city = params.profile.resident[1];
          params.profile.resident_area = params.profile.resident[2];
          params.profile.resident_suburb = params.profile.resident[3];
          params.profile.resident_village = params.profile.resident[4];
          delete params.profile.resident;
        }
        if (params.profile && params.profile.source_place) {
          params.profile.source_place_state = params.profile.source_place[0];
          params.profile.source_place_city = params.profile.source_place[1];
          delete params.profile.source_place;
        }
        if (params.profile && params.profile.country) {
          params.profile.country = params.profile.country[0];
        }
        params.addition.reward = this.reward;
        params.addition.punishment = this.punishment;
        axios.post(BASE_URL + url, params).then(res => {
          this.pending = false;
          if (Util.isAjaxResOk(res)) {
            if (this.student_id) {
              this.$message({
                message: "保存成功",
                type: "success"
              });
              setTimeout(function() {
                window.history.go(-1);
              }, 1200);
            } else {
              this.$alert(res.data.message, "添加成功", {
                confirmButtonText: "我知道了",
                callback: action => {
                  window.location.href = "/school_manager/school/students";
                }
              });
            }
          } else {
            this.$message({
              message: res.data.message,
              type: "error"
            });
          }
        });
      } catch (e) {}
    },
    setData(data) {
      Object.keys(data).forEach(key => {
        if (typeof data[key] === "object") {
          // 把户籍和 生源地转成数组
          Object.keys(data[key]).forEach(subkey => {
            if (["reward", "punishment"].includes(subkey)) {
              this[subkey] = data[key][subkey];
              return;
            }
            let resident = [
              "resident_state",
              "resident_city",
              "resident_area",
              "resident_suburb",
              "resident_village"
            ];
            let source_place = ["source_place_state", "source_place_city"];
            if (resident.includes(subkey)) {
              if (!data[key + "$resident"]) {
                data[key + "$resident"] = [];
              }
              let index = resident.indexOf(subkey);
              data[key + "$resident"][index] = data[key][subkey];
            } else if (source_place.includes(subkey)) {
              if (!data[key + "$source_place"]) {
                data[key + "$source_place"] = [];
              }
              let index = source_place.indexOf(subkey);
              data[key + "$source_place"][index] = data[key][subkey];
            } else {
              data[key + "$" + subkey] = data[key][subkey];
            }
          });
          delete data[key];
        }
      });
      this.form.forEach(group => {
        group.fields.forEach(field => {
          field.value = data[field.key];
        });
      });
      if (this.$refs.profile$residentarearemote) {
        this.$refs.profile$residentarearemote[0].setData(data.profile$resident);
      }
      if (this.$refs.profile$source_placearearemote) {
        this.$refs.profile$source_placearearemote[0].setData(
          data.profile$source_place
        );
      }
      if (this.$refs.profile$countryarearemote) {
        this.$refs.profile$countryarearemote[0].setData([data.profile$country]);
      }
      this.major_initing = data.grade_id;
    },
    resetGrade(major_id) {
      if (this.gradeOptCache && this.gradeOptCache[major_id]) {
        this.form.forEach(group => {
          group.fields.forEach(filed => {
            if (filed.key === "grade_id") {
              filed.value = this.major_initing || "";
              this.major_initing = "";
              filed.options = this.gradeOptCache[major_id];
            }
          });
        });
      } else {
        axios
          .post("/api/school/load-major-grades", {
            id: major_id
          })
          .then(res => {
            if (Util.isAjaxResOk(res)) {
              let options = res.data.data.grades.map((item, index) => {
                return {
                  label: item.name,
                  value: item.id
                };
              });
              if (!this.gradeOptCache) {
                this.gradeOptCache = {};
              }
              this.gradeOptCache[major_id] = options;
              this.form.forEach(group => {
                group.fields.forEach(field => {
                  if (field.key === "grade_id") {
                    field.options = options;
                    if (
                      field.value &&
                      options.find(item => item.value === field.value)
                    ) {
                      return;
                    }
                    field.value = "";
                  }
                });
              });
            }
          });
      }
    }
  },
  computed: {
    rules() {
      return (forms => {
        let obj = {};
        forms.forEach(group => {
          group.fields.forEach(field => {
            obj[field.key] = field.validator || [
              {
                required: false,
                validator: (r, v, c) => {
                  c && c();
                }
              }
            ];
          });
        });
        return obj;
      })(this.form);
    },
    ruleForm() {
      return (forms => {
        let obj = {};
        forms.forEach(group => {
          group.fields.forEach(field => {
            if (field.key) {
              obj[field.key] = field.value;
            }
          });
        });
        return obj;
      })(this.form);
    }
  },
  data() {
    return {
      pending: false,
      reward: "",
      punishment: "",
      form: [
        {
          title: "",
          span: "x",
          fields: [
            {
              key: "user$name",
              name: "姓名",
              type: "text",
              value: "",
              maxlength: 10,
              validator: [
                {
                  required: true,
                  trigger: "blur",
                  message: "请填写姓名",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "profile$gender",
              name: "性别",
              type: "select",
              value: "",
              options: [
                {
                  label: "男",
                  value: 1
                },
                {
                  label: "女",
                  value: 2
                }
              ],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "message",
                  message: "请选择学生性别",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "profile$nation_name",
              name: "民族",
              type: "select",
              value: "",
              filterable: true,
              code: 0,
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择学生民族",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "profile$political_name",
              name: "政治面貌",
              type: "select",
              code: 1,
              value: "",
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择政治面貌",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: this.$attrs.status == 1 ? "" : "profile$birthday",
              name: "出生日期",
              type: "date",
              value: "",
              hidden: this.$attrs.status == 1 ? "hidden" : false,
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择学生出生日期",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "user$mobile",
              name: "本人电话号码",
              type: "number",
              value: "",
              validator: [
                {
                  required: true,
                  message: "请输入正确的手机号！",
                  validator: (r, v, c) => {
                    if (!/^1[3-9]\d{9}$/.test(v)) {
                      c && c(new Error(""));
                      return false;
                    } else {
                      c && c();
                    }
                  },
                  trigger: "blur"
                }
              ]
            },
            {
              key: "profile$id_number",
              name: "身份证号",
              type: "number",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "blur",
                  message: "请输入正确的身份证号！",
                  validator: (r, v, c) => {
                    if (
                      !/^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/.test(
                        v
                      )
                    ) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "profile$student_code",
              name: "学籍号",
              type: "text",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "blur",
                  message: "请输入学生学籍号",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "profile$country",
              name: "籍贯",
              type: "arearemote",
              level: 1,
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "blur",
                  message: "请输入学生籍贯地",
                  validator: (r, v, c) => {
                    if (!v || !v[0]) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: this.$attrs.status == 1 ? "" : "profile$health_status",
              name: "健康状况",
              type: "select",
              hidden: this.$attrs.status == 1 ? "hidden" : false,
              isId: true,
              code: 7,
              value: "",
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择学生健康状况",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            }
          ]
        },
        {
          title: "招生信息",
          span: "x",
          fields: [
            {
              key: "profile$graduate_school",
              name: "毕业学校",
              type: "text",
              value: "",
              maxlength: 20,
              validator: [
                {
                  required: true,
                  trigger: "blur",
                  message: "请填写毕业学校",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: this.$attrs.status == 1 ? "" : "profile$graduate_type",
              name: "学生来源（招生对象）",
              type: "select",
              value: "",
              hidden: this.$attrs.status == 1 ? "none" : false,
              options: [
                {
                  label: "应届",
                  value: "应届"
                },
                {
                  label: "往届",
                  value: "往届"
                }
              ],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择学生来源",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: this.$attrs.status == 1 ? "" : "profile$cooperation_type",
              name: "联招合作类型",
              type: "select",
              hidden: this.$attrs.status == 1 ? "none" : false,
              code: 16,
              isId: true,
              value: "",
              options: [
                {
                  label: "应届",
                  value: "应届"
                },
                {
                  label: "往届",
                  value: "往届"
                }
              ],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择招生类型",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "profile$source_place",
              name: "生源地",
              type: "arearemote",
              level: 2,
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择生源地",
                  validator: (r, v, c) => {
                    if (!v || !v[0] || !v[1]) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              type: "empty",
              hidden: this.$attrs.status == 1 ? "none" : false
            },
            {
              key: "profile$recruit_type",
              name: "招生方式",
              type: "select",
              code: 17,
              value: "",
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择招生方式",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: this.$attrs.status != 1 ? "" : "profile$graduate_type",
              name: "学生来源",
              type: "select",
              value: "",
              hidden: this.$attrs.status != 1 ? "none" : false,
              options: [
                {
                  label: "应届",
                  value: "应届"
                },
                {
                  label: "往届",
                  value: "往届"
                }
              ],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择学生来源",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              type: "empty",
              hidden: this.$attrs.status != 1 ? "none" : false
            },
            {
              key: "profile$volunteer",
              name: "报名志愿",
              type: "text",
              value: ""
            },
            {
              key: "profile$license_number",
              name: "准考证号",
              type: "text",
              value: ""
            },
            {
              key: "profile$examination_site",
              name: "考点",
              type: "text",
              value: ""
            },
            {
              key: "profile$examination_score",
              name: "考试成绩",
              type: "text",
              value: ""
            }
          ]
        },
        {
          title: "家庭信息",
          span: "x",
          fields: [
            {
              key: "profile$resident_type",
              name: "户口性质",
              type: "select",
              value: "",
              options: [
                {
                  label: "农业",
                  value: "农业"
                },
                {
                  label: "非农业",
                  value: "非农业"
                }
              ],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择户口性质",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "profile$resident",
              name: "户籍地（户口所在地）",
              type: "arearemote",
              level: 5,
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择户籍地",
                  validator: (r, v, c) => {
                    if (!v || !v[0] || !v[1] || !v[2]) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "profile$detailed_address",
              name: "户籍详细地址",
              type: "text",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "blur",
                  message: "请填写户籍详细地址",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key:
                this.$attrs.status == 1 ? "" : "profile$family_poverty_status",
              name: "家庭贫困程度",
              hidden: this.$attrs.status == 1 ? "none" : false,
              type: "select",
              code: 12,
              isId: true,
              value: "",
              options: []
            },
            {
              key: this.$attrs.status == 1 ? "" : "profile$zip_code",
              name: "家庭地址邮编",
              type: "number",
              hidden: this.$attrs.status == 1 ? "none" : false,
              value: ""
            },
            {
              key: this.$attrs.status == 1 ? "" : "profile$residence_type",
              name: "学生居住地类型",
              type: "select",
              hidden: this.$attrs.status == 1 ? "none" : false,
              code: 8,
              isId: true,
              value: "",
              options: []
            },
            {
              key: "profile$current_residence",
              name: "现居住地址",
              type: "text",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "blur",
                  message: "请填写现居住地址",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: this.$attrs.status == 1 ? "" : "profile$create_file",
              name: "是否建档立卡贫困户",
              type: "select",
              hidden: this.$attrs.status == 1 ? "none" : false,
              value: "",
              options: [
                {
                  value: 1,
                  label: "是"
                },
                {
                  value: 0,
                  label: "否"
                }
              ],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请确定是否建档立卡贫困户",
                  validator: (r, v, c) => {
                    if (v == null || v == undefined) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "profile$parent_name",
              name: "监护人姓名",
              type: "text",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "blur",
                  message: "请填写监护人姓名",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "profile$parent_mobile",
              name: "监护人电话",
              type: "number",
              value: "",
              validator: [
                {
                  required: true,
                  message: "请输入正确的手机号！",
                  validator: (r, v, c) => {
                    if (!/^1[3-9]\d{9}$/.test(v)) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  },
                  trigger: "blur"
                }
              ]
            },
            {
              key: "profile$relationship",
              name: "与本人关系",
              type: "select",
              value: "",
              code: 11,
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择学生与监护人关系",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              type: "empty",
              hidden: this.$attrs.status != 1 ? "none" : false
            },
            {
              key: this.$attrs.status != 1 ? "" : "profile$create_file",
              name: "是否建档立卡",
              type: "select",
              hidden: this.$attrs.status != 1 ? "none" : false,
              value: "",
              options: [
                {
                  value: 1,
                  label: "是"
                },
                {
                  value: 0,
                  label: "否"
                }
              ],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请确定是否建档立卡",
                  validator: (r, v, c) => {
                    if (v == null || v == undefined) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            }
          ]
        },
        {
          title: "在校信息",
          hidden: this.$attrs.status == 1 ? "none" : false,
          span: "x",
          fields: [
            {
              key: "profile$enrollment_at",
              name: "入学年月",
              type: "date",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择学生入学年月",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "major_id",
              name: "专业",
              type: "select",
              value: "",
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择专业",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "grade_id",
              name: "班级",
              type: "select",
              value: "",
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择班级",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "profile$learning_form",
              name: "学习形式",
              type: "select",
              value: "",
              options: [
                {
                  label: "全日制",
                  value: "全日制"
                },
                {
                  label: "非全日制",
                  value: "非全日制"
                }
              ]
            },
            {
              key: "profile$educational_system",
              name: "学制",
              type: "select",
              code: 13,
              isId: true,
              value: "",
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择学制",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "profile$entrance_type",
              name: "入学方式",
              type: "select",
              code: 9,
              isId: true,
              value: "",
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择入学方式",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "profile$student_type",
              name: "学生类别",
              type: "select",
              code: 14,
              isId: true,
              value: "",
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择学生类别",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "profile$segmented_type",
              name: "分段培养方式",
              type: "select",
              code: 10,
              isId: true,
              value: "",
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择学生类别",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              type: "empty"
            },
            {
              type: "empty"
            },
            {
              key: "profile$student_number",
              name: "学号",
              type: "text",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "blur",
                  message: "请填学号",
                  validator: (r, v, c) => {
                    if (!v) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  }
                }
              ]
            },
            {
              key: "profile$qq",
              name: "QQ号",
              type: "number",
              value: ""
            },
            {
              key: "profile$wx",
              name: "微信号",
              type: "text",
              value: ""
            },
            {
              key: "user$email",
              name: "邮箱",
              type: "text",
              value: "",
              validator: [
                {
                  required: false,
                  message: "请输入正确的邮箱！",
                  validator: (r, v, c) => {
                    if (
                      !!v &&
                      !/^[A-Za-z0-9]+([_\.][A-Za-z0-9]+)*@([A-Za-z0-9\-]+\.)+[A-Za-z]{2,6}$/.test(
                        v
                      )
                    ) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  },
                  trigger: "blur"
                }
              ]
            }
          ]
        },
        {
          title: "寄宿信息",
          hidden: this.$attrs.status == 1 ? "none" : false,
          span: "x",
          fields: [
            {
              key: "addition$borrow_type",
              name: "类型",
              type: "select",
              value: "",
              code: 18,
              isId: true,
              options: []
            },
            {
              key: "addition$people",
              name: "住宿联系人",
              type: "text",
              value: ""
            },
            {
              key: "addition$mobile",
              name: "联系电话",
              type: "number",
              value: "",
              validator: [
                {
                  required: false,
                  message: "请输入正确的手机号！",
                  validator: (r, v, c) => {
                    if (v && !/^1[3-9]\d{9}$/.test(v)) {
                      c && c(new Error(r.message));
                      return false;
                    } else {
                      c && c();
                    }
                  },
                  trigger: "blur"
                }
              ]
            },
            {
              key: "addition$address",
              name: "住址",
              type: "text",
              value: ""
            }
          ]
        }
      ]
    };
  },
  watch: {
    "ruleForm.major_id": function(val) {
      this.resetGrade(val);
    }
  },
  created() {
    window.testInstance = this;
    this.schoolid = this.$attrs.schoolid;
    this.student_id = this.$attrs.student_id;
    this.status = this.$attrs.status || 2;
    this.form.forEach((group, i) => {
      group.fields.forEach((field, j) => {
        ((f, gindex, findex) => {
          if (f.code !== null && f.code !== undefined && !f.hidden) {
            axios
              .post("/api/pc/get-search-config", {
                type: f.code
              })
              .then(res => {
                if (Util.isAjaxResOk(res)) {
                  let options = res.data.data.map((item, index) => {
                    return {
                      label: item.name,
                      value: f.isId ? item.id : item.name
                    };
                  });
                  this.form[gindex].fields[findex].options = options;
                }
              });
          }
        })(field, i, j);
      });
    });

    axios
      .post("/api/school/load-majors", {
        id: this.schoolid
      })
      .then(res => {
        if (Util.isAjaxResOk(res)) {
          let options = res.data.data.majors.map((item, index) => {
            return {
              label: item.name,
              value: item.id
            };
          });
          this.form.forEach(group => {
            group.fields.forEach(field => {
              if (field.key === "major_id") {
                field.options = options;
              }
              if (field.key === "grade_id") {
                field.options = [];
              }
            });
          });
        }
      });
  }
};
</script>
<style lang="scss" scoped>
.teacher-edit-form .el-select {
  width: 100%;
}
.teacher-edit-form .el-input {
  width: 100%;
}
.teacher-edit-form .el-cascader {
  width: 100%;
}
.teacher-edit-form .el-col.col--5 {
  width: 20%;
  float: left;
}
.teacher-edit-form .el-col.arealevel1 {
  width: 20%;
  float: left;
  ::v-deep.el-select {
    width: 100%;
  }
}
.teacher-edit-form .el-col.arealevel2 {
  width: 20%;
  float: left;
  ::v-deep.el-select {
    width: calc(50% - 4px);
  }
}
.teacher-edit-form .el-col.arealevel {
  width: 40%;
  float: left;
}
.teacher-edit-form .el-col.arealevel5 {
  width: 60%;
  float: left;
  ::v-deep.el-select {
    width: calc(20% - 4px);
  }
}
.teacher-edit-form .el-divider--horizontal {
  margin-top: 12px;
}
.teacher-edit-form .form-divider {
  font-size: 18px;
  color: #313b4c;
  margin-top: 12px;
}
.teacher-edit-form {
  ::v-deep.el-form-item__label {
    padding: 0;
    margin: 0;
  }
}
</style>