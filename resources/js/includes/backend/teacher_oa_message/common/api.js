import {
  Util
} from "../../../../common/utils";

const message = Vue.prototype.$message

const API_MAP = {
  getOaMessageListInfo: '/api/Oa/message-list',
  getOrganization: '/Oa/tissue/getOrganization',
  updateTag: '/api/Oa/message-update-or-del',
  getMessageDetail: '/api/Oa/message-info',
  getOaProjectListInfo: '/Oa/project/getOaProjectListInfo',
  getOaProjectUserListInfo: '/Oa/project/getOaProjectUserListInfo',
  addOaMessageInfo: '/api/Oa/add-message',
  updateOaMessageInfo: '/api/Oa/message-update',
  getOaMessageInfo: '/Oa/message/getOaMessageInfo',
  addOaMessageForum: '/Oa/message/addOaMessageForum',
  delOaMessageForum: '/Oa/message/delOaMessageForum',
  finishOaMessageInfo: '/Oa/message/finishOaMessageInfo',
  addOaMessageUser: '/Oa/message/addOaMessageUser',
  receiveOaMessageInfo: '/Oa/message/receiveOaMessageInfo',
  getOaMessageReport: '/Oa/message/getOaMessageReport',
  getTeacherInfo: '/api/user/getTeacherInfo'
}

export const MessageApi = {
  excute: function (fn, params = {}) {
    return new Promise((resolve, reject) => {
      const url = Util.buildUrl(API_MAP[fn]);
      if (Util.isDevEnv()) {
        return axios.get(url, affix).then(res => {
          if (Util.isAjaxResOk(res)) {
            resolve(res)
          } else {
            message.error(res.data.message);
            reject(res)
          }
        });
      }
      return axios.post(
        url,
        params
      ).then(res => {
        if (Util.isAjaxResOk(res)) {
          resolve(res)
        } else {
          message.error(res.data.message);
          reject(res)
        }
      });
    })
  }
}

export const finishMessage = function (data) {
  return axios({
    method: 'post',
    url: API_MAP.finishOaMessageInfo,
    headers: {
      'Content-Type': 'multipart/form-data;charset=UTF-8'
    },
    data
  })
}
