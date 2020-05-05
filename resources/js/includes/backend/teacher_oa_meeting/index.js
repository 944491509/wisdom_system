import './style/common.scss'
import './style/index.scss'
// import './style/iconfont.css'
import {
  MeetingMode
} from './common/enum'
import MeetingList from './components/meeting'
import MeetingForm from './components/meeting-form'
import {
  MeetingApi
} from './common/api'
import {
  getQueryString
} from "../teacher_oa_tasks/common/utils";

const APPID = 'teacher-oa-meeting-app'
let app = document.getElementById(APPID)
if (app) {
  new Vue({
    el: '#' + APPID,
    components: {
      MeetingList,
      MeetingForm
    },
    watch: {
      'activeName': function (val) {
        this.$refs[val][0].getMeetingList()
      }
    },
    computed: {
      activeNameText() {
        return (MeetingMode[this.activeName] || {}).text || ''
      }
    },
    methods: {
      onMeetingCreated() {
        this.refreshList(this.activeName)
        this.$refs.addMeetingDrawer.closeDrawer()
        this.addDrawer = false
      },
      refreshList(val) {
        if (this.$refs[val]) {
          this.$refs[val][0].getMeetingList()
        }
      },
      showCreateModal() {
        this.addDrawer = true
      },
      checkClose(close) {
        if (!this.$refs.addMeetingDrawer.$children[0].selectMb) {
          close()
        } else {
          this.$refs.addMeetingDrawer.$children[0].selectMb = false
        }
      },
    },
    data() {
      return {
        meetingTypes: ((types) => {
          return Object.keys(types).map(typeKey => {
            return types[typeKey]
          })
        })(MeetingMode),
        activeName: '',
        formTitle: '',
        formTitleIcon: '',
        addDrawer: false,
        currentUserId: ''
      }
    },
    created() {
      let mode = window.localStorage.getItem('meetingMode')
      if (mode) {
        this.activeName = mode
        window.localStorage.setItem('meetingMode', '')
      } else {
        this.activeName = 'unfinished'
      }
      MeetingApi.excute("getTeacherInfo").then(res => {
        this.currentUserId = res.data.data.user_id;
      });
    }
  })
}
