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
      SearchBarNew,
    },
    data() {
      return {
        mode,
        school_id: schooldom.getAttribute('data-school'),
        page: 1,
        pagination: {
          page: 1,
          pageCount: 0
        },
        list: []
      }
    },
    watch: {
      "pagination.page": function () {
        this.getList();
      }
    },
    methods: {
      getList(params = {}) {
        axios.post(
          '/api/pc/get-students', {
            page: this.pagination.page,
            school_id: this.school_id,
            where: {
              ...params
            }
          }
        ).then(res => {
          if (Util.isAjaxResOk(res)) {
            this.list = res.data.data.list
            this.pagination.pageCount = res.data.data.lastPage
          }
        })
      },
      optCommand(command) {
        switch (command) {
          case 'edit':
            window.open('/verified_student/profile/edit?uuid=a1ff6422-b69e-4e2a-b0bc-0dd6da5fb2b2')
            break
          case 'photo':
            window.open('/teacher/student/edit-avatar?uuid=a1ff6422-b69e-4e2a-b0bc-0dd6da5fb2b2')
            break
          default:
            break
        }
      },
      onPageChange(page) {
        this.pagination.page = page;
      },
    },
    created() {
      this.getList()
    }
  })
}
