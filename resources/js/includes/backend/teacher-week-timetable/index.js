// import { Util } from "../../../common/utils";
// import { Constants } from "../../../common/constants";

import store from "./store";
import {
  Mixins
} from "./Mixins";
import { Constants } from "../../../common/constants";
import { Util } from "../../../common/utils";
import WeekTimeTable from "./components/WeekTimeTable";
import WeekTimeHeader from "./components/WeekTimeHeader";
import WeekTitle from "./components/WeekTitle";
import WeekPrev from "./components/WeekPrev";
import WeekNext from "./components/WeekNext";

import "./index.css";
const teacherWeekTimetable = document.getElementById("teacherWeekTimetable");
if (teacherWeekTimetable) {
  new Vue({
    mixins: [Mixins],
    store,
    el: "#teacherWeekTimetable",
    template: `
        <div class="teacher-week-content">
        <WeekPrev />
        <div class="teacher-week-timetable-container">
            <div class="week-main-title">
                我的课表
                <el-dropdown trigger="click" class="grades">
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
            </div>
            <div class="teacher-week-timetable-main">
            <WeekTitle />
            <div class="teacher-week-timetable" v-loading="isTableLoading" :style="{'min-height':minHeight + 'px'}">
                <WeekTimeHeader />
                <WeekTimeTable />
            </div>
            </div>
        </div>
         <WeekNext />
        </div>
        `,
    created() {
      const d = document.getElementById('teacher-assistant-check-in-app');
        const school_id = d.getAttribute("data-school")
        axios
        .get(Constants.API.LOAD_GRADE_OF_SCHOOL + "?school_id=" + school_id)
        .then(res => {
          if (Util.isAjaxResOk(res)) {
            this.grades = res.data.data;
            this.selectedGrade = this.grades[0];
          }
        });
    },
    data() {
      return {
        minHeight: window.innerHeight - 299,
        grades: [],
        selectedGrade: {}
      }
    },
    methods: {
      choseGrade(grade) {
        this.selectedGrade = grade;
      }
    },
    watch: {
      selectedGrade: {
        deep: true,
        immediate: true,
        handler(grade) {
          if (!grade || !grade.year) {
            return;
          }
          const currentDate = moment(new Date()).format("YYYY-MM-DD");
          this._initData(currentDate, grade.year);
        }
      }
    },
  });
}
