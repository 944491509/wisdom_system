import { Mixins } from "../Mixins";
import { _load_attendance,_load_all, catchErr } from "../api/index";
import Axios from "axios";
Vue.component("AttendanceRecord", {
  template: `
    <div class="attendance-record-container">
      <el-row :gutter="20">
        <el-col :span="12">
          <div class="recordLeft">
            <div class="leftTitle">
              <span>教务处考勤组</span>
              <el-select v-model="select1" placeholder="请选择" size="small">
                <el-option
                  v-for="item in options1"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
              <el-date-picker
                v-if="isDay"
                size="small"
                v-model="value1"
                type="date"
                placeholder="选择日期">
              </el-date-picker>
              <el-date-picker
                v-else
                size="small"
                v-model="value2"
                type="month"
                placeholder="选择月份">
              </el-date-picker>
              <el-button type="primary" size="small" @click="getList">查询</el-button>
              <el-button type="primary" size="small">导出</el-button>
            </div>
            <div class="leftContent">
              <div>
                <span>上午</span>
                <div>
                  <span></span>
                  <span></span>
                </div>
              </div>
              <div>
                <span>下午</span>
                <div>
                  <span></span>
                  <span></span>
                </div>
              </div>
              <div>
                <span>下班</span>
                <div>
                  <span></span>
                  <span></span>
                </div>
              </div>
            </div>
          </div>
        </el-col>
        <el-col :span="12">
          <div class="recordRight">
            <div class="leftTitle">上午-按时上班</div>
            <el-table
              :data="data.data"
              v-loading="isTableLoading"
              :empty-text="isTableLoading? ' ':'暂无数据'"
              :style="{'width': '100%','min-height':minHeight + 'px'}"
              border
              :header-cell-style="{'background':'#F8F9FB','color':'#313B4C'}"
              >
              <el-table-column
                align="center"
                prop="title"
                label="序号"
                min-width="180">
              </el-table-column>
              <el-table-column
                align="center"
                prop="members"
                label="姓名"
                width="180">
              </el-table-column>
              <el-table-column
                align="center"
                prop="using_afternoon"
                label="打卡时间"
                width="180">
              </el-table-column>
              <el-table-column
                align="center"
                prop="wifi_name"
                label="状态"
                min-width="120">
              </el-table-column>
            </el-table>
          </div>
        </el-col>
      </el-row>
    </div>`,
  mixins: [Mixins],
  data() {
    return {
    };
  },
  created() {

  },
  methods: {
    getList() {
      let params = {
        day: '',
        groupid: ''
      }
      axios.post('/Oa/attendance/getManageDayCount', params).then(res => {
        console.log(res)
      })
    },
    _record(item) {},
  }
});
