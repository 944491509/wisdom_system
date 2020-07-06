import { Mixins } from "../Mixins";
Vue.component("AttendanceAdd", {
  template: `
    <div class="attendance-add">
        <div class="title">考勤组</div>
        <div class="btn-create">
        <el-button @click="_add()" icon="el-icon-plus" type="primary" size="mini">添加</el-button>
        </div>
    </div>`,
  mixins: [Mixins],
  methods: {
    _add() {
      axios.post(
        '/school_manager/teacher-attendance/save-exceptionday',
        {onlyacl: 1 }
      ).then(res => {
        if(res.data == 'ok'){
          // let school_id = 1 //接口
          const formData = {
            attendance: {
              id: "", //编辑时传递
              school_id:this.schoolIdx,
              title: "",
              wifi_name: "",
              using_afternoon: true //是否启用中午打卡
            },
            organizations: [],
            managers:[],
            exceptiondays:[]
          };
          this.SETOPTIONS({ formData, organizations: [],isCreated:true ,teacherName:''});
          this.SETOPTIONS({ visibleFormDrawer: true, isEditFormLoading: false });
        }
      })

    }
  }
});
