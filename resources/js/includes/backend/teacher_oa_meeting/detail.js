import './style/common.scss'
import MeetingDetail from './components/meeting-detail'

const APPID = 'teacher-oa-meeting-detail-app'
let app = document.getElementById(APPID)
if (app) {
  var inited = {}
  new Vue({
    el: '#' + APPID,
    components: {
      MeetingDetail
    }
  })
}