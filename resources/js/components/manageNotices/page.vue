<template>
  <div class="page">
    <el-pagination
      layout="prev, pager, next, total"
      :page-size="15"
      @current-change="handleEvent"
      :current-page.sync="pageNum"
      :total="totalCount"
    >
    </el-pagination>
  </div>
</template>
<script>
export default {
  data () {
    return {
      pageNum: 0
    }
  },
  props: {
    getpagedata: {
      type: Object,
    }
  },
  computed: {
    totalCount () {
      return this.getpagedata.total
    }
  },
  mounted() {
  },
  methods: {
    handleEvent(e) {
      this.pageNum = e
      if (this.totalCount === 0) {
        this.pageNum = 0;
      }
      this.getpagedata.page = this.pageNum;
      this.$emit('get-list-fun')
    }
  }
}
</script>