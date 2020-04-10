import './style/common.scss'
import './style/index.scss'
import './style/iconfont.css'
import {
  MessageMode
} from './common/enum'
import MessageList from './components/message'
import MessageForm from './components/message-form'
import {
  MessageApi
} from './common/api'
import {InnerStorage} from './common/utils'

const APPID = 'teacher-oa-message-app'
let app = document.getElementById(APPID)
if (app) {
  var inited = {}
  new Vue({
    el: '#' + APPID,
    components: {
      MessageList,
      MessageForm
    },
    watch: {
      'activeName': function (val) {
        this.$refs[val][0].getMessageList()
      }
    },
    computed: {
      activeNameText() {
        return (MessageMode[this.activeName] || {}).text || ''
      }
    },
    methods: {
      onMessageCreated() {
        this.refreshList(this.activeName)
        this.$refs.addMessageDrawer.closeDrawer()
        this.addDrawer = false
      },
      refreshList(val) {
        if (this.$refs[val]) {
          this.$refs[val][0].getMessageList()
        }
      },
      checkClose(close) {
        if (!this.$refs.addMessageDrawer.$children[0].selectMb) {
          close()
        } else {
          this.$refs.addMessageDrawer.$children[0].selectMb = false
        }
      },
      editMessage(message){
        
      },
      formbroad(ctrl, message, shareMessage){
        this.addDrawer = true
        if(!ctrl){
          this.formTitle = '写信'
          this.formTitleIcon = 'write'
          return
        }
        if(ctrl.indexOf('send') > -1) {
          this.formTitle = '写信'
          this.formTitleIcon = 'write'
          if(shareMessage && shareMessage.id) {
            this.formTitle = '转发'
            this.formTitleIcon = 'share'
          }
        }
        if(ctrl.indexOf('reply') > -1 ){
          this.formTitle = '回复'
          this.formTitleIcon = 'reply'
        }
        this.$nextTick(()=>{
          return this.$refs.addMessageDrawer.$children[0].setData(ctrl,message,shareMessage)
        })
      }
    },
    data() {
      return {
        messageTypes: ((types) => {
          return Object.keys(types).map(typeKey => {
            return types[typeKey]
          })
        })(MessageMode),
        activeName: '',
        formTitle: '',
        formTitleIcon: '',
        addDrawer: false,
      }
    },
    created() {
      this.activeName = 'unread'
      MessageApi.excute("getTeacherInfo").then(res => {
        InnerStorage.set('userId', res.data.data.user_id)
      });
    }
  })
}
