/**
 * 设备管理
 */
import {
  Util
} from "../../common/utils";
import {
  Constants
} from "../../common/constants";
if (document.getElementById('facility-form')) {
  new Vue({
    el: '#facility-form',
    data: {
      type: '', // 设备类型
      card_type: '', // 班牌类型
      show: false,
      options: [],
      value: '',
      loading: false,
      loading_text: '搜索中......',
    },
    methods: {
      searchGrade: function (queryString) {
        const _queryString = queryString.trim();
        if (Util.isEmpty(_queryString)) {
          // 如果视图搜索空字符串, 那么不执行远程调用, 而是直接回调一个空数组
          this.options = [];
        } else {


        }
      },
      initGrades() {
        this.loading = true;
        let dom = document.getElementById('app-init-data-holder')
        let school_id = 2
        if(dom){
            school_id = dom.getAttribute('data-school')
        }
        axios.post(
          Constants.API.LOAD_GRADES_BY_NAME, {
            school_id
          }
        ).then(res => {
          if (Util.isAjaxResOk(res)) {
            this.loading = false;
            this.options = res.data.data;
          }
        })
      }
    },
    mounted() {
      let type = this.$refs.facilityInitType.getAttribute('value')
      if (!isNaN(type)) {
        this.type = parseInt(type)
        let card_type = this.$refs.facilityInitCardType.getAttribute('value')
        if (!isNaN(type)) {
          this.card_type = card_type
          this.initGrades()
          let value = this.$refs.facilityInitCardValue.getAttribute('value')
          if(!isNaN(type)){
              this.value = value
          }
        }
      }
    }
  });
}
