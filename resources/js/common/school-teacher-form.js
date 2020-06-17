// 添加、编辑教师
import TeacherForm from '../components/teacher/teacher-form'
import {
  Util
} from '../common/utils'

function getQueryString(name) {
  var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
  var r = window.location.search.substr(1).match(reg); //search,查询？后面的参数，并匹配正则
  if (r != null) return unescape(r[2]);
  return null;
}

const dom = document.getElementById("school-add-teacher-app")

if (dom) {
  const dom = document.getElementById("app-init-data-holder");
  const schoolid = dom.dataset.school;
  new Vue({
    el: "#school-add-teacher-app",
    components: {
      TeacherForm
    },
    data() {
      return {
        schoolid,
        teacherName: '',
        teacher_id: ''
      }
    },
    created() {
      if (window.location.pathname.endsWith('modify')) {
        this.teacher_id = getQueryString('uuid')
        axios
          .post("/school_manager/teachers/get-teacher-profile", {
            teacher_id: this.teacher_id
          })
          .then(res => {
            if (Util.isAjaxResOk(res)) {
              let data = {
                campus_id: res.data.data.campus_id,
                ...res.data.data.profile,
                ...res.data.data.teacher,
              };
              ['birthday',
                'party_time',
                'graduation_time',
                'final_graduation_time',
                'title_start_at',
                'work_start_at',
                'hired_at'
              ].forEach(k => {
                if (data[k].includes('.')) {
                  // 把有点的时间换成'-'
                  data[k] = data[k].replace(/\./g, "-")
                }
                if (data[k] && data[k].split('-').length < 3) {
                  // 如果-分隔少于三个 当作是年月 没有日
                  data[k] = data[k] + '-01'
                }
              })
              this.$refs.teacherform.setData(data)
              this.teacherName = res.data.data.teacher.name
            }
          });
      }
    }
  })
}
