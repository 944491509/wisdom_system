import {Constants} from "../../common/constants";
import {Util} from "../../common/utils";

/**
 * 获取用户二维码
 */
if (document.getElementById('get-user-qr-code')){
    new Vue({
      el: '#get-user-qr-code',
      data: {
        code:''
       },
       methods: {
          getUserCode(user_id) {
             const url = Util.buildUrl(Constants.API.GET_USER_QR_CODE_BY_ID);
              axios.post(url, {
                user_id: user_id
              }).then((res) => {
                  if (Util.isAjaxResOk(res)) {
                     this.code = res.data.data.code
                    console.log(this.code)
                  }
              }).catch((err) => {
                  console.log(err)
              });
          }
       },

    });
}
