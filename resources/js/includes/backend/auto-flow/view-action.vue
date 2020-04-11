<template>
  <div class="showAction">
    <el-drawer title="审批详情" ref="showAction" :visible.sync="showAction">
      <div class="information" v-if="baseInfo.length > 0">
        <h4>基本信息</h4>
        <el-divider></el-divider>
        <div v-for="item in baseInfo" :key="item.value">
          <h5>
            <p>{{ item.name }}</p>
            <p>{{ item.value }}</p>
          </h5>
          <el-divider></el-divider>
        </div>
      </div>
      <div class="information" v-if="options.length > 0">
        <h4>表单信息</h4>
        <el-divider></el-divider>
        <div v-for="(item,index) in options" :key="index">
          <div v-if="item.type=='image'">
            <h5>
              <p>{{ item.title }}</p>
            </h5>
            <div class="imageBox" v-if="item.type=='image' && item.value != 'null'">
              <div
                v-for="(img,index) in item.value"
                :key="index"
                style="width: 32%;margin-bottom: 5px"
              >
                <img :src="img" alt class="image" />
              </div>
            </div>
          </div>
          <div v-else-if="item.type=='files'">
            <h5>
              <p>{{ item.title }}</p>
            </h5>
            <div v-if="item.type='files'">
              <div class="reason option" v-for="(file,index) in item.value" :key="index">
                <span>{{ file.file_name }}</span>
                <span>下载</span>
              </div>
            </div>
          </div>
          <h5 v-else>
            <p>{{ item.title }}</p>
            <p>{{ item.value == "null" ? '' : item.value }}</p>
          </h5>
          <el-divider></el-divider>
        </div>
      </div>
      <div class="information">
        <h4>
          <span>审批人</span>
          <span style="font-size: 14px; font-weight: 100;" v-if="autoProcessed == 1">自动同意</span>
        </h4>
        <div class="block" style="padding: 0 15px;">
          <el-timeline>
            <!-- key="0" -->
            <el-timeline-item key="0" icon="el-icon-circle-check" type="success" v-if="startInfo">
              <div style="display: flex;justify-content: space-between;align-items: center;">
                <img
                  :src="startInfo.avatar"
                  alt
                  style="width: 40px; height: 40px;border-radius: 50%;vertical-align: middle;"
                />
                <div style="flex: 1;margin-left: 20px;">
                  <p style="margin: 0;">{{ startInfo.name }}</p>
                  <p style="margin: 0;">{{ startInfoTime.substr(0, 16) }}</p>
                </div>
                <span
                  style="text-align: right;font-size: 13px;color: #4FA8FE;"
                  v-if="startInfo"
                >发起审批</span>
              </div>
            </el-timeline-item>
            <el-timeline-item key="0" icon="el-icon-refresh-left" v-if="cancelInfo.length > 0">
              <div style="display: flex;justify-content: space-between;align-items: center;">
                <img
                  :src="startInfo.avatar"
                  alt
                  style="width: 40px; height: 40px;border-radius: 50%;vertical-align: middle;"
                />
                <div style="flex: 1;margin-left: 20px;">
                  <p style="margin: 0;">{{ startInfo.name }}</p>
                  <p style="margin: 0;">{{ startInfoTime.substr(0, 16) }}</p>
                </div>
                <span
                  style="text-align: right;font-size: 13px;color: #ababab;"
                  v-if="cancelInfo.length > 0"
                >已撤回</span>
              </div>
            </el-timeline-item>
            <!-- key="0" -->
            <!-- shengyu -->
            
            <!-- shengyu -->
          </el-timeline>
        </div>
      </div>
      <div class="information">
        <h4>抄送人（{{copys.length}}人）</h4>
        <div class="sendBox">
          <figure v-for="item in copys" :key="item.user_id">
            <img :src="item.avatar" width="50" height="50" />
            <p>{{ item.name }}</p>
          </figure>
        </div>
      </div>
      <p class="infobtn" @click="tips = true" v-if="baseInfo.length > 0">撤销</p>
      <p class="infobtn" @click="dialogVisible = true" v-else>审批</p>
      <el-dialog title="审批" :visible.sync="dialogVisible" width="25%" center v-cloak>
        <el-input
          type="textarea"
          :rows="6"
          placeholder="请输入审批意见"
          v-model="textarea"
          maxlength="100"
        ></el-input>
        <span style="position: relative;top: -20px;left: 85%;">{{textarea.length}}/100</span>
        <span slot="footer" class="dialog-footer">
          <el-button style="border-radius: 40px;width: 80px;" @click="button(5)">拒 绝</el-button>
          <el-button style="border-radius: 40px;width: 80px;" type="primary" @click="button(3)">同 意</el-button>
        </span>
      </el-dialog>
      <el-dialog title="提示" :visible.sync="tips" width="30%" center v-cloak>
        <span style="padding: 20px 0;display: inline-block;">您确认撤销此申请吗?</span>
        <span slot="footer" class="dialog-footer">
          <el-button @click="tips = false">取 消</el-button>
          <el-button type="primary" @click="cancelAction">确 定</el-button>
        </span>
      </el-dialog>
    </el-drawer>
  </div>
