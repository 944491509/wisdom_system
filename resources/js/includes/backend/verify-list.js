// 快速定位用户的搜索框: 会更加当前的状况, 来搜索用户和学院 系等
import {
  Util
} from "../../common/utils";

import SearchBarNew from '../../components/quickSearch/SearchBarNew'

const appdom = document.getElementById('verify-list')
const schooldom = document.getElementById('quick-search-current-school-id')


if (appdom && schooldom) {
  let mode = appdom.getAttribute('name')
  new Vue({
    el: '#verify-list',
    components: {
      SearchBarNew
    },
    data() {
      return {
        mode,
        school_id: schooldom.getAttribute('data-school'),
        page: 1,
        list: []
      }
    },
    methods: {
      getList(params = {}) {
        axios.post(
          '/api/pc/get-students', {
            page: this.page,
            school_id: this.school_id,
            where: {
              ...params
            }
          }
        ).then(res => {
          if (Util.isAjaxResOk(res)) {
            // this.oneList = res.data.data.list
          }
        })
      }
    },
    created() {
      this.getList()
    }
  })
}
