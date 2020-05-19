<template>
  <span class="status" :class="'status'+status">{{matchStatus[status]}}</span>
</template>
<script>
export default {
  name: "course-status",
  props: {
    status: {
      default: 1
    }
  },
  data() {
    return {
      matchStatus: {
        1: "已关闭",
        2: "待选课",
        3: "报名中",
        4: "待开课",
        5: "已结束",
        6: "进行中",
        7: "异常状态",
        8: "待审核",
        9: "被拒绝"
      }
    };
  }
  // 1: "已关闭",
  //       2: "待选课",
  //       3: "报名中",
  //       4: "待开课",
  //       5: "已结束",
  //       6: "进行中",
  //       7: "异常状态",
  //       8: "待审核",
  //       9: "被拒绝"
};
</script>
<style lang="scss" scoped>
.status{
    color: #fe862e;
}
.status.status1 {
  color: #bac1cd;
}
.status.status9 {
  color: #fd2e2e;
}
.status.status6 {
  color: #6dcc58;
}
</style>