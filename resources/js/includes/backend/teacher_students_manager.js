import {
  Util
} from "../../common/utils";
import {
  Constants
} from "../../common/constants";
import qs from 'qs'

if (document.getElementById('teacher-assistant-students-manager-app')) {
  new Vue({
    el: '#teacher-assistant-students-manager-app',
    data() {
      return {
        schoolId: null,
        classData: [],
        stuData: [],
        stuDetail: {},
        defaultProps: {
          children: 'children',
          label: 'label'
        },
        detailData: {},
        detailDataList: [{
          label: '姓名',
          detail: '',
          key: 'name'
        }, {
          label: '身份证号',
          detail: '',
          key: 'id_number'
        }, {
          label: '性别',
          detail: '男',
          key: 'gender'
        }, {
          label: '出生日期',
          detail: '',
          key: 'birthday'
        }, {
          label: '民族',
          detail: '',
          key: 'nation_name'
        }, {
          label: '政治面貌',
          detail: '',
          key: 'political_name'
        }, {
          label: '生源地',
          detail: '',
          key: 'source_place'
        }, {
          label: '籍贯',
          detail: '',
          key: 'country'
        }, {
          label: '联系电话',
          detail: '',
          key: 'contact_number'
        }, {
          label: 'QQ号',
          detail: '',
          key: 'qq'
        }, {
          label: '微信号',
          detail: '',
          key: 'wx'
        }, {
          label: '家长姓名',
          detail: '',
          key: 'parent_name'
        }, {
          label: '家长电话',
          detail: '',
          key: 'parent_mobile'
        }, {
          label: '所在城市',
          detail: '',
          key: 'city'
        }, {
          label: '详细地址',
          detail: ' ',
          key: 'address_line'
        }, {
          label: '邮箱',
          detail: '',
          key: 'email'
        }, {
          label: '学制',
          detail: '',
          key: 'school_year'
        }, {
          label: '学历',
          detail: '',
          key: 'education'
        }, {
          label: '学院',
          detail: '',
          key: 'institute'
        }, {
          label: '年级',
          detail: '',
          key: 'year'
        }, {
          label: '专业',
          detail: '',
          key: 'major'
        }, {
          label: '职务',
          detail: '',
          key: 'stu_job'
        }, {
          label: '学生照片'
        }],
        detailForm: {
          contact_number: '',
          qq: '',
          wx: '',
          parent_mobile: '',
          city: '',
          address_line: '',
          email: '',
          position: ''
        },
        ifShowStu: false,
        ifShowDetail: false,
        dialogVisible: false,
        student_id: '',
        detailPage: {},
        studentsParams: {}
      }
    },
    created() {
      const dom = document.getElementById('app-init-data-holder');
      this.schoolId = dom.dataset.school;
      this.getClassData();
      // console.log('班级评分');
    },
    methods: {
      showStu: function (data) {
        this.ifShowStu = true;
        this.ifShowDetail = false;
        this.studentsParams = data;
        this.getStuData(data);
        // this.stuName = stuData.stuName;
      },
      showDetail: function (data) {
        this.ifShowDetail = true;
        let params = {
          student_id: data.student_id
        };
        this.student_id = data.student_id;
        this.getStuDetail(params)
      },
      editStu: function () {

      },
      onSubmit: function () {
        // console.log(this.detailForm);
        // console.log('提交');
        let params = {};
        params.data = {};
        params.monitor = {};
        params.group = {};
        params.monitor['monitor_name'] = '';
        params.monitor['monitor_id'] = 0;
        params.monitor['grade_id'] = this.detailData.grade_id;
        params.group['group_name'] = '';
        params.group['group_id'] = 0;
        params.group['grade_id'] = this.detailData.grade_id;
        for (let item in this.detailForm) {
          if (item != 'position') {
            // 循环赋值数组内容
            params.data[item] = this.detailForm[item]
          } else {
            // 班长/团支书/无
            let type = this.detailForm[item];
            if (type && type !== 'false') {
              // type为'monitor'或'group' 出现哪个把哪个赋值;
              params[type][type + '_id'] = this.detailData.student_id;
              params[type][type + '_name'] = this.detailData.name;
            }
          }
        }
        params.student_id = this.student_id
        this.updateStudents(params);
      },
      getClassData: function () {
        const url = Util.buildUrl(Constants.API.TEACHER_WEB.STUDENTS_GRADE_LIST);
        axios.post(url).then((res) => {
          if (Util.isAjaxResOk(res)) {
            let data = res.data.data;
            this.classData = data;
          }
        }).catch((err) => {

        });
      },
      getStuData: function (params) {
        const url = Util.buildUrl(Constants.API.TEACHER_WEB.STUDENTS_LIST);
        axios.post(url, params).then((res) => {
          if (Util.isAjaxResOk(res)) {
            this.stuData = res.data.data;
            this.$set(this, 'detailPage', res.data)
          }
        }).catch((err) => {

        });
      },
      getStuDetail: function (params) {
        const url = Util.buildUrl(Constants.API.TEACHER_WEB.STUDENTS_INFO);
        axios.post(url, params).then((res) => {
          if (Util.isAjaxResOk(res)) {
            let data = res.data.data;
            this.setStuDetail(data)
          }
        }).catch((err) => {

        });
      },
      setStuDetail: function (data) {
        this.detailData = data
        this.detailData.genderText = this.detailData.gender === 1 ? '男' : '女'
        this.detailDataList.forEach((item, index) => {
          if (item.key === 'stu_job') {
            if (data.group) {
              item.detail = '团支书'
              this.detailForm.position = 'group'
            } else if (data.monitor) {
              item.detail = '班长'
              this.detailForm.position = 'monitor'
            } else {
              item.detail = '无'
              this.detailForm.position = 'false'
            }
          } else if (item.key === 'gender') {
            if (data.gender === 1) {
              item.detail = '男'
            } else {
              item.detail = '女'
            }
          } else {
            item.detail = data[item.key];
          }
        })
        try {
          let src = data.face_image
          let img = document.getElementById('student-photo-img')
          if (!img) {
            let tbodys = this.$refs.stuinfoTable.$el.getElementsByTagName('tbody')
            const tbody = tbodys[tbodys.length - 1]
            const tr = document.createElement('tr')
            tr.setAttribute('class', 'el-table__row')
            tr.setAttribute('id', 'student-img-box')
            tr.innerHTML = `<div style="width: 200%;padding: 10px"><img id="student-photo-img" style="max-width: 100%;" src="${src}"/></div>`
            tbody.appendChild(tr)
          } else {
            img.setAttribute('src', src)
          }
        } catch (e) {
          console.error(e)
        }
      },
      updateStudents: function (params) {
        const url = '/api/Oa/update-student-info';
        axios.post(url, params).then((res) => {
          if (Util.isAjaxResOk(res)) {
            this.$message({
              message: '保存成功',
              type: 'success'
            });
            let params = {
              student_id: this.student_id
            };
            this.dialogVisible = false;
            this.getStuDetail(params)
          }
        }).catch((err) => {
          this.$message({
            message: '保存失败',
            type: 'success'
          });
        });
      },
      detailChange: function (current) {
        this.studentsParams.page = current;
        this.getStuData(this.studentsParams)
      },
      onFileSelected(e) {
        const file = e.target.files[0];
        this.$refs.studentImgUpload.value = null;
        let form = new FormData();
        form.append("user_id", this.uploadStorage.student_id);
        form.append("face_image", file);
        form.append("type", this.uploadStorage.status ? 1 : 0);
        return axios({
          method: 'post',
          url: '/api/cloud/uploadFaceImage',
          headers: {
            'Content-Type': 'multipart/form-data;charset=UTF-8'
          },
          data: form
        }).then(res => {
          if (Util.isAjaxResOk(res)) {
            this.stuData[this.uploadStorage.index].face_code_status = 1
          }
        })
      },
      setCurrentStudent(stu, index) {
        this.uploadStorage = {
          student_id: stu.student_id,
          status: stu.face_code_status,
          index,
        }
      }
    }
  });
}
