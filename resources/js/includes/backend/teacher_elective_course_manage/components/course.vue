<template>
  <div>
    <div class="list-item" v-for="(course, index) in list" :key="index">
      <span class="name">{{course.name}}</span>
      <span class="arrangement">
        <div
          v-for="(arrange, index) in course.arrangement"
          :key="index"
        >{{`第${arrange.week}周 周${arrange.day_index} ${arrange.time}`}}</div>
      </span>
      <span class="date">{{course.created_at}}</span>
      <course-status :status="course.status" />
      <span class="detail" @click="goDetail(course)">
        <span class="detail-btn">
          <span class="icon-eyes"></span>
        </span>
      </span>
    </div>
    <el-pagination
      background
      layout="prev, pager, next"
      :page-count="pagination.pageCount"
      :current-page="pagination.page"
      @current-change="onPageChange"
    ></el-pagination>
  </div>
</template>
<script>
import { CourseApi } from "../common/api";
import { CourseMode, CourseStatus } from "../common/enum";
import { Util } from "../../../../common/utils";
import Status from './status'

export default {
  name: "course-list",
  components: {
    'course-status': Status
  },
  props: {
    mode: {
      type: String,
      required: true,
      default: ""
    }
  },
  data() {
    return {
      list: [],
      pagination: {
        page: 1,
        pageCount: 0
      }
    };
  },
  computed: {
    getStatusText() {
      return function(status) {
        return CourseStatus[status].text;
      };
    },
    getStatusClass() {
      return function(status) {
        return CourseStatus[status].classes;
      };
    }
  },
  watch: {
    "pagination.page": page => {
      this.getCourseList();
    }
  },
  methods: {
    goDetail(course) {
      this.$emit("detail", course, this.mode);
    },
    getCourseList() {
      if (this.mode === CourseMode.applying.status) {
        CourseApi.excute("applyingList", {
          page: this.pagination.page
        }).then(res => {
          this.list = res.data.data;
          this.pagination.pageCount = res.data.lastPage;
        });
      } else {
        CourseApi.excute("list", {
          page: this.pagination.page,
          type: CourseMode[this.mode].code
        }).then(res => {
          this.list = res.data.data;
          this.pagination.pageCount = res.data.lastPage;
        });
      }
    },
    onPageChange(page) {
      this.pagination.page = page;
    }
  }
};
</script>
<style lang="scss" scoped>
.list-item {
  display: flex;
  font-size: 14px;
  border-bottom: 1px solid #eaedf2;
  padding: 12px;
  transition: all 0.5s;
  .name {
    flex: 2;
  }
  .arrangement {
    flex: 4;
    color: #9ba3af;
  }
  .date {
    flex: 2;
  }
  .status {
    flex: 2;
    text-align: center;
  }
  .detail {
    flex: 1;
    text-align: right;
    .detail-btn {
      background-color: #4ea5fe;
      cursor: pointer;
      width: 34px;
      display: inline-flex;
      height: 24px;
      padding: 0 6px;
      border-radius: 4px;
      .icon-eyes {
        height: 24px;
        width: 24px;
        background-size: contain;
        background-image: url(../assets/icon-eye.png);
      }
    }
  }
}
.list-item:last-child {
  border-bottom: none;
}
.list-item:hover {
  box-shadow: 0 0 6px #ccc;
}
.el-pagination {
  float: right;
  padding-top: 16px;
}
</style>
