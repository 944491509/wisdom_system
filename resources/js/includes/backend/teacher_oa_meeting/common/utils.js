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

export class DownLoadUtil {
  static download(blob, filename) {
		let b = DownLoadUtil.getBrowser();
		switch (b) {
			case "ie":
				navigator.msSaveBlob(blob, filename);
				break;
			case "edge":
				navigator.msSaveBlob(blob, filename);
				break;
			case "opera":
				DownLoadUtil.fetchDown(blob, filename);
				break;
			case "maxthon win":
				DownLoadUtil.fetchDown(blob, filename);
				break;
			// case "maxthon":
			// 	call(url);
			// 	break;
			case "firefox":
				DownLoadUtil.fetchClickDown(blob, filename);
				break;
			// case "safari":
			// 	call(url);
			// 	break;
			case "chrome":
				DownLoadUtil.fetchDown(blob, filename);
				break;
			default:
				try {
					DownLoadUtil.fetchDown(blob, filename);
				} catch (e) {
					alert(e);
					//TODO handle the exception
				}
				break;
		}
	}

	static fetchDown(blob, filename) {
		var urlObject = window.URL || window.webkitURL || window;
		var a = document.createElement('a');
		var url = urlObject.createObjectURL(blob);
		a.href = url;
		a.download = filename;
		a.click();
		window.URL.revokeObjectURL(url)
	}

	static fetchClickDown(blob, filename) {
		var urlObject = window.URL || window.webkitURL || window;
		var save_link = document.createElement("a");
		save_link.href = urlObject.createObjectURL(blob);
		save_link.download = filename;
		DownLoadUtil.fakeClick(save_link)
	}

	static fakeClick (obj) {
		var ev = document.createEvent("MouseEvents");
		ev.initMouseEvent("click", true, false, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
		obj.dispatchEvent(ev)
	}

	static getBrowser () {
		if (!!window.ActiveXObject || "ActiveXObject" in window) {
			return "ie"
		}
		var userAgent = navigator.userAgent.toLocaleLowerCase();
		if (userAgent.indexOf("opr") > -1) {
			return "opera";
		}
		if (userAgent.indexOf("maxthon") > -1) {
			if (userAgent.indexOf("windows") > -1) {
				return "maxthon win"
			}
			return "maxthon"
		}
		if (userAgent.indexOf("edge") > -1) {
			return "edge"
		}
		if (userAgent.indexOf("firefox") > -1) {
			return "firefox"
		}
		if (userAgent.indexOf("safari") > -1 && userAgent.indexOf("chrome") == -1) {
			return "safari"
		}

		if (userAgent.indexOf("chrome") > -1) {
			return "chrome"
		}
		return 'other';
	};

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
		set(key,val){
			storage[key] = val
			return this
		},
		get(key){
			return storage[key]
		}
	}
})()

export const searchMemberDebounce = debounce(searchMember, 500)
