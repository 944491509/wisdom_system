import './style/common.scss'
import MessageDetail from './components/message-detail'

const APPID = 'teacher-oa-message-detail-app'
let app = document.getElementById(APPID)
if (app) {
  var inited = {}
  new Vue({
    el: '#' + APPID,
    components: {
      MessageDetail
    }
  })
}