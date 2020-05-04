import {
  MeetingApi
} from './api'
export function getQueryString(name) {
  var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
  var r = window.location.search.substr(1).match(reg);
  if (r != null) return unescape(r[2]);
  return null;
}

export function getFileURL(file) {
  let getUrl = null;
  if (window.createObjectURL !== undefined) { // basic
    getUrl = window.createObjectURL(file);
  } else if (window.URL !== undefined) { // mozilla(firefox)
    getUrl = window.URL.createObjectURL(file);
  } else if (window.webkitURL !== undefined) { // webkit or chrome
    getUrl = window.webkitURL.createObjectURL(file);
  }
  return getUrl;
}

function debounce(func, wait, immediate) {
  let timer;
  return function () {
    let context = this;
    let args = arguments;

    if (timer) clearTimeout(timer);
    if (immediate) {
      var callNow = !timer;
      timer = setTimeout(() => {
        timer = null;
      }, wait)
      if (callNow) func.apply(context, args)
    } else {
      timer = setTimeout(function () {
        func.apply(context, args)
      }, wait);
    }
  }
}

const searchMember = function (keyword, call) {
  if (!keyword) {
    return
  }
  MeetingApi.excute("getOrganization", {
    keyword: keyword,
    type: 2
  }).then(res => {
    call(res)
  })
}

export function converSize(limit) {
  var size = "";
  if (limit < 0.1 * 1024) { //如果小于0.1KB转化成B  
    size = limit.toFixed(2) + "B";
  } else if (limit < 0.1 * 1024 * 1024) { //如果小于0.1MB转化成KB  
    size = (limit / 1024).toFixed(2) + "KB";
  } else if (limit < 1 * 1024 * 1024 * 1024) { //如果小于0.1GB转化成MB  
    size = (limit / (1024 * 1024)).toFixed(2) + "MB";
  } else { //其他转化成GB  
    size = (limit / (1024 * 1024 * 1024)).toFixed(2) + "GB";
  }

  var sizestr = size + "";
  var len = sizestr.indexOf("\.");
  var dec = sizestr.substr(len + 1, 2);
  if (dec == "00") { //当小数点后为00时 去掉小数部分  
    return sizestr.substring(0, len) + sizestr.substr(len + 3, 2);
  }
  return sizestr;
}

export const InnerStorage = (() => {
  var storage = {}
  return {
    set(key, val) {
      storage[key] = val
      return this
    },
    get(key) {
      return storage[key]
    }
  }
})()

export const searchMemberDebounce = debounce(searchMember, 500)

export function deepClone(obj) {
  var objClone = Array.isArray(obj) ? [] : {};
  if (obj && typeof obj === "object") {
    for (const key in obj) {
      if (obj.hasOwnProperty(key)) {
        if (obj[key] && typeof obj[key] === "object") {
          objClone[key] = deepClone(obj[key]);
        } else {
          objClone[key] = obj[key];
        }
      }
    }
  }
  return objClone;
}
