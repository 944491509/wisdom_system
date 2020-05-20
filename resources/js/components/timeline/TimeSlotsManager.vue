<template>
  <div class="block">
    <div class="title-bar">作息时间 <el-button type="primary" @click="addTimeSlot">添加</el-button></div>
    <div class="content"  v-for="(grade, index) in allTimeSlot" :key="index">
      <div class="title"> <span :class="'tag style' +index"></span>{{grade.name}}</div>
      <el-timeline class="frame-wrap">
        <el-timeline-item
          v-for="(activity, index) in grade.time_slot"
          :key="index"
          type="primary"
          :color="activity.color"
          :timestamp="activity.from + '-' + activity.to"
        >
          <p style="padding: 0;color: #409EFF;" @click="editTimeSlot(grade.name,activity)">
            {{activity.name}}
          </p>
        </el-timeline-item>
      </el-timeline>
    </div>

    <slot></slot>
  </div>
</template>
<script>
import { Constants } from "../../common/constants";
import { Util } from "../../common/utils";

export default {
  name: "TimeSlotsManager",
  props: {
    school: {
      type: String,
      required: true
    },
    schoolid: {
      required: true
    },
    dotSize: {
      type: String,
      required: false,
      default: "normal"
    }
  },
  data() {
    return {
      timeFrame: [],
      highlightIdx: -1,
      allTimeSlot:[],
    };
  },
  watch: {

  },
  mounted() {
    axios
      .get('/api/school/getAllTimeSlot' + "?school_id=" + this.schoolid)
      .then(res => {
        if (Util.isAjaxResOk(res)) {
          this.allTimeSlot = res.data.data;
        }
      });
  },
  methods: {
    editTimeSlot: function(grade, activity) {
      this.$emit("edit-time-slot", {
        timeSlot: activity,
        schoolUuid: this.school,
        grade: grade,
        type:'edit'
      });
    },
    addTimeSlot:function(){
      this.$emit("edit-time-slot", {
        type:'add'
      });
    }
  }
};
</script>

<style scoped lang="scss">

.block {
  margin: 10px;
  .title-bar {
    display: flex;
    padding: 0 20px 9px;
    line-height: 17px;
    font-size: 20px;
    border-radius: 2px 2px 0 0;
    color: #3a405b;
    align-items: center;
    justify-content: space-between;
    font-weight: 600;
    border-bottom: 1px dotted rgba(0, 0, 0, 0.2);
  }
  .title {
    font-size: 18px;
    .tag{
        height: 20px;
        width: 20px;
        margin-right: 20px;
        display: inline-block;
        position: relative;
        vertical-align: middle;
        margin-top: -4px;
        &::after{
          border: 10px solid transparent;
          content: "";
          position: absolute;
          right: -20px;
          // border-left-color: antiquewhite;
        }
        &.style0{
          background-color: #ed931b;
           &::after{
             border-left-color:#ed931b;
          }
        }
        &.style1{
          background-color: #3dc7dd;
           &::after{
             border-left-color:#3dc7dd;
          }
        }
        &.style2{
          background-color: #7c68e9;
          &::after{
            border-left-color:#7c68e9;
          }
        }
        &.style3{
          background-color: #e9689e;
          &::after{
            border-left-color:#e9689e;
          }
        }
        &.style4{
          background-color: #7ded85;
          &::after{
            border-left-color:#7ded85;
          }
        }


    }
  }
  .content{
    margin: 20px;
  }
  .frame-wrap {
    margin-top: 20px;
    margin-left: 5px;
  }
}

.el-dropdown.grades {
  cursor: pointer;
  padding-left: 12px;
  .el-dropdown-link {
    cursor: pointer;
  }
}
.grade-item {
  cursor: pointer;
}

</style>
