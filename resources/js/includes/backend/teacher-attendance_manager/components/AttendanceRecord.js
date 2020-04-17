import { Mixins } from "../Mixins";
import { _load_attendance,_load_all, catchErr } from "../api/index";
import Axios from "axios";
Vue.component("AttendanceRecord", {
  template: `
    <div class="attendance-record-container">
      <el-row :gutter="20">
        <el-col :span="13" style="height: 100%;
        background: #fff;">
          <div class="recordLeft">
            <div class="leftTitle">
              <img class="icon" src="/assets/img/yinxin/recordLeft.png">
              <span class="title1">教务处考勤组</span>
              <el-select v-model="selectDateType" placeholder="请选择" size="small">
                <el-option
                  v-for="item in options"
                  :key="item.value"
                  :label="item.value"
                  :value="item.label">
                </el-option>
              </el-select>
              <el-date-picker
                v-if="isDay"
                size="small"
                v-model="dateByDay"
                type="date"
                placeholder="选择日期">
              </el-date-picker>
              <el-date-picker
                v-else
                size="small"
                v-model="dateByMonth"
                type="month"
                placeholder="选择月份">
              </el-date-picker>
              <el-button type="primary" size="small" @click="getList" style="margin-left: 20px;">查询</el-button>
              <el-button type="primary" size="small">导出</el-button>
            </div>
            <div class="leftContent">
              <div class="itemsDiv">
                <span>上午</span>
                <div class="itemDiv">
                  <p class="itemTop">XXXX</p>
                  <p class="itemBtm">20</p>
                </div>
              </div>
              <div class="itemsDiv">
                <span>下午</span>
                <div class="itemDiv">
                  <p>XXXX</p>
                  <p>20</p>
                </div>
              </div>
              <div class="itemsDiv">
                <span>下班</span>
                <div class="itemDiv">
                  <p>XXXX</p>
                  <p>20</p>
                </div>
              </div>
            </div>
          </div>
        </el-col>
        <el-col :span="11">
          <div class="recordRight">
            <div class="leftTitle">
            <img class="icon" src="/assets/img/yinxin/recordRight.png">
              上午-按时上班</div>
            <el-table
              :data="data.data"
              v-loading="isTableLoading"
              :empty-text="isTableLoading? ' ':'暂无数据'"
              :style="{}"
              border
              :header-cell-style="{'background':'#F8F9FB','color':'#313B4C'}"
              >
              <el-table-column
                align="center"
                prop="title"
                label="序号"
                min-width="60">
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
                width="280">
              </el-table-column>
              <el-table-column
                align="center"
                prop="wifi_name"
                label="状态"
                min-width="100">
              </el-table-column>
            </el-table>
          </div>
        </el-col>
      </el-row>
    </div>`,
  mixins: [Mixins],
  data() {
    return {
      options: [
        {
          lable: '按日筛选',
          value: '0'
        },
        {
          lable: '按月筛选',
          value: '1'
        }
      ],
      selectDateType: '',
      dateByDay: '',
      dateByMonth: '',
      isDay: false
    };
  },
  created() {

  },
  watch: {
    selectDateType: {
      handler(val) {
        if (val === '0') {
          this.isDay = true
        } else {
          this.isDay = false
        }
      }
    }
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
