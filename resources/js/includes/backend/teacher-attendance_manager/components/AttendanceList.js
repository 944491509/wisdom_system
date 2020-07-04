import { Mixins } from "../Mixins";
import { _load_attendance,_load_all, catchErr } from "../api/index";
Vue.component("AttendanceList", {
  template: `
    <div class="attendance-list-container">
    <el-table
      :data="data.data"
      v-loading="isTableLoading"
      :empty-text="isTableLoading? ' ':'暂无数据'"
      :style="{'width': '100%','min-height':minHeight + 'px'}"
      border
      :header-cell-style="{'background':'#F8F9FB','color':'#313B4C'}"
      >
      <el-table-column
        align="center"
        prop="title"
        label="考勤组"
        min-width="180">
      </el-table-column>
      <el-table-column
        align="center"
        prop="members"
        label="考勤人数"
        width="180">
      </el-table-column>
      <el-table-column
        align="center"
        prop="using_afternoon"
        label="考勤班次"
        width="180">
        <template slot-scope="scope">
          <span v-if="scope.row.using_afternoon">早中晚</span>
          <span v-else>早晚</span>
        </template>

      </el-table-column>
      <el-table-column
        align="center"
        prop="wifi_name"
        label="WiFi"
        min-width="120">
      </el-table-column>
      <el-table-column
        align="center"
        width="300"
      label="操作"
      >
      <template slot-scope="scope">
        <el-button @click="_record(scope.row.id)" type="primary" size="mini">记录</el-button>
        <el-button @click="_holiday_set(scope.row.id)" type="success"  size="mini">节假日</el-button>
        <el-button @click="_clock_set(scope.row.id)" type="primary" size="mini">时间</el-button>
        <el-button @click="_edit(scope.row.id)" type="warning" icon="el-icon-edit" size="mini"></el-button>
      </template>
    </el-table-column>
    </el-table>
    </div>`,
  mixins: [Mixins],
  data() {
    return {
      minHeight: window.innerHeight - 299
    };
  },
  methods: {
    async _record(attendance_id) {
      // /school_manager/teacher-attendance/load-clockins-daycount
      let onlyacl =  await axios.post(
        '/school_manager/teacher-attendance/load-clockins-daycount',
        {onlyacl: 1 }
      )
      if(onlyacl.data != 'ok') return;
      console.log('AAAAAA',attendance_id)
      this.SETOPTIONS({ isShowRecord: true, attendance_id });
    },
    async _holiday_set (attendance_id) {
      // /school_manager/teacher-attendance/load-attendance
      let onlyacl =  await axios.post(
        '/school_manager/teacher-attendance/load-attendance',
        {onlyacl: 1 }
      )
      if(onlyacl.data != 'ok') return;

      const [err, res] = await catchErr(_load_attendance({ attendance_id }));
      const exceptiondays = res.exceptiondays.map(({day}) => (day))
      this.SETOPTIONOBJ({
        key: "formData",
        value: {
          exceptiondays:res.exceptiondays
        }
      });
      this.SETOPTIONS({ visibleHolidaySet: true,attendance_id,exceptiondays });
    },
    async _edit(attendance_id) {
      let onlyacl =  await axios.post(
        '/school_manager/teacher-attendance/save-exceptionday',
        {onlyacl: 1 }
      )
      if(onlyacl.data != 'ok') return;
      this.SETOPTIONS({ visibleFormDrawer: true, isEditFormLoading: true,isCreated:false });
      if(!this.organizations.length) {
        let [, orgs] = await catchErr(_load_all({ school_id:this.schoolIdx }));
        // console.log(orgs)
        this.SETOPTIONS({ organizations: orgs });
      }
      const [err, res] = await catchErr(_load_attendance({ attendance_id }));
      if (err) return false;
      const { id, school_id, title, wifi_name, using_afternoon, organizations,managers,exceptiondays} = res;
      const formData = {
        attendance: {
          id, //编辑时传递
          school_id,
          title,
          wifi_name,
          using_afternoon: using_afternoon == 1
        },
        organizations,
        managers,
      };
      res && this.SETOPTIONS({ formData, isEditFormLoading: false,isCreated:false,teacherName:"" });
    },
    async _clock_set(attendance_id) {
      let onlyacl =  await axios.post(
        '/school_manager/teacher-attendance/load-attendance',
        {onlyacl: 1 }
      )
      if(onlyacl.data != 'ok') return;

      this.SETOPTIONS({ visibleClockDrawer: true, isEditFormLoading: true });
      const [err, res] = await catchErr(_load_attendance({ attendance_id }));
      if (err) return false;
      const { clocksets, using_afternoon } = res;
      if (clocksets.length) {
        this.SETOPTIONS({
          clockSetData: clocksets,
          usingAfternoon: using_afternoon == 1,
          isEditFormLoading: false,
          attendance_id
        });
      } else {
        const clockSetData = [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday",
          "Saturday",
          "Sunday"
        ].map(week => ({
          id: "",
          week,
          start: "",
          end: "",
          morning: "",
          morning_late: "",
          afternoon: "",
          afternoon_late: "",
          evening: "",
          is_weekday: 1
        }));
        this.SETOPTIONS({
          clockSetData,
          usingAfternoon: using_afternoon == 1,
          isEditFormLoading: false,
          attendance_id
        });
      }
    }
  }
});
