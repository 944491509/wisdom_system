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
          :class="group.span ==='x'?'col--5':''"
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

import { Util } from "../../common/utils";
import { Constants } from "../../common/constants";
export default {
  name: "teacher-form",
  components: {
    NumberInput,
    AreaSelector
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
      notes: '',
      form: [
        {
          title: "",
          span: 6,
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
                  message: "请选择教师性别",
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
                  message: "请选择教师民族",
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
                  message: "请选择教师出生日期",
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
              key: "serial_number",
              name: "教师编号",
              type: "text",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "blur",
                  message: "请输入教师编号",
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
              name: "手机号",
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
              key: "resident",
              name: "户籍所在地",
              type: "areas",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择教师户籍所在地",
                  validator: (r, v, c) => {
                    setTimeout(() => {
                      if (!this.ruleForm.resident) {
                        c(new Error(r.message));
                      } else {
                        c();
                      }
                    }, 100);
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
              key: "party_time",
              name: "入党时间",
              type: "date",
              value: ""
            },
            {
              key: "home_address",
              name: "家庭住址",
              type: "text",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "blur",
                  message: "请输入家庭住址",
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
          title: "教育履历",
          span: "x",
          fields: [
            {
              key: "education",
              name: "第一学历",
              type: "select",
              value: "",
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请输入教师学历",
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
              key: "major",
              name: "第一学历专业",
              type: "text",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "blur",
                  message: "请输入所学专业",
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
              key: "degree",
              name: "学位",
              type: "select",
              value: "",
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请输入学位",
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
              key: "graduation_school",
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
              key: "graduation_time",
              name: "毕业时间",
              type: "date",
              value: "",
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择毕业时间",
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
              key: "final_education",
              name: "最高学历",
              type: "select",
              value: "",
              options: []
            },
            {
              key: "final_major",
              name: "最高学历专业",
              type: "text",
              value: ""
            },
            {
              key: "final_degree",
              name: "学位",
              type: "select",
              value: "",
              options: []
            },
            {
              key: "final_graduation_school",
              name: "毕业学校",
              type: "text",
              value: ""
            },
            {
              key: "final_graduation_time",
              name: "毕业时间",
              type: "date",
              value: ""
            },
            {
              key: "title",
              name: "目前职称",
              type: "select",
              value: "",
              options: [],
              validator: [
                {
                  required: true,
                  trigger: "change",
                  message: "请选择目前职称",
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
              key: "title_start_at",
              name: "职称获得时间",
              type: "date",
              value: ""
            }
          ]
        },
        {
          title: "工作履历",
          span: 8,
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
        code: 2,
        key: ["education", "final_education"]
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
            // if (opt.code === 5) {
            //   res.data = {
            //     code: 1000,
            //     message: "OK",
            //     data: [
            //       {
            //         id: 84,
            //         name: "在职",
            //         type: 5
            //       },
            //       {
            //         id: 85,
            //         name: "离职",
            //         type: 5
            //       },
            //       {
            //         id: 86,
            //         name: "退休",
            //         type: 5
            //       },
            //       {
            //         id: 87,
            //         name: "调离",
            //         type: 5
            //       }
            //     ]
            //   };
            // }
            // if (opt.code === 2) {
            //   res.data = {
            //     code: 1000,
            //     message: "OK",
            //     data: [
            //       {
            //         id: 69,
            //         name: "小学",
            //         type: 2
            //       },
            //       {
            //         id: 70,
            //         name: "初中",
            //         type: 2
            //       },
            //       {
            //         id: 71,
            //         name: "高中",
            //         type: 2
            //       },
            //       {
            //         id: 72,
            //         name: "中专",
            //         type: 2
            //       },
            //       {
            //         id: 73,
            //         name: "大专",
            //         type: 2
            //       },
            //       {
            //         id: 74,
            //         name: "硕士研究生",
            //         type: 2
            //       },
            //       {
            //         id: 75,
            //         name: "博士研究生",
            //         type: 2
            //       }
            //     ]
            //   };
            // }
            // if (opt.code === 4) {
            //   res.data = {
            //     code: 1000,
            //     message: "OK",
            //     data: [
            //       {
            //         id: 79,
            //         name: "高级助讲",
            //         type: 4
            //       },
            //       {
            //         id: 80,
            //         name: "助教",
            //         type: 4
            //       },
            //       {
            //         id: 81,
            //         name: "讲师",
            //         type: 4
            //       },
            //       {
            //         id: 82,
            //         name: "副教授",
            //         type: 4
            //       },
            //       {
            //         id: 83,
            //         name: "教授",
            //         type: 4
            //       }
            //     ]
            //   };
            // }
            // if (opt.code === 6) {
            //   res.data = {
            //     code: 1000,
            //     message: "OK",
            //     data: [
            //       {
            //         id: 88,
            //         name: "在编",
            //         type: 6
            //       },
            //       {
            //         id: 89,
            //         name: "外聘兼职",
            //         type: 6
            //       },
            //       {
            //         id: 90,
            //         name: "借调",
            //         type: 6
            //       },
            //       {
            //         id: 91,
            //         name: "实习",
            //         type: 6
            //       },
            //       {
            //         id: 92,
            //         name: "其他",
            //         type: 6
            //       }
            //     ]
            //   };
            // }
            // if (opt.code === 0) {
            //   res.data = {
            //     code: 1000,
            //     message: "OK",
            //     data: [
            //       {
            //         id: 1,
            //         name: "汉族",
            //         type: 0
            //       },
            //       {
            //         id: 2,
            //         name: "蒙古族",
            //         type: 0
            //       },
            //       {
            //         id: 3,
            //         name: "回族",
            //         type: 0
            //       },
            //       {
            //         id: 4,
            //         name: "藏族",
            //         type: 0
            //       },
            //       {
            //         id: 5,
            //         name: "维吾尔族",
            //         type: 0
            //       },
            //       {
            //         id: 6,
            //         name: "苗族",
            //         type: 0
            //       },
            //       {
            //         id: 7,
            //         name: "彝族",
            //         type: 0
            //       },
            //       {
            //         id: 8,
            //         name: "壮族",
            //         type: 0
            //       },
            //       {
            //         id: 9,
            //         name: "布依族",
            //         type: 0
            //       },
            //       {
            //         id: 10,
            //         name: "朝鲜族",
            //         type: 0
            //       },
            //       {
            //         id: 11,
            //         name: "满族",
            //         type: 0
            //       },
            //       {
            //         id: 12,
            //         name: "侗族",
            //         type: 0
            //       },
            //       {
            //         id: 13,
            //         name: "瑶族",
            //         type: 0
            //       },
            //       {
            //         id: 14,
            //         name: "土家族",
            //         type: 0
            //       },
            //       {
            //         id: 15,
            //         name: "哈尼族",
            //         type: 0
            //       },
            //       {
            //         id: 16,
            //         name: "哈萨克族",
            //         type: 0
            //       },
            //       {
            //         id: 17,
            //         name: "傣族",
            //         type: 0
            //       },
            //       {
            //         id: 18,
            //         name: "黎族",
            //         type: 0
            //       },
            //       {
            //         id: 19,
            //         name: "僳僳族",
            //         type: 0
            //       },
            //       {
            //         id: 20,
            //         name: "佤族",
            //         type: 0
            //       },
            //       {
            //         id: 21,
            //         name: "畲族",
            //         type: 0
            //       },
            //       {
            //         id: 22,
            //         name: "高山族",
            //         type: 0
            //       },
            //       {
            //         id: 23,
            //         name: "拉祜族",
            //         type: 0
            //       },
            //       {
            //         id: 24,
            //         name: "水族",
            //         type: 0
            //       },
            //       {
            //         id: 25,
            //         name: "东乡族",
            //         type: 0
            //       },
            //       {
            //         id: 26,
            //         name: "纳西族",
            //         type: 0
            //       },
            //       {
            //         id: 27,
            //         name: "景颇族",
            //         type: 0
            //       },
            //       {
            //         id: 28,
            //         name: "柯尔克孜族",
            //         type: 0
            //       },
            //       {
            //         id: 29,
            //         name: "土族",
            //         type: 0
            //       },
            //       {
            //         id: 30,
            //         name: "达斡尔族",
            //         type: 0
            //       },
            //       {
            //         id: 31,
            //         name: "仫佬族",
            //         type: 0
            //       },
            //       {
            //         id: 32,
            //         name: "羌族",
            //         type: 0
            //       },
            //       {
            //         id: 33,
            //         name: "布朗族",
            //         type: 0
            //       },
            //       {
            //         id: 34,
            //         name: "撒拉族",
            //         type: 0
            //       },
            //       {
            //         id: 35,
            //         name: "毛南族",
            //         type: 0
            //       },
            //       {
            //         id: 36,
            //         name: "仡佬族",
            //         type: 0
            //       },
            //       {
            //         id: 37,
            //         name: "锡伯族",
            //         type: 0
            //       },
            //       {
            //         id: 38,
            //         name: "阿昌族",
            //         type: 0
            //       },
            //       {
            //         id: 39,
            //         name: "普米族",
            //         type: 0
            //       },
            //       {
            //         id: 40,
            //         name: "塔吉克族",
            //         type: 0
            //       },
            //       {
            //         id: 41,
            //         name: "怒族",
            //         type: 0
            //       },
            //       {
            //         id: 42,
            //         name: "乌孜别克族",
            //         type: 0
            //       },
            //       {
            //         id: 43,
            //         name: "俄罗斯族",
            //         type: 0
            //       },
            //       {
            //         id: 44,
            //         name: "鄂温克族",
            //         type: 0
            //       },
            //       {
            //         id: 45,
            //         name: "德昂族",
            //         type: 0
            //       },
            //       {
            //         id: 46,
            //         name: "保安族",
            //         type: 0
            //       },
            //       {
            //         id: 47,
            //         name: "裕固族",
            //         type: 0
            //       },
            //       {
            //         id: 48,
            //         name: "京族",
            //         type: 0
            //       },
            //       {
            //         id: 49,
            //         name: "塔塔尔族",
            //         type: 0
            //       },
            //       {
            //         id: 50,
            //         name: "独龙族",
            //         type: 0
            //       },
            //       {
            //         id: 51,
            //         name: "鄂伦春族",
            //         type: 0
            //       },
            //       {
            //         id: 52,
            //         name: "赫哲族",
            //         type: 0
            //       },
            //       {
            //         id: 53,
            //         name: "门巴族",
            //         type: 0
            //       },
            //       {
            //         id: 54,
            //         name: "珞巴族",
            //         type: 0
            //       },
            //       {
            //         id: 55,
            //         name: "基诺族",
            //         type: 0
            //       }
            //     ]
            //   };
            // }
            // if (opt.code === 1) {
            //   res.data = {
            //     code: 1000,
            //     message: "OK",
            //     data: [
            //       {
            //         id: 56,
            //         name: "中共党员",
            //         type: 1
            //       },
            //       {
            //         id: 57,
            //         name: "中共预备党员",
            //         type: 1
            //       },
            //       {
            //         id: 58,
            //         name: "共青团员",
            //         type: 1
            //       },
            //       {
            //         id: 59,
            //         name: "民革党员",
            //         type: 1
            //       },
            //       {
            //         id: 60,
            //         name: "民盟盟员",
            //         type: 1
            //       },
            //       {
            //         id: 61,
            //         name: "民建会员",
            //         type: 1
            //       },
            //       {
            //         id: 62,
            //         name: "民进会员",
            //         type: 1
            //       },
            //       {
            //         id: 63,
            //         name: "农工党党员",
            //         type: 1
            //       },
            //       {
            //         id: 64,
            //         name: "致公党党员",
            //         type: 1
            //       },
            //       {
            //         id: 65,
            //         name: "九三学社社员",
            //         type: 1
            //       },
            //       {
            //         id: 66,
            //         name: "台盟盟员",
            //         type: 1
            //       },
            //       {
            //         id: 67,
            //         name: "无党派人士",
            //         type: 1
            //       },
            //       {
            //         id: 68,
            //         name: "群众",
            //         type: 1
            //       }
            //     ]
            //   };
            // }
            // if (opt.code === 3) {
            //   res.data = {
            //     code: 1000,
            //     message: "OK",
            //     data: [
            //       {
            //         id: 76,
            //         name: "学士",
            //         type: 3
            //       },
            //       {
            //         id: 77,
            //         name: "硕士",
            //         type: 3
            //       },
            //       {
            //         id: 78,
            //         name: "博士",
            //         type: 3
            //       }
            //     ]
            //   };
            // }
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
<style>
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
.teacher-edit-form .el-divider--horizontal {
  margin-top: 12px;
}
.teacher-edit-form .form-divider {
  font-size: 18px;
  color: #313b4c;
  margin-top: 12px;
}
</style>