// 学校时间段管理
import {
  saveTimeSlot,
  addTimeSlot,
  editTimeSlot,
  deleteTimeSlot
} from "../../common/timetables";
import { Util } from "../../common/utils";
import { Constants } from "../../common/constants";

if (document.getElementById("school-time-slots-manager")) {
  new Vue({
    el: "#school-time-slots-manager",
    data() {
      return {
        needReload: false,
        currentTimeSlot: {
          id: "",
          from: "",
          to: "",
          name: "",
          type: "",
          grade_id: "",
          status: false
        },
        showEditForm: false,
        mode: "add",
        schoolUuid: "",
        grades: [],
        schoolid: "",
      };
    },
    methods: {
      editTimeSlotHandler: async function (payload) {

        this.mode = payload.type;
        if (payload.type == "add") {
          // let onlyacl =  await addTimeSlot({onlyacl:1})
          // if(onlyacl.data != 'ok') return;
          this.currentTimeSlot = {
            id: "",
            from: "",
            to: "",
            name: "",
            type: "",
            grade_id: "",
            status: false
          };
          this.showEditForm = true;
          return;
        }
        if (payload.type == "edit") {
          // let onlyacl =  await saveTimeSlot({onlyacl:1})
          // if(onlyacl.data != 'ok') return;

          const keys = Object.keys(payload.timeSlot);
          keys.forEach(key => {
            this.currentTimeSlot[key] = payload.timeSlot[key];
          });
          console.log(payload);
          // this.currentTimeSlot = payload.timeSlot;
          this.schoolUuid = payload.schoolUuid;
          this.currentTimeSlot.grade_id = payload.grade.year;
          this.currentTimeSlot.from = payload.timeSlot.from;
          this.currentTimeSlot.to = payload.timeSlot.to;
          this.currentTimeSlot.id = payload.timeSlot.id;
          this.currentTimeSlot.status = Boolean(payload.timeSlot.status);
          this.showEditForm = true;
        }
      },
      onSubmit: function () {
        console.log(this.currentTimeSlot);
        if (this.currentTimeSlot.grade_id == "") {
          this.$message.error("年级不可以为空");
          return;
        }
        if (!this.currentTimeSlot.type) {
          this.$message.error("类型不可以为空");
          return;
        }
        if (this.currentTimeSlot.name.trim() === "") {
          this.$message.error("作息时间表的名称不可以为空");
          return;
        }
        if (
          Util.isEmpty(this.currentTimeSlot.from) ||
          Util.isEmpty(this.currentTimeSlot.to)
        ) {
          this.$message.error("作息时间表的时间段不可以为空");
          return;
        }
        if (this.currentTimeSlot.to < this.currentTimeSlot.from) {
          this.$message.error("作息时间表的结束时间不可以早于开始时间");
          return;
        }
        if (this.mode == "add") {
          addTimeSlot({
            year: this.currentTimeSlot.grade_id,
            school_id: Number(this.schoolid),
            type: this.currentTimeSlot.type,
            from: this.currentTimeSlot.from,
            to: this.currentTimeSlot.to,
            name: this.currentTimeSlot.name,
            // status: Number(this.currentTimeSlot.status)
          }).then(res => {
            if (Util.isAjaxResOk(res)) {
              this.$message({
                message: "添加成功, 作息表正在重新加载 ...",
                type: "success"
              });
              window.location.reload();
            } else {
              this.$message.error("错了哦，这是一条错误消息");
            }
          });
        }
        if (this.mode == "edit") {
          saveTimeSlot({
            id:this.currentTimeSlot.id,
            type: this.currentTimeSlot.type,
            from: this.currentTimeSlot.from,
            to: this.currentTimeSlot.to,
            name: this.currentTimeSlot.name,
            status: Number(this.currentTimeSlot.status)
          }).then(res => {
            if (Util.isAjaxResOk(res)) {
              this.$message({
                message: "修改成功, 作息表正在重新加载 ...",
                type: "success"
              });
              window.location.reload();
            } else {
              this.$message.error("错了哦，这是一条错误消息");
            }
          });
        }
      },
      toChangedHandler: function (to) {
        if (to < this.currentTimeSlot.from) {
          this.$message.error("作息时间表的结束时间不可以早于开始时间");
        }
      },
      getGradeList() {
        axios
          .get(
            Constants.API.LOAD_GRADE_OF_SCHOOL + "?school_id=" + this.schoolid
          )
          .then(res => {
            if (Util.isAjaxResOk(res)) {
              this.grades = res.data.data;
            }
          });
      },
      deleteItem() {
        this.$confirm('此操作将永久该作息时间, 是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          deleteTimeSlot(this.currentTimeSlot.id).then((res) => {
            if (Util.isAjaxResOk(res)) {
              this.$message({
                message: "删除成功, 作息表正在重新加载 ...",
                type: "success"
              });
              window.location.reload();
            } else {
              this.$message.error("删除失败！");
            }
          })
        }).catch(() => {
          this.$message({
            type: 'info',
            message: '已取消删除'
          });
        });

      },
      formSave(){
        axios.post('/school_manager/school/config/update').then(res => {
          console.log('res', res)
          if (res.status === 200) {
            document.getElementById("btn-save-school-config").click()
          }
        })
      }
    },
    mounted() {
      let dom = document.getElementById("school-time-slots-manager");
      this.schoolid = dom.getAttribute("schoolid");
      this.getGradeList();
    }
  });
}
