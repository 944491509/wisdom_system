<template>
  <div class="detail-container">
    <div class="detail detail-panel">
      <div class="detail-item">
        <span class="title">课程名称</span>
        <span class="content">
          <span>{{course.name}}</span>
          <course-status style="float: right;" :status="course.status" />
        </span>
      </div>
      <div class="detail-item">
        <span class="title">教师名称</span>
        <span class="content">{{course.teacher_name}}</span>
      </div>
      <div class="detail-item">
        <span class="title">学分</span>
        <span class="content">{{course.scores}}分</span>
      </div>
      <div class="detail-item">
        <span class="title">学生人数</span>
        <span class="content">{{(course.user_list || []).length}}人</span>
      </div>
      <div class="detail-item">
        <span class="title">开课人数</span>
        <span class="content">满{{course.open_num}}人</span>
      </div>
      <div class="detail-item">
        <span class="title">报名截止时间</span>
        <span class="content">{{course.elective_expired_at}}</span>
      </div>

      <div class="detail-item">
        <span class="title">上课信息</span>
        <span class="content">
          <Arranges :arranges="course.arrangement" />
        </span>
      </div>
      <div class="detail-item">
        <span class="title">课程介绍</span>
        <span class="content">{{course.desc}}</span>
      </div>
      <div class="detail-item">
        <span class="title">申请理由</span>
        <span class="content">{{course.reply_content}}</span>
      </div>
    </div>
    <div class="apply-detail">
      <div class="title">报名详情（{{course.user_list?course.user_list.length:0}}/{{course.max_num}}）</div>
      <div class="user-item" v-for="(user, index) in course.user_list" :key="index">
        <avatar :src="user.avatar" />
        <div class="user-info">
          <span class="name">{{user.name}}</span>
          <span class="time">{{user.created_at}}</span>
        </div>
        <div class="major-in">{{user.major}}</div>
      </div>
    </div>
  </div>
</template>
<script>
import { CourseApi } from "../common/api";
import { CourseMode } from "../common/enum";
import avatar from "../../teacher_oa_tasks/components/avatar";
import CourseStatus from "./status";
import Arranges from './arrange'

export default {
  name: "course-detail",
  components: {
    avatar,
    CourseStatus,
    Arranges
  },
  data() {
    return {
      course: {}
    };
  },
  watch: {
    "$attrs.courseid": {
      deep: true,
      immediate: true,
      handler: function(id) {
        CourseApi.excute(
          this.$attrs.mode === CourseMode.applying.status
            ? "applyinfo"
            : "info",
          {
            applyid: id
          }
        ).then(res => {
          this.course = res.data.data;
          //   this.course.user_list = [
          //     {
          //       name: "白光玺",
          //       avatar:
          //         "https://dss0.bdstatic.com/6Ox1bjeh1BF3odCf/it/u=743072963,848806922&fm=85&app=92&f=JPEG?w=121&h=75&s=7040D31D46E35F15B824B1CF0300E0A0",
          //       major: "电子技术应用",
          //       created_at: "2020-02-18"
          //     }
          //   ];
        });
      }
    }
  },
  created() {}
};
</script>
<style lang="scss" scoped>
.detail-container {
  flex: 1;
  overflow-y: auto;
  .detail-panel {
    flex: 1;
    background-color: #ffffff;
    border-radius: 4px;

    > .title {
      font-size: 18px;
      color: #4ea5fe;
      padding: 14px;

      .el-button {
        float: right;
      }
    }

    // .detail-item:first-child {
    // border-top: none;
    // }
    .detail-item {
      display: flex;
      // border-top: 1px solid #eaedf2;
      padding-left: 22px;
      font-size: 14px;
      padding: 12px 0;
      margin: 0 20px;

      .title {
        display: inline-block;
        width: 100px;
        color: #8a93a1;
      }

      .content {
        flex: 1;
        word-break: break-all;
        word-wrap: break-word;
        color: #414a5a;
        .arrangement {
          display: flex;
          .week {
            flex: 1;
          }
          .day {
            flex: 2;
          }
          .address {
            flex: 3;
          }
        }
      }
    }
  }
  .apply-detail {
    border-top: 1px solid #f2f2f3;
    .title {
      font-size: 18px;
      font-weight: bold;
      color: #313b4c;
      padding: 16px 20px 12px 20px;
    }
    .user-item {
      padding: 12px 20px;
      display: flex;
      border-bottom: 1px solid #f2f2f3;
      .avatar {
        flex: none;
      }
      .user-info {
        margin-left: 20px;
        flex: none;
        display: flex;
        flex-direction: column;
        .name {
          color: #313b4c;
          font-size: 16px;
        }
        .time {
          font-size: 12px;
          color: #d2d5de;
        }
      }
      .major-in {
        flex: 1;
        text-align: right;
        color: #313b4c;
        font-size: 16px;
      }
    }
    .user-item:last-child {
      border: none;
    }
  }
}
</style>