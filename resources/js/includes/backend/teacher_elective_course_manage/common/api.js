import {
  Util
} from "../../../../common/utils";

const messate = Vue.prototype.$messate

const API_MAP = {
  list: '/Oa/electivecourse/lists',
  applyingList: '/Oa/electivecourse/applylists',
  applyinfo: '/Oa/electivecourse/applyinfo',
  info: '/Oa/electivecourse/info'
}

const toGet = function (obj) {
  let str = ''
  Object.keys(obj).forEach(key => {
    str = str + (key + '=' + obj[key] + '&')
  })
  return str
}

export const CourseApi = {
  excute: function (fn, params = {}, ext = {}) {
    return new Promise((resolve, reject) => {
      const url = Util.buildUrl(API_MAP[fn] + (ext.url ? ext.url : '') + (ext.methods === 'get' ? ('?' + toGet(params)) : ''));
      if (Util.isDevEnv()) {
        return axios.get(url, affix).then(res => {
          if (Util.isAjaxResOk(res)) {
            resolve(res)
          } else {
            messate.error(res.data.message);
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
          messate.error(res.data.message);
          reject(res)
        }
      });
    })
  }
}
