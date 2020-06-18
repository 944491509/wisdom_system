import { mapState, mapActions, mapMutations } from "vuex";
import { _load_manager,catchErr } from "./api/index";

export const Mixins = {
  computed: {
    ...mapState([
      "data",
      "isTableLoading",
      "visibleFormDrawer",
      "formData",
      "organizations",
      'isEditFormLoading',
      'visibleClockDrawer',
      'clockSetData',
      'usingAfternoon',
      'usingMorning',
      'attendance_id',
      'visibleHolidaySet',
      'schoolIdx',
      'isCreated',
      'teacherName',
      'exceptiondays',
      'isShowRecord',
      'resDate'
    ])
  },
  methods: {
    ...mapMutations(["SETOPTIONS", "SETOPTIONOBJ"]),
    async _initData() {
      this.SETOPTIONS({ isTableLoading: true ,schoolIdx:school_id});
      const [err, data] = await catchErr(_load_manager());
      data && this.SETOPTIONS({ data, isTableLoading: false });
    },
    getQueryString(name) {
      var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i')
      var r = window.location.search.substr(1).match(reg)
      if (r != null) return unescape(r[2])
      return null
    },
  }
};
