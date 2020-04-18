<template>
  <div class="block">
    <h2 class="title-bar">
      作息时间表
      <el-dropdown>
        <span class="el-dropdown-link">
          {{selectedGrade.text}}
          <i class="el-icon-arrow-down el-icon--right"></i>
        </span>
        <el-dropdown-menu slot="dropdown">
          <el-dropdown-item v-for="(grade, index) in grades" :key="index">
            <span @click="choseGrade(grade)" class="grade-item">{{grade.text}}</span>
          </el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>
    </h2>
    <el-timeline class="frame-wrap">
      <el-timeline-item
        v-for="(activity, index) in timeFrame"
        :key="index"
        :icon="activity.icon"
        :type="activity.type"
        :color="activity.color"
        :size="activity.size"
        :timestamp="activity.timestamp"
      >
        <p style="padding: 0;color: #409EFF;" v-on:click="editTimeSlot(activity, index)">
          {{activity.content}}
          &nbsp;
          <i
            class="el-icon-check"
            v-if="index === highlightIdx"
          ></i>
        </p>
      </el-timeline-item>
    </el-timeline>
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
      grades: [],
      selectedGrade: {}
    };
  },
  watch: {
    selectedGrade: {
      deep: true,
      immediate: true,
      handler(grade) {
        if (!grade || !grade.year) {
          return;
        }
        axios
          .post(Constants.API.LOAD_TIME_SLOTS_BY_SCHOOL, {
            school: this.school,
            year: grade.year
          })
          .then(res => {
            if (Util.isAjaxResOk(res)) {
              this.timeFrame = [];
              res.data.data.time_frame.forEach(item => {
                this.timeFrame.push({
                  timestamp: item.from + " - " + item.to,
                  size: this.dotSize,
                  // color: '#0bbd87',
                  type: "primary",
                  icon: "",
                  content: item.name,
                  id: item.id,
                  origin: item
                });
              });
            }
          });
      }
    }
  },
  mounted() {
    axios
      .get(Constants.API.LOAD_GRADE_OF_SCHOOL + "?school_id=" + this.schoolid)
      .then(res => {
        if (Util.isAjaxResOk(res)) {
          this.grades = res.data.data;
          this.selectedGrade = this.grades[0];
        }
      });
  },
  methods: {
    editTimeSlot: function(activity, index) {
      this.highlightIdx = index;
      const timeSlot = Util.GetItemById(activity.id, this.timeFrame);
      this.$emit("edit-time-slot", {
        timeSlot: timeSlot.origin,
        schoolUuid: this.school
      });
    },
    choseGrade(grade) {
      this.selectedGrade = grade;
    }
  }
};
</script>

<style scoped lang="scss">
.block {
  margin: 10px;
  .title-bar {
    display: block;
    line-height: 50px;
    .el-dropdown {
      cursor: pointer;
      .el-dropdown-link {
        cursor: pointer;
      }
    }
  }
  .frame-wrap {
    margin-top: 20px;
  }
}
.grade-item {
  cursor: pointer;
}
</style>