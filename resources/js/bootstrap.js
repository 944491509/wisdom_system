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
      console.log('进入axios response 拦截')
      if(response.status == 200 ){
        if(response.data.code && response.data.code != 1000){
          Message({
            message: response.data.message,
            type: 'warning'
          });
        }
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
      return response;
    }, function (error) {
      let message ="";
      try{
        message = error.response.data.error.description ;
        if(error.response.status == 401){
          message = "权限不足"
        }
      }catch(e){
        message = error.message
      }
      Message({
        message: message,
        type: 'warning'
      });
      return Promise.reject(error);
    });
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}


$(".page-content-wrapper").on('click',"a:not(#consultDeleteBtn)",function(e){
  var href = $(this).attr('href');
  if(href && href.indexOf('javascript') == -1){
    console.log('进入a 标签 拦截')
    let aclhref =  href + (href.indexOf('?') == -1? '?onlyacl=1':'&onlyacl=1')
    window.axios.get(aclhref).then(res => {
      window.location.href = href;
    }).catch(e => {
      console.log('错误')
    })
    e.preventDefault();
    return false;
  }
})


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
