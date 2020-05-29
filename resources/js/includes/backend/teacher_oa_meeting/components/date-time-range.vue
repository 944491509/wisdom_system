<template>
  <div class="date-time-range">
    <el-date-picker
      v-if="!isTime"
      :picker-options="{disabledDate:(time)=>time.getTime() < Date.now()-24*60*60*1000}"
      v-model="data.date"
      :disabled="disabled"
      type="date"
      placeholder="选择日期"
    ></el-date-picker>
    <div class="time-range">
      <el-time-select
        placeholder="起始时间"
        :disabled="(!data.date && !isTime) || disabled"
        v-model="data.startTime"
        :picker-options="{
            start: '00:00',
            step: '00:30',
            end: '24:00',
            minTime: minTime,
            maxTime: data.endTime
        }"
      ></el-time-select>
      <span class="split" :class="{disabled: (!data.date && !isTime)||disabled}">—</span>
      <el-time-select
        placeholder="结束时间"
        :disabled="(!data.date && !isTime) || disabled"
        v-model="data.endTime"
        :picker-options="{
            start: '00:00',
            step: '00:30',
            end: '24:00',
            minTime: data.startTime
        }"
      ></el-time-select>
    </div>
  </div>
</template>
<script>
import moment from "moment";

export default {
  name: "date-time-picker",
  props: {
    isTime: {
      type: Boolean,
      default: false
    },
    minTime: {
      type: String,
      default: "00:00"
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      data: {
        startTime: "",
        endTime: "",
        date: ""
      }
    };
  },
  watch: {
    data: {
      immediate: true,
      deep: true,
      handler(val) {
        if ((this.isTime || val.startTime) && val.endTime && val.date) {
          if (this.isTime) {
            this.$emit("input", [val.startTime, val.endTime]);
          } else {
            let datestr = moment(val.date).format("YYYY-MM-DD");
            this.$emit("input", [
              datestr + " " + val.startTime,
              datestr + " " + val.endTime
            ]);
          }
        } else {
          this.$emit("input", "");
        }
      }
    }
  }
};
</script>
<style lang="scss" scoped>
.date-time-range {
  border-radius: 4px;
  border: 1px solid #dcdfe6;
  width: 100%;
  display: flex;
  padding: 0 2px;
  .el-date-editor--date {
    flex: 1;
    ::v-deep .el-input__inner {
      border: none;
      // height: 32px;
      // line-height: 32px;
      border-radius: 0;
      border-right: 1px solid #efefef;
      padding-right: 0;
    }
  }
  .time-range {
    flex: 2;
    display: flex;
    .split {
      flex: none;
      color: #dcdcdc;
    }
    .split.disabled {
      background-color: #f5f7fa;
    }
    .el-date-editor--time-select {
      flex: 1;
      width: initial;
      ::v-deep .el-input__inner {
        border: none;
        text-align: center;
        padding-right: 0;
        border-radius: 0;
      }
    }
  }
}
</style>