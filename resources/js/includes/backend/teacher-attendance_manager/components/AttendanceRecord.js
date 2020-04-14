import { Mixins } from "../Mixins";
import { _load_attendance,_load_all, catchErr } from "../api/index";
Vue.component("AttendanceRecord", {
  template: `
    <div class="attendance-record-container">
        fdsfsdfdssdfds
    </div>`,
  mixins: [Mixins],
  data() {
    return {
    };
  },
  methods: {
    _record(item) {},
  }
});
