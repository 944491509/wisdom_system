/**
 * 设备管理
 */
if (document.getElementById('facility-form')){
    new Vue({
        el: '#facility-form',
        data: {
          type: '', // 设备类型
          card_type: '', // 班牌类型
          show: false
        },
    });
}
