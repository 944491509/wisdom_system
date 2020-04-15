<template>
  <div class="message-detail-container">
    <detail :message="message" />
    <div class="ctrl-container">
      <el-button
        size="mini"
        @click="go('reply')"
        v-show="[MessageMode.unread.value,MessageMode.read.value].includes(message.type)"
      >
        <i class="iconfont icon-message"></i>
      </el-button>
      <el-button size="mini" @click="go('share')">
        <i class="iconfont icon-share"></i>
      </el-button>
      <el-button size="mini" @click="deleteMsg">
        <i class="iconfont icon-delete" style="font-size: 20px;"></i>
      </el-button>
    </div>
  </div>
</template>
<script>
import { MessageApi } from "../common/api";
import { Util } from "../../../../common/utils";
import { MessageMode } from "../common/enum";
import MemberSelect from "./setmember";
import Detail from "./detail-component";
import moment from "moment";

export default {
  name: "message-form",
  props: {
    id: {
      default: null
    }
  },
  components: {
    Detail
  },
  data() {
    return {
      message: {},
      MessageMode
    };
  },
  methods: {
    go(mode) {
      this.$emit("go", mode, this.message);
    },
    updateDetail(message) {
      this.message = message;
    },
    deleteMsg() {
      this.$confirm("确认删除信件?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning"
      }).then(() => {
        MessageApi.excute("updateTag", {
          id: this.message.id,
          type: 1
        }).then(res => {
          this.$emit('close', true)
        });
      });
    }
  }
};
</script>
<style lang="scss" scoped>
.message-detail-container {
  display: flex;
  flex-direction: column;
  height: 100%;
  .ctrl-container {
    box-shadow: 0 0 6px #cccccc;
    padding: 20px;
    z-index: 200;
    .el-button {
      border: none;
    }
  }
}
</style>
