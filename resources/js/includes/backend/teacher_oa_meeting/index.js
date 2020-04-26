import './style/common.scss'
import './style/index.scss'
// import './style/iconfont.css'
import {
  MeetingMode
} from './common/enum'
import MeetingList from './components/meeting'
// import MeetingForm from './components/meeting-form'
import {
  MeetingApi
} from './common/api'
import {InnerStorage} from './common/utils'

const APPID = 'teacher-oa-meeting-app'
let app = document.getElementById(APPID)
if (app) {
  var inited = {}
  new Vue({
    el: '#' + APPID,
    components: {
      MeetingList,
      // MeetingForm
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
      checkClose(close) {
        if (!this.$refs.addMeetingDrawer.$children[0].selectMb) {
          close()
        } else {
          this.$refs.addMeetingDrawer.$children[0].selectMb = false
        }
      },
      editMeeting(meeting){
        
      },
      formbroad(ctrl, meeting, shareMeeting){
        this.addDrawer = true
        if(!ctrl){
          this.formTitle = '写信'
          this.formTitleIcon = 'write'
          return
        }
        if(ctrl.indexOf('send') > -1) {
          this.formTitle = '写信'
          this.formTitleIcon = 'write'
          if(shareMeeting && shareMeeting.id) {
            this.formTitle = '转发'
            this.formTitleIcon = 'share'
          }
        }
        if(ctrl.indexOf('reply') > -1 ){
          this.formTitle = '回复'
          this.formTitleIcon = 'reply'
        }
        this.$nextTick(()=>{
          return this.$refs.addMeetingDrawer.$children[0].setData(ctrl,meeting,shareMeeting)
        })
      }
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
