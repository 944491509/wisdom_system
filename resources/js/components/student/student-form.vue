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
      <el-row :gutter="20" v-for="(group, index) in form" :key="index">
        <div class="form-divider" v-if="group.title">
          <span>{{group.title}}</span>
          <el-divider></el-divider>
        </div>
        <el-col
          :span="isNaN(group.span)?null:group.span"
          v-for="(field, index_) in group.fields"
          :class="field.type==='arearemote'? ('arealevel'+field.level): (group.span ==='x'?'col--5':'')"
          :key="index_"
        >
          <el-form-item :label="field.name" :prop="field.key">
            <el-input v-if="field.type==='text'" v-model="field.value"></el-input>
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
            <area-selector-remote v-else-if="field.type==='arearemote'" :level="field.level"></area-selector-remote>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row :gutter="20">
        <el-col :span="24">
          <el-form-item label="备注" prop="notes">
            <el-input type="textarea" v-model="notes"></el-input>
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
          group.fields.forEach(field => {
            if (
              field.validator &&
              field.validator[0] &&
              field.validator[0].required &&
              !field.value
            ) {
              this.$message({
                message: field.validator[0].message,
                type: "error"
              });
              throw new Error("校验未通过");
            }
          });
        });
        this.pending = true;
        let params = {
          school_id: this.schoolid,
          campus_id: this.ruleForm.campus_id,
          teacher: {
            name: this.ruleForm.name,
            mobile: this.ruleForm.mobile,
            status: this.ruleForm.status
          },
          profile: {
            ...this.ruleForm,
            notes: this.notes
          }
        };
        delete params.profile["campus_id"];
        delete params.profile["name"];
        delete params.profile["mobile"];
        delete params.profile["status"];
        let url = "/school_manager/teachers/save-profile";
        if (this.teacher_id) {
          params.teacher_id = this.teacher_id;
          url = "/school_manager/teachers/update-teacher-profile";
        }
        axios.post(url, params).then(res => {
          this.pending = false;
          if (Util.isAjaxResOk(res)) {
            if (this.teacher_id) {
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
                  window.location.href = "/school_manager/school/teachers";
                }
              });
            }
          }
        });
      } catch (e) {}
    },
    setData(data) {
      this.form.forEach(group => {
        group.fields.forEach(field => {
          field.value = data[field.key];
        });
      });
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
                  c();
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
            obj[field.key] = field.value;
          });
        });
        return obj;
      })(this.form);
    }
  },
  data() {
    return {
      pending: false,
      notes: "",
      form: [
        {
          title: "",
          span: "x",
          fields: [
            {
              key: "name",
              name: "姓名",
              type: "text",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "blur",
                  message: "请填写姓名",
                  validator: (r, v, c) => {
                    if (!v) {
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "gender",
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
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "nation_name",
              name: "民族",
              type: "select",
              value: "",
              filterable: true,
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择学生民族",
                  validator: (r, v, c) => {
                    if (!v) {
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "political_name",
              name: "政治面貌",
              type: "select",
              value: "",
              options: [
                {
                  label: "党员",
                  value: "党员"
                },
                {
                  label: "团员",
                  value: "团员"
                },
                {
                  label: "群众",
                  value: "群众"
                }
              ],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择政治面貌",
                  validator: (r, v, c) => {
                    if (!v) {
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "birthday",
              name: "出生日期",
              type: "date",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择学生出生日期",
                  validator: (r, v, c) => {
                    if (!v) {
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "mobile",
              name: "本人电话号码",
              type: "number",
              value: "",
              validator: [
                {
                  required: true,
                  validator: (r, v, c) => {
                    if (!/^1[3-9]\d{9}$/.test(v)) {
                      c(new Error("请输入正确的手机号！"));
                    }
                  },
                  trigger: "blur"
                }
              ]
            },
            {
              key: "id_number",
              name: "身份证号",
              type: "number",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "blur",
                  validator: (r, v, c) => {
                    if (
                      !/^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/.test(
                        v
                      )
                    ) {
                      c(new Error("请输入正确的身份证号！"));
                    }
                  }
                }
              ]
            },
            {
              key: "student_code",
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
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "country",
              name: "籍贯",
              type: "text",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "blur",
                  message: "请输入学生籍贯地",
                  validator: (r, v, c) => {
                    if (!v) {
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "health_status",
              name: "健康状况",
              type: "select",
              value: "",
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择学生健康状况",
                  validator: (r, v, c) => {
                    if (!v) {
                      c(new Error(r.message));
                    } else {
                      c();
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
              key: "graduate_school",
              name: "毕业学校",
              type: "text",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "blur",
                  message: "请填写毕业学校",
                  validator: (r, v, c) => {
                    if (!v) {
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "graduate_type",
              name: "学生来源（招生对象）",
              type: "select",
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
                  message: "请选择学生来源",
                  validator: (r, v, c) => {
                    if (!v) {
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "cooperation_type",
              name: "联招合作类型",
              type: "select",
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
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "source_place",
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
                    if (!v) {
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "recruit_type",
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
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "volunteer",
              name: "报名志愿",
              type: "text",
              value: ""
            },
            {
              key: "license_number",
              name: "准考证号",
              type: "text",
              value: ""
            },
            {
              key: "examination_site",
              name: "考点",
              type: "text",
              value: ""
            },
            {
              key: "examination_score",
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
              key: "work_start_at",
              name: "参加工作时间",
              type: "date",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请设置参加工作时间",
                  validator: (r, v, c) => {
                    if (!v) {
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "hired_at",
              name: "本校聘任开始时间",
              type: "date",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请设置本校聘任开始时间",
                  validator: (r, v, c) => {
                    if (!v) {
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "campus_id",
              name: "办公校区",
              type: "select",
              value: "",
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择办公校区",
                  validator: (r, v, c) => {
                    if (!v) {
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "status",
              name: "聘任状态",
              type: "select",
              value: "",
              options: [
                {
                  label: "在职",
                  value: 3
                },
                {
                  label: "离职",
                  value: 4
                },
                {
                  label: "退休",
                  value: 5
                },
                {
                  label: "调离",
                  value: 6
                }
              ],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择聘任状态",
                  validator: (r, v, c) => {
                    if (!v) {
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "mode",
              name: "聘任方式",
              type: "select",
              value: "",
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择聘任方式",
                  validator: (r, v, c) => {
                    if (!v) {
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            },
            {
              key: "category_teach",
              name: "授课类别",
              type: "select",
              value: "",
              options: [
                {
                  value: 1,
                  label: "文化课"
                },
                {
                  value: 2,
                  label: "专业课"
                },
                {
                  value: 3,
                  label: "实训课"
                },
                {
                  value: 4,
                  label: "其他"
                }
              ],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择授课类别",
                  validator: (r, v, c) => {
                    if (!v) {
                      c(new Error(r.message));
                    } else {
                      c();
                    }
                  }
                }
              ]
            }
          ]
        }
      ]
    };
  },
  created() {
    this.schoolid = this.$attrs.schoolid;
    this.teacher_id = this.$attrs.teacher_id;
    return;
    [
      {
        code: 0,
        key: ["nation_name"]
      },
      {
        code: 1,
        key: ["political_name"]
      },
      {
        code: 7,
        key: ["health_status"]
      },
      {
        code: 3,
        key: ["degree", "final_degree"]
      },
      {
        code: 4,
        key: ["title"]
      },
      {
        code: 6,
        key: ["mode"]
      }
    ].forEach(opt => {
      (opt => {
        axios
          .post("/api/pc/get-search-config", {
            type: opt.code
          })
          .then(res => {
            if (Util.isAjaxResOk(res)) {
              let options = res.data.data.map((item, index) => {
                return {
                  label: item.name,
                  value: opt.isID ? item.id : item.name
                };
              });
              this.form.forEach(group => {
                group.fields.forEach(field => {
                  if (opt.key.includes(field.key)) {
                    field.options = options;
                  }
                });
              });
            }
          });
      })(opt);
    });
    axios
      .post(Constants.API.LOAD_BUILDINGS_BY_SCHOOL, {
        school: this.schoolid
      })
      .then(res => {
        if (Util.isAjaxResOk(res)) {
          let options = res.data.data.campuses.map(campuse => {
            return {
              label: campuse.campus,
              value: campuse.id
            };
          });
          this.form.forEach(group => {
            group.fields.forEach(field => {
              if (field.key === "campus_id") {
                field.options = options;
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
.teacher-edit-form .el-col.arealevel2 {
  width: 40%;
  float: left;
  ::v-deep.el-select {
    width: 23%;
  }
}
.teacher-edit-form .el-col.arealevel {
  width: 40%;
  float: left;
}
.teacher-edit-form .el-col.arealevel5 {
  width: 40%;
  float: left;
}
.teacher-edit-form .el-divider--horizontal {
  margin-top: 12px;
}
.teacher-edit-form .form-divider {
  font-size: 18px;
  color: #313b4c;
  margin-top: 12px;
}
</style>