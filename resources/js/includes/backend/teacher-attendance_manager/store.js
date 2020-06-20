import Vuex from "vuex";
const clockSetData = [
  "Monday",
  "Tuesday",
  "Wednesday",
  "Thursday",
  "Friday",
  "Saturday",
  "Sunday"
].map(week => ({
  id:'',
  week,
  start: "",
  end: "",
  morning: "",
  morning_late: "",
  morning_end: "",
  morning_end2: "",
  afternoon: "",
  afternoon_late: "",
  evening: "",
  is_weekday: true
}));
export default new Vuex.Store({
  state: {
    data: {},
    isTableLoading: false,
    visibleFormDrawer: false,
    formData: {
      managers:[],
      attendance: {
        id: "", //编辑时传递
        school_id: "",
        title: "",
        wifi_name: "",
        using_afternoon: false, //是否启用中午打卡
        using_morning: true //是否启用上午打卡
      },
      organizations: [],
      exceptiondays:[]
    },
    organizations: [],
    isEditFormLoading: false,
    visibleClockDrawer: false,
    clockSetData,
    usingAfternoon:false,
    usingMorning:false,
    attendance_id:0,
    visibleHolidaySet:false, //节假日
    schoolIdx:'',
    isCreated:false,
    teacherName:'',
    exceptiondays:[],//节假日数据
    isShowRecord: false,
    resDate: {
      morning: {
        ok: {},
        late: {},
        later: {},
        not: {},
      },
      afternoon: {
        ok: {},
        late: {},
        later: {},
        not: {},
      },
      evening: {
        ok: {},
        late: {},
        later: {},
        not: {},
      }
    },
    groupTitle: ''
  },
  mutations: {
    SETOPTIONS(state, res) {
      Object.keys(res).forEach(item => (state[item] = res[item]));
    },
    SETOPTIONOBJ(state, { key, value }) {
      Object.keys(value).forEach(item => (state[key][item] = value[item]));
    }
  }
});
