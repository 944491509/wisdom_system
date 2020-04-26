import {
  Util
} from "../../../../common/utils";

const meeting = Vue.prototype.$meeting

const API_MAP = {
  list: '/api/meeting/'
}

const toGet = function (obj) {
  let str = ''
  Object.keys(obj).forEach(key => {
    str = str + (key + '=' + obj[key] + '&')
  })
  return str
}

export const MeetingApi = {
  excute: function (fn, params = {}, ext = {}) {
    return new Promise((resolve, reject) => {
      const url = Util.buildUrl(API_MAP[fn] + (ext.url ? ext.url : '') + (ext.methods === 'get' ? ('?' + toGet(params)) : ''));
      if (Util.isDevEnv()) {
        return axios.get(url, affix).then(res => {
          if (Util.isAjaxResOk(res)) {
            resolve(res)
          } else {
            meeting.error(res.data.meeting);
            reject(res)
          }
        });
      }
      return axios[ext.methods || 'post'](
        url,
        params
      ).then(res => {
        if (Util.isAjaxResOk(res)) {
          resolve(res)
        } else {
          meeting.error(res.data.meeting);
          reject(res)
        }
      });
    })
  }
}
