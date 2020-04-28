import { Mixins } from "../Mixins";
import { _load_clockins_daycount, _load_clockins_monthcount, catchErr } from "../api/index";
Vue.component("AttendanceRecord", {
  template: `
    <div class="attendance-record-container">
      <el-row :gutter="20">
        <el-col :span="13">
          <div class="recordLeft">
            <div class="leftTitle">
              <img class="icon" src="/assets/img/yinxin/recordLeft.png">
              <span class="title1">教务处考勤组</span>
              <el-select v-model="selectDateType" placeholder="请选择" size="small">
                <el-option
                  v-for="item in options"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
              <el-date-picker
                v-if="isDay"
                size="small"
                v-model="dateByDay"
                value-format="yyyy-MM-dd"
                format="yyyy-MM-dd"
                type="date"
                placeholder="选择日期">
              </el-date-picker>
              <el-date-picker
                v-else
                size="small"
                v-model="dateByDay"
                value-format="yyyy-MM"
                type="month"
                placeholder="选择月份">
              </el-date-picker>
              <el-button type="primary" size="small" @click="getList" style="margin-left: 20px;">查询</el-button>
              <el-button type="primary" size="small" @click="getExcel">导出</el-button>
            </div>
            <div class="leftContent" v-if="isShow">
              <div class="itemsDiv">
                <span>上午</span>
                <div class="itemDiv border1" @click="clickItem(resDate.morning.ok.list)">
                  <p class="itemTop">按时打卡</p>
                  <p class="itemBtm">{{resDate.morning ? resDate.morning.ok.count :0}}<span v-if="resDate.morning.ok.users">次/{{resDate.morning.ok.users ? resDate.morning.ok.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border2" @click="clickItem(resDate.morning.late.list)">
                  <p class="itemTop">迟到</p>
                  <p class="itemBtm">{{resDate.morning ? resDate.morning.late.count :0}}<span v-if="resDate.morning.late.users">次/{{resDate.morning.late.users ? resDate.morning.late.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border3" @click="clickItem(resDate.morning.later.list)">
                  <p class="itemTop">严重迟到</p>
                  <p class="itemBtm">{{resDate.morning ? resDate.morning.later.count :0}}<span v-if="resDate.morning.later.users">次/{{resDate.morning.later.users ? resDate.morning.later.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border4" @click="clickItem(resDate.morning.not.list)">
                  <p class="itemTop">未打卡</p>
                  <p class="itemBtm">{{resDate.morning ? resDate.morning.not.count :0}}<span v-if="resDate.morning.not.users">次/{{resDate.morning.not.users ? resDate.morning.not.users.length : 0}}人</span></p>
                </div>
              </div>
              <div class="itemsDiv">
                <span>下午</span>
                <div class="itemDiv border1" @click="clickItem(resDate.afternoon.ok.list)">
                  <p class="itemTop">按时打卡</p>
                  <p class="itemBtm">{{resDate.afternoon ? resDate.afternoon.ok.count :0}}<span v-if="resDate.afternoon.ok.users">次/{{resDate.afternoon.ok.users ? resDate.afternoon.ok.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border2" @click="clickItem(resDate.afternoon.late.list)">
                  <p class="itemTop">迟到</p>
                  <p class="itemBtm">{{resDate.afternoon ? resDate.afternoon.late.count :0}}<span v-if="resDate.afternoon.late.users">次/{{resDate.afternoon.late.users ? resDate.afternoon.late.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border3" @click="clickItem(resDate.afternoon.later.list)">
                  <p class="itemTop">严重迟到</p>
                  <p class="itemBtm">{{resDate.afternoon ? resDate.afternoon.later.count :0}}<span v-if="resDate.afternoon.later.users">次/{{resDate.afternoon.later.users ? resDate.afternoon.later.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border4" @click="clickItem(resDate.afternoon.not.list)">
                  <p class="itemTop">未打卡</p>
                  <p class="itemBtm">{{resDate.afternoon ? resDate.afternoon.not.count :0}}<span v-if="resDate.afternoon.not.users">次/{{resDate.afternoon.not.users ? resDate.afternoon.not.users.length : 0}}人</span></p>
                </div>
              </div>
              <div class="itemsDiv">
                <span>下班</span>
                <div class="itemDiv border1" @click="clickItem(resDate.evening.ok.list)">
                  <p class="itemTop">按时打卡</p>
                  <p class="itemBtm">{{resDate.evening ? resDate.evening.ok.count :0}}<span v-if="resDate.evening.ok.users">次/{{resDate.evening.ok.users ? resDate.evening.ok.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border3" @click="clickItem(resDate.evening.early.list)">
                  <p class="itemTop">早退</p>
                  <p class="itemBtm">{{resDate.evening ? resDate.evening.early.count :0}}<span v-if="resDate.evening.early.users">次/{{resDate.evening.early.users ? resDate.evening.early.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border4" @click="clickItem(resDate.evening.not.list)">
                  <p class="itemTop">未打卡</p>
                  <p class="itemBtm">{{resDate.evening ? resDate.evening.not.count :0}}<span v-if="resDate.evening.not.users">次/{{resDate.evening.not.users ? resDate.evening.not.users.length : 0}}人</span></p>
                </div>
              </div>
            </div>
          </div>
        </el-col>
        <el-col :span="11" v-if="isShowTable">
          <div class="recordRight">
            <div class="leftTitle">
            <img class="icon" src="/assets/img/yinxin/recordRight.png">
              上午-按时上班</div>
            <el-table
              :data="tableList"
              :style="{}"
              height="470"
              border
              :header-cell-style="{'background':'#F8F9FB','color':'#313B4C'}"
              >
              <el-table-column
                align="center"
                prop="userid"
                label="序号"
                min-width="60">
              </el-table-column>
              <el-table-column
                align="center"
                prop="name"
                label="姓名"
                width="180">
              </el-table-column>
              <el-table-column
                align="center"
                prop="time"
                label="打卡时间"
                width="280">
              </el-table-column>
              <el-table-column
                align="center"
                prop="isRecord"
                label="状态"
                min-width="100">
              </el-table-column>
            </el-table>
          </div>
        </el-col>
      </el-row>
      <div style="position: absolute;bottom: 72px;left: 50%;">
        <el-button type="primary" @click="cancel">取消</el-button>
      </div>
    </div>`,
  mixins: [Mixins],
  data() {
    return {
      options: [
        {
          label: '按日筛选',
          value: '0'
        },
        {
          label: '按月筛选',
          value: '1'
        }
      ],
      selectDateType: '',
      dateByDay: '',
      dateByMonth: '',
      isDay: false,
      resDate: {},
      tableList: [],
      isShowTable: false,
      isShow: false,
      type: ''
    };
  },
  mounted() {
    let date = new Date()
    let year = date.getFullYear()
    let month = date.getMonth().toString().length == 1 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
    let day = date.getDate()
    this.isDay = true
    this.dateByDay = year + '-' + month + '-' + day
    console.log(this.dateByDay)
    this.selectDateType = '0'
    this.getList()
  },
  watch: {
    selectDateType: {
      handler(val) {
        let date = new Date()
        let year = date.getFullYear()
        let month = date.getMonth().toString().length == 1 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
        let day = date.getDate()
        if (val === '0') {
          this.dateByDay = year + '-' + month + '-' + day
          this.isDay = true
        } else {
          this.dateByDay = year + '-' + month 
          this.isDay = false
        }
      }
    }
  },
  methods: {
    async getList() {
      let params = {}
      if (this.dateByDay.split("-").length == 3) {
        console.log('ssss')
        params = {
          attendance_id: this.attendance_id,
          day: this.dateByDay
        }
        const [err, res] = await catchErr(_load_clockins_daycount(params));
        this.resDate = res.info
        this.isShow = true
        console.log(this.resDate)
      } else if (this.dateByDay.split("-").length == 2) {
        console.log('xxxx')
        params = {
          attendance_id: this.attendance_id,
          month: this.dateByDay
        }
        const [err, res] = await catchErr(_load_clockins_monthcount(params));
        this.resDate = res.info
        this.isShow = true
        console.log(this.resDate)
      } else {
        this.$message.error('请选择日期或月份');
      }
    },
    cancel() {
      this.SETOPTIONS({ isShowRecord: false });
    },
    clickItem(list) {
      console.log(list)
      this.tableList = list.map(e => {
        if (e.status == 0) {
          e.isRecord = '未打卡'
        } else if (e.status == 1) {
          e.isRecord = '正常'
        } else if (e.status == 2) {
          e.isRecord = '迟到'
        } else if (e.status == 3) {
          e.isRecord = '严重迟到'
        } else if (e.status == 4) {
          e.isRecord = '早退'
        }
        return e
      })

      this.isShowTable = true
    },
    typeSwitch(val) {
      switch (val) {
        case 'morning': 
          return '上午'
          break
        case 'afternoon': 
          return '下午'
          break
        case 'evening': 
          return '下班'
          break
        default:
          break
      }
    },
    dakaSwitch(val) {
      switch (val) {
        case 0: 
          return '未打卡'
          break
        case 1: 
          return '正常'
          break
        case 2: 
          return '迟到'
          break
        case 3: 
          return '严重迟到'
          break
        case 4: 
          return '早退'
        default:
          break
      }
    },
    formatList() {
      let list = []
      let data = JSON.parse(JSON.stringify(this.resDate))
      if (this.dateByDay.split("-").length == 3) {
        Object.entries(data).map(([key, value], i) => {
          Object.entries(value).map(([k, v]) => {
            v.list.map(e => {
              e.date = this.dateByDay,
              e.type = this.typeSwitch(key),
              e.dakaStatus = this.dakaSwitch(e.status)
            })
            list = list.concat(v.list)
          })
        })
        return list
      } else if (this.dateByDay.split("-").length == 2) {
        Object.entries(data).map(([key, value]) => {
          console.log(value)
          let O = {
            date: this.dateByDay,
            type: this.typeSwitch(key),
            okCount: value.ok.count,
            okTimes: value.ok.users.length,
            lateCount: value.late ? value.late.count : value.early.count,
            lateTimes: value.late ? value.late.users.length : value.early.users.length,
            laterCount: value.later ? value.later.count : 0,
            laterTimes: value.later ? value.later.users.length : 0,
            notCount: value.not.count,
            notTimes: value.not.users.length,
          }
          list.push(O)
        })
        console.log(list)
        return list
      }

    },

    getExcel(res) {
      require.ensure([], () => {
          const { export_json_to_excel } = require('../js/Export2Excel.js')
          let tHeader = []
          let filterVal = []
          if (this.dateByDay.split("-").length == 3) {
            tHeader = ['日期', '姓名', '打卡类型', '打卡时间', '打卡状态']
            filterVal = ['date', 'name', 'type', 'time', 'dakaStatus']
          } else if (this.dateByDay.split("-").length == 2) {
            tHeader = ['日期', '打卡类型', '按时打卡次数', '按时打卡人数', '迟到/早退次数', '迟到/早退人数', '严重迟到次数', '严重迟到人数', '未打卡次数', '未打卡人数']
            filterVal = ['date', 'type', 'okCount', 'okTimes', 'lateCount', 'lateTimes', 'laterCount', 'laterTimes', 'notCount', 'notTimes']
          }
          const data = this.formatJson(filterVal, this.formatList())
          let excelName = this.dateByDay + '考勤记录'
          export_json_to_excel(tHeader, data, excelName)
      },'ex2Excel/ex2Excel')
    }, 
    formatJson(filterVal, jsonData) {
        return jsonData.map(v => filterVal.map(j => v[j]))
    },
  }
});
