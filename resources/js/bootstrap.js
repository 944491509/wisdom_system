// window._ = require('lodash');
import {Message} from 'element-ui';

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

// try {
//     window.Popper = require('popper.js').default;
//     window.$ = window.jQuery = require('jquery');
//
//     require('bootstrap');
// } catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

// window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');
const API_TOKEN = document.head.querySelector('meta[name="api-token"]');

if (token) {
    window.axios.defaults.headers.common = {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': token.content,
        'Authorization': 'Bearer ' + API_TOKEN.content,
        'Accept': 'application/json',
    };
    window.axios.interceptors.response.use(function (response) {
      if(response.status == 200 ){
        return response;
      }
      if(response.status == 401){
        Message({
          message: '权限不足',
          type: 'warning'
        });
      }else{
        Message({
          message: response.statusText,
          type: 'warning'
        });
      }
      // 对响应数据做点什么
      return response;
    }, function (error) {
      // 对响应错误做点什么
      return Promise.reject(error);
    });
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}


$(".page-content-wrapper").on('click','a', function(e){
  var href = $(this).attr('href');

  if(href && href.indexOf('javascript') == -1){
    getAuth(href).then(()=>{
      window.location.href = href;
    }).catch(res => {
      if(res.status == 401){
        Message({
          message: '权限不足',
          type: 'warning'
        });
      }else{
        Message({
          message: res.statusText,
          type: 'warning'
        });
      }
    })
    e.preventDefault()
    return false;
  }
})
function getAuth(href){
  return new Promise((resolve,reject) =>{
    $.ajax({
      type: "GET",
      cache: false,
      url: href,
      dataType: "html",
      success: function (res) {
        console.log(res)
        resolve();
      },
      error: function (res) {
        reject(res)
      }
    });
  })

}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     encrypted: true
// });
