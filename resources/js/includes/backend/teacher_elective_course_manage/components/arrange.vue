<template>
  <span>
    <div v-for="(arr, index) in arrangements" :key="index">
      <span v-if="!arr.to">第{{arr.from}}周 {{matchWeek[arr.day_index]}} {{arr.time}}</span>
      <span v-else>第{{arr.from}}周——第{{arr.to}}周 {{matchWeek[arr.day_index]}} {{arr.time}}</span>
    </div>
  </span>
</template>
<script>
export default {
  name: "arrange-ment",
  props: {
    arranges: {
      type: Array,
      default: Array
    }
  },
  data() {
    return {
      matchWeek: {
        1: "周一",
        2: "周二",
        3: "周三",
        4: "周四",
        5: "周五",
        6: "周六",
        7: "周日"
      }
    };
  },
  /**
    day_index: 3
    time: "预备"
    week: 15
    week_day: ""
     */
  computed: {
    arrangements() {
      let arr = [];
      let initone;
      let preone;
      for (let i = 0; i < this.arranges.length; i++) {
        let current = this.arranges[i];
        if (!initone) {
          initone = current;
          if (i === this.arranges.length - 1) {
            arr.push({
              from: initone.week,
              to: null,
              day_index: initone.day_index,
              time: initone.time
            });
          }
        } else {
          if (
            initone.time !== current.time ||
            initone.day_index !== current.day_index
          ) {
            arr.push({
              from: initone.week,
              to: preone ? preone.week : null,
              day_index: initone.day_index,
              time: initone.time
            });
            initone = current;
            preone = null;
            if (i === this.arranges.length - 1) {
              arr.push({
                from: initone.week,
                to: null,
                day_index: initone.day_index,
                time: initone.time
              });
            }
          } else {
            preone = current;
            if (i === this.arranges.length - 1) {
              arr.push({
                from: initone.week,
                to: preone.week,
                day_index: initone.day_index,
                time: initone.time
              });
            }
          }
        }
      }
      return arr;
    }
  }
};
</script>