</template>
<script>
import { Util } from "../../../common/utils";

export default {
  name: "ViewAction",
  data() {
    return {
      showAction: false,
      baseInfo: [], // 基本信息
      options: [], // 表单
      autoProcessed: "", // 自动同意
      startInfo: {}, // 发起人审批中
      startInfoTime: "", // 发起人时间
      cancelInfo: "", // 发起人已撤回
      handlers: [], // 审批人
      copys: [], // 抄送人
      showActionEditForm: "", // 审批按钮
      textarea: "", // 审批意见
      dialogVisible: false, // 审批
      tips: false, // 撤回
      user_flow_id: "",
      actionid: ""
    };
  },
  computed: {
    countLength() {
      return this.textarea.length >= 100;
    }
  },
  methods: {
    getAction(id) {
      this.user_flow_id = id;
      axios
        .post("/api/pipeline/flow/view-action", { user_flow_id: id })
        .then(res => {
          if (Util.isAjaxResOk(res)) {
            this.showAction = true;
            this.baseInfo = res.data.data.baseInfo;
            this.options = res.data.data.options;
            this.autoProcessed = res.data.data.autoProcessed;
            this.startInfo = res.data.data.startInfo;
            this.startInfoTime = res.data.data.startInfo.time;
            this.cancelInfo = res.data.data.cancelInfo;
            this.handlers = res.data.data.handlers;
            this.copys = res.data.data.copys;
            this.showActionEditForm = res.data.data.showActionEditForm;
            this.actionid = res.data.data.initData.actionid;
          }
        })
        .catch(err => {
          console.log(err);
        });
    },
    // 审批详情关闭按钮
    handleClose(done) {
      done();
    },
    // 审批按钮
    button(result) {
      if (result === 5 && this.textarea === "") {
        this.$message.info("审批意见不能为空");
        return;
      } else {
        const action = {
          id: this.actionid, //action.id
          result: result, //3=同意 5=驳回
          content: this.textarea, //审批意见
          urgent: false //是否加急
        };
        axios
          .post("/api/pipeline/flow/process", { action: action })
          .then(res => {
            if (Util.isAjaxResOk(res)) {
              this.dialogVisible = false;
            } else {
              this.$message.info(res.data.message);
              this.dialogVisible = false;
            }
          })
          .catch(err => {
            console.log(err);
          });
      }
    },
    // 撤销
    cancelAction() {
      axios
        .post("/api/pipeline/flow/cancel-action", {
          user_flow_id: this.user_flow_id
        })
        .then(res => {
          if (Util.isAjaxResOk(res)) {
            this.tips = false;
          } else {
            this.$message.info(res.data.message);
            this.tips = false;
          }
        })
        .catch(err => {
          console.log(err);
        });
    }
  }
};
</script>
<style lang="scss">
.showAction {
  .el-drawer {
    min-width: 400px;
    max-width: 600px;
    width: 50% !important;

    .el-drawer__header {
      color: #333333;
      font-weight: bold;
      padding: 12px !important;
      font-size: 18px;
      border-bottom: 1px solid #eaedf2;
      margin-bottom: 0;
    }

    .el-drawer__body {
      height: 100%;
      overflow: auto;
      overflow-x: hidden;
    }
  }
  .information {
    h4 {
      font-size: 16px;
      font-family: PingFangSC-Medium, PingFang SC;
      font-weight: 500;
      color: #475b6d;
      padding: 0 20px;
    }
    h5 {
      display: flex;
      font-weight: 500;
      padding: 0 20px;
      font-size: 14px;
      p:first-child {
        flex: 1;
        color: #8a93a1;
      }
      p:nth-child(2) {
        flex: 2;
        color: #414a5a;
        text-align: right;
      }
    }
    .reason {
      font-size: 14px;
      font-weight: 400;
      color: rgba(51, 51, 51, 1);
      padding: 0 15px 10px;
    }
    .option {
      width: 93%;
      height: 50px;
      line-height: 50px;
      background: rgba(3, 133, 255, 0.05);
      margin: 0 auto;
      margin-bottom: 10px;
      color: #333333;
      display: flex;
      justify-content: space-between;
      span:first-child {
        flex: 1;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
      }
      span:last-child {
        cursor: pointer;
        color: #4ea5fe !important;
      }
    }
    .el-divider--horizontal {
      margin: 0;
    }
    .imageBox {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      padding: 0 20px 8px;
      .image {
        width: 100px;
        height: 100px;
      }
    }
    .sendBox {
      display: flex;
      padding: 0 10px 10px;
      figure {
        margin: 0;
        padding: 0 10px;
        img {
          border-radius: 50%;
        }
        p {
          font-size: 14px;
          text-align: center;
        }
      }
    }
    .el-timeline-item {
      padding-bottom: 10px;
    }
  }
  .el-dialog {
    position: absolute;
    right: 7%;
  }
  .infobtn {
    width: 50%;
    text-align: center;
    line-height: 47px;
    font-size: 17px;
    color: #fff;
    cursor: pointer;
    margin: 0 auto 30px;
    background-image: url("../../../../../public/assets/img/bgImg.png");
    background-size: 100%;
    background-repeat: no-repeat;
  }
}
</style>