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
        where: {

        },
        list: [],
        allchecked: false,
        total: null
      }
    },
    watch: {
      "pagination.page": function () {
        this.getList();
      },
      allchecked: function (value) {
        if (value) {
          this.list.forEach(item => {
            item.checked = true
          })
        } else {
          this.list.forEach(item => {
            item.checked = false
          })
        }
      }
    },
    methods: {
      getList(params = {}) {
        axios.post(
          mode === 'students' ? '/api/pc/get-students' : '/api/pc/get-teachers', {
            page: this.pagination.page,
            school_id: this.school_id,
            where: {
              ...this.where,
            }
          }
        ).then(res => {
          if (Util.isAjaxResOk(res)) {
            let list = res.data.data.list
            if (this.total === null) {
              this.total = res.data.data.total
              try {
                document.getElementById('veri-list-total').innerText = this.total
              } catch (e) {}
            }
            if (this.allchecked) {
              list.forEach(item => {
                item.checked = true
              })
            } else {
              list.forEach(item => {
                item.checked = false
              })
            }
            this.list = list
            this.pagination.pageCount = res.data.data.lastPage
          }
        })
      },
      search() {
        this.pagination.page = 1
        this.getList()
      },
      optCommand(command, data) {
        switch (command) {
          case 'edit':
            window.open('/verified_student/profile/edit?uuid=' + data.uuid)
            break
          case 'photo':
            window.open('/teacher/student/edit-avatar?uuid=' + data.uuid)
            break
          default:
            break
        }
      },
      gokebiao(data) {
        window.open('/school_manager/timetable/manager/view-grade-timetable?uuid=' + data.grade_id)
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
