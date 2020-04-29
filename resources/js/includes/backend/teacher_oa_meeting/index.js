import './style/common.scss'
import './style/index.scss'
// import './style/iconfont.css'
import {
  MeetingMode
} from './common/enum'
import MeetingList from './components/meeting'
import MeetingForm from './components/meeting-form'

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
      showCreateModal(){
        this.addDrawer = true
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
      }
    },
    created() {
      this.activeName = 'unfinished'
      // MeetingApi.excute("getTeacherInfo").then(res => {
      //   InnerStorage.set('userId', res.data.data.user_id)
      // });
    }
  })
}
