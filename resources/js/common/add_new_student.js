// 添加、编辑教师
import StudentForm from '../components/student/student-form'
import {
  Util
} from '../common/utils'

function getQueryString(name) {
  var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
  var r = window.location.search.substr(1).match(reg); //search,查询？后面的参数，并匹配正则
  if (r != null) return unescape(r[2]);
  return null;
}

const dom = document.getElementById("school-add-student-app")

if (dom) {
  const dom = document.getElementById("app-init-data-holder");
  const schoolid = dom.dataset.school;
  new Vue({
    el: "#school-add-student-app",
    components: {
      StudentForm
    },
    data() {
      return {
        schoolid,
        studentName: '',
        student_id: '',
        title:'',
        status: 2 // 2 学生 1 未认证
      }
    },
    created() {

      if(getQueryString('status') == '1'){
        this.status = 1
        this.title = '创建新的用户档案'
      }else{
        this.title = '创建新的学生档案'
      }
      if (window.location.pathname.endsWith('edit')) {
        this.student_id = getQueryString('uuid')
        axios
          .post("/school_manager/student/info", {
            student_id: this.student_id
          })
          .then(res => {
            if (Util.isAjaxResOk(res)) {
              this.studentName = res.data.data[0].user.name
              this.$refs.studentform.setData(res.data.data[0])
            }
          });
      }
    }
  })
}
