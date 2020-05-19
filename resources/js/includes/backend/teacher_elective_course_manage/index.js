import './style/common.scss'
import './style/index.scss'
import {
  CourseMode
} from './common/enum'
import CourseList from './components/course'
import CourseDetail from './components/detail'
import {
  CourseApi
} from './common/api'

const APPID = 'teacher-elective-course-manage'
let app = document.getElementById(APPID)
if (app) {
  var inited = {}
  new Vue({
    el: '#' + APPID,
    components: {
      CourseList,
      CourseDetail
    },
    watch: {
      'activeName': function (val) {
        if (!inited[val] && this.$refs[val]) {
          this.$refs[val][0].getCourseList()
          inited[val] = true
        }
      }
    },
    // computed: {
    //   activeNameText() {
    //     return (CourseMode[this.activeName] || {}).text || ''
    //   }
    // },
    methods: {
      refreshList(val) {
        if (this.$refs[val]) {
          this.$refs[val][0].getCourseList()
        }
      },
      checkDetail(course, mode){
        this.courseMode = mode
        this.courseId = course.applyid
        this.coursedetailshow = true
      },
      checkClose(close) {
        if (!this.$refs.courseDetailDrawer.$children[0].selectMb) {
          close()
        } else {
          this.$refs.courseDetailDrawer.$children[0].selectMb = false
        }
      }
    },
    data() {
      return {
        courseTypes: ((types) => {
          return Object.keys(types).map(typeKey => {
            return types[typeKey]
          })
        })(CourseMode),
        activeName: '',
        currentUserId: {},
        coursedetailshow: false,
        courseId: '',
        courseMode: ''
      }
    },
    created() {
      this.activeName = CourseMode.waiting.status
      // CourseApi.excute("getTeacherInfo").then(res => {
      //   this.currentUserId = res.data.data.user_id;
      // });
    }
  })
}
