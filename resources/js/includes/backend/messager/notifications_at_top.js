/**
 * 顶部的消息通知应用
 */
import {
  loadMessages
} from '../../../common/notifications';
import Notifications from '../../../components/notification/index'
import {
  Util
} from "../../../common/utils";

const appid = 'notification-app-data-top'
const dom = document.getElementById(appid);
const schoolId = dom.dataset.schoolid;
if (dom) {
  new Vue({
    el: '#' + appid,
    components: {
      Notifications
    },
    data() {
      return {
        schoolId: schoolId,
        count: 0,
        notifyDrawer: false
      };
    },
    created() {
      this.loadLastSixSystemMessage();
    },
    methods: {
      loadLastSixSystemMessage: function () {
        if (!Util.isEmpty(this.schoolId)) {
          loadMessages(this.schoolId).then(res => {
            if (Util.isAjaxResOk(res)) {
              // 检查是不是有新的消息
              if (res.data.data.unread !== this.count && res.data.data.unread > this.count) {
                if(window.localStorage.getItem('initLogin')){
                  this.$notify.info({
                    title: '消息',
                    message: `你有${res.data.data.unread - this.count}条新消息`,
                    duration: 0
                  });
                  window.localStorage.removeItem('initLogin')
                }
              }
              this.count = res.data.data.unread
            }
          })
        }
      },
      viewNotifications() {
        this.notifyDrawer = true
        setTimeout(() => {
          this.loadLastSixSystemMessage()
        }, 3000)
      }
    }
  })
}
