import {
  Util
} from "../../../../common/utils";

const message = Vue.prototype.$message

const API_MAP = {
  list: '/api/meeting/',
  getMeetRoomList: '/api/meeting/getMeetRoomList',
  getTeacherInfo: '/api/user/getTeacherInfo',
  addMeeting: '/api/meeting/addMeeting',
  meetDetails: '/api/meeting/meetDetails',
  getMeetSummary: '/api/meeting/getMeetSummary',
  myMeetSummary: '/api/meeting/myMeetSummary',
  saveMeetSummary: '/api/meeting/saveMeetSummary',
  signInRecord: '/api/meeting/signInRecord',
  mySignInRecord: '/api/meeting/mySignInRecord',
  signInQrCode: '/api/meeting/signInQrCode',
  signOutQrCode: '/api/meeting/signOutQrCode'
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
            message.error(res.data.message);
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
          message.error(res.data.message);
          reject(res)
        }
      });
    })
  }
}


export const addMeeting = function (data) {
  return axios({
    method: 'post',
    url: API_MAP.addMeeting,
    headers: {
      'Content-Type': 'multipart/form-data;charset=UTF-8'
    },
    data
  })
}



export const submitSummary = function (data) {
  return axios({
    method: 'post',
    url: API_MAP.saveMeetSummary,
    headers: {
      'Content-Type': 'multipart/form-data;charset=UTF-8'
    },
    data
  })
}
