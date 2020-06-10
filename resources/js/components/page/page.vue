<template>
  <div class="page">
    <el-pagination
      layout="prev, pager, next, total"
      :page-size="10"
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
    getPageData: {
      type: Object,
    },
    getListFun: {
      type: Function,
    },
  },
  computed: {
    totalCount () {
      return this.getPageData.total
    }
  },
  mounted() {
    console.log('CCC',this.getPageData)
  },
  methods: {
    handleEvent(e) {
      this.pageNum = type
      if (this.totalCount === 0) {
        this.pageNum = 0;
      }
      this.getPageData.currentPage = this.pageNum;
      this.getListFun()
    }
  }
}
</script>