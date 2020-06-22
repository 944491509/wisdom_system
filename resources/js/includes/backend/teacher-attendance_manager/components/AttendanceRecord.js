import { Mixins } from "../Mixins";
import tableToExcel from '../js/tableToExcel.js';
import { _load_clockins_daycount, _load_clockins_monthcount, catchErr } from "../api/index";
Vue.component("AttendanceRecord", {
  template: `
    <div class="attendance-record-container">
      <el-row :gutter="20">
        <el-col :span="11">
          <div class="recordLeft">
            <div class="leftTitle">
              <img class="icon" src="/assets/img/yinxin/recordLeft.png">
              <span class="title1">{{groupTitle}}</span>
              <el-select v-model="selectDateType" placeholder="请选择" size="small" @change="getList">
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
              <div class="text">上午</div>
              <div class="itemsDiv">
                <div class="itemDiv border1" @click="clickItem(resultDate.morning.ok.list, '上午-按时上班')">
                  <p class="itemTop color1">按时上班</p>
                  <p class="itemBtm">{{resultDate.morning ? resultDate.morning.ok.count :0}}<span v-if="!resultDate.morning.ok.users">人</span><span v-if="resultDate.morning.ok.users">次/{{resultDate.morning.ok.users ? resultDate.morning.ok.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border2" @click="clickItem(resultDate.morning.late.list, '上午-迟到')">
                  <p class="itemTop color2">迟到</p>
                  <p class="itemBtm">{{resultDate.morning ? resultDate.morning.late.count :0}}<span v-if="!resultDate.morning.late.users">人</span><span v-if="resultDate.morning.late.users">次/{{resultDate.morning.late.users ? resultDate.morning.late.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border3" @click="clickItem(resultDate.morning.later.list, '上午-严重迟到')">
                  <p class="itemTop color3">严重迟到</p>
                  <p class="itemBtm">{{resultDate.morning ? resultDate.morning.later.count :0}}<span v-if="!resultDate.morning.later.users">人</span><span v-if="resultDate.morning.later.users">次/{{resultDate.morning.later.users ? resultDate.morning.later.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border4" @click="clickItem(resultDate.morning_end.ok.list, '上午-按时下班')" v-if="isMor">
                  <p class="itemTop color4">按时下班</p>
                  <p class="itemBtm">{{resultDate.morning_end ? resultDate.morning_end.ok.count :0}}<span v-if="!resultDate.morning_end.ok.users">人</span><span v-if="resultDate.morning_end.ok.users">次/{{resultDate.morning_end.ok.users ? resultDate.morning_end.ok.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border5" @click="clickItem(resultDate.morning_end.early.list, '上午-早退')" v-if="isMor">
                  <p class="itemTop color5">早退</p>
                  <p class="itemBtm">{{resultDate.morning_end ? resultDate.morning_end.early.count :0}}<span v-if="!resultDate.morning_end.early.users">人</span><span v-if="resultDate.morning_end.early.users">次/{{resultDate.morning_end.early.users ? resultDate.morning_end.early.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border6" @click="clickItem(resultDate.morning.not.list, '上午-未打卡')">
                  <p class="itemTop color6">未打卡</p>
                  <p class="itemBtm">{{resultDate.morning ? resultDate.morning.not.count :0}}<span v-if="!resultDate.morning.not.users">人</span><span v-if="resultDate.morning.not.users">次/{{resultDate.morning.not.users ? resultDate.morning.not.users.length : 0}}人</span></p>
                </div>
              </div>
              <div class="text">下午</div>
              <div class="itemsDiv">
                <div class="itemDiv border1" @click="clickItem(resultDate.afternoon.ok.list, '下午-按时上班')" v-if="isAft">
                  <p class="itemTop color1">按时上班</p>
                  <p class="itemBtm">{{resultDate.afternoon ? resultDate.afternoon.ok.count :0}}<span v-if="!resultDate.afternoon.ok.users">人</span><span v-if="resultDate.afternoon.ok.users">次/{{resultDate.afternoon.ok.users ? resultDate.afternoon.ok.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border2" @click="clickItem(resultDate.afternoon.late.list, '下午-迟到')" v-if="isAft">
                  <p class="itemTop color2">迟到</p>
                  <p class="itemBtm">{{resultDate.afternoon ? resultDate.afternoon.late.count :0}}<span v-if="!resultDate.afternoon.late.users">人</span><span v-if="resultDate.afternoon.late.users">次/{{resultDate.afternoon.late.users ? resultDate.afternoon.late.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border3" @click="clickItem(resultDate.afternoon.later.list, '下午-严重迟到')" v-if="isAft">
                  <p class="itemTop color3">严重迟到</p>
                  <p class="itemBtm">{{resultDate.afternoon ? resultDate.afternoon.later.count :0}}<span v-if="!resultDate.afternoon.later.users">人</span><span v-if="resultDate.afternoon.later.users">次/{{resultDate.afternoon.later.users ? resultDate.afternoon.later.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border4" @click="clickItem(resultDate.evening.ok.list, '下午-按时下班')">
                  <p class="itemTop color4">按时下班</p>
                  <p class="itemBtm">{{resultDate.evening ? resultDate.evening.ok.count :0}}<span v-if="!resultDate.evening.ok.users">人</span><span v-if="resultDate.evening.ok.users">次/{{resultDate.evening.ok.users ? resultDate.evening.ok.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border5" @click="clickItem(resultDate.evening.early.list, '下午-早退')">
                  <p class="itemTop color5">早退</p>
                  <p class="itemBtm">{{resultDate.evening ? resultDate.evening.early.count :0}}<span v-if="!resultDate.evening.early.users">人</span><span v-if="resultDate.evening.early.users">次/{{resultDate.evening.early.users ? resultDate.evening.early.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border6" @click="clickItem(resultDate.afternoon.not.list, '下午-未打卡')">
                  <p class="itemTop color6">下午未打卡</p>
                  <p class="itemBtm">{{resultDate.afternoon ? resultDate.afternoon.not.count :0}}<span v-if="!resultDate.afternoon.not.users">人</span></span><span v-if="resultDate.afternoon.not.users">次/{{resultDate.afternoon.not.users ? resultDate.afternoon.not.users.length : 0}}人</span></p>
                </div>
              </div>
              <div class="text">其他</div>
              <div class="itemsDiv">
                <div class="itemDiv border7" @click="clickItem(resultDate.other.list, '其他-请假')">
                  <p class="itemTop color7">请假</p>
                  <p class="itemBtm">{{resultDate ? resultDate.other.leave.count :0}}<span v-if="!resultDate.other.leave.users">人</span><span v-if="resultDate.other.leave.users">次/{{resultDate.other.leave.users ? resultDate.other.leave.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border8" @click="clickItem(resultDate.other.travel.list, '其他-出差')">
                  <p class="itemTop color8">出差</p>
                  <p class="itemBtm">{{resultDate ? resultDate.other.travel.count :0}}<span v-if="!resultDate.other.travel.users">人</span><span v-if="resultDate.other.travel.users">次/{{resultDate.other.travel.users ? resultDate.other.travel.users.length : 0}}人</span></p>
                </div>
                <div class="itemDiv border9" @click="clickItem(resultDate.other.away.list, '其他-外出')">
                  <p class="itemTop color9">外出</p>
                  <p class="itemBtm">{{resultDate ? resultDate.other.away.count :0}}<span v-if="!resultDate.other.away.users">人</span><span v-if="resultDate.other.away.users">次/{{resultDate.other.away.users ? resultDate.other.away.users.length : 0}}人</span></p>
                </div>
              </div>
            </div>
          </div>
        </el-col>
        <el-col :span="13" v-if="isShowTable">
          <div class="recordRight">
            <div class="leftTitle">
              <img class="icon" src="/assets/img/yinxin/recordRight.png">
                {{statusText}}
              <el-button type="primary" size="small" @click="getExcel3" style="float: right">导出</el-button>
            </div>
            <el-table
              :data="tableList"
              :style="{}"
              height="519"
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
                v-if="showDate"
                align="center"
                prop="time"
                :label="dateLabel"
                width="280">
              </el-table-column>
              <el-table-column
                align="center"
                prop="banci"
                label="打卡班次"
                width="280">
              </el-table-column>
              <el-table-column
                v-if="showDate"
                align="center"
                prop="isRecord"
                label="状态"
                min-width="100">
              </el-table-column>
            </el-table>
          </div>
        </el-col>
      </el-row>
      <div style="position: absolute;bottom: 33px;left: 50%;">
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
      resultDate:{},
      dateByMonth: '',
      isDay: false,
      tableList: [],
      isShowTable: false,
      isShow: false,
      type: '',
      isMor: false,
      isAft: false,
      statusText: ''
    };
  },
  computed: {
    showDate() {
      console.log('showDate', this.statusText, this.statusText.indexOf("未打卡") != -1)
      return this.statusText.indexOf("未打卡") == -1
    },
    dateLabel() {
      console.log('showDate', this.statusText, this.statusText.indexOf("其他"))
      return this.statusText.indexOf("其他") != -1 ? '日期' : '打卡时间'
    }
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
      console.log('AAAA',this.selectDateType)
      let params = {}
      if (this.selectDateType == 0) {
        console.log('ssss')
        params = {
          attendance_id: this.attendance_id,
          day: this.dateByDay
        }
        const [err, res] = await catchErr(_load_clockins_daycount(params));
        this.resultDate = res.info
        this.isMor = res.using_morning
        this.isAft = res.using_afternoon
        this.isShow = true
        console.log(this.resultDate)
      } else if (this.selectDateType == 1) {
        console.log('xxxx',this.dateByDay)
        params = {
          attendance_id: this.attendance_id,
          month: this.dateByDay
        }
        console.log(params)
        const [err, res] = await catchErr(_load_clockins_monthcount(params));
        this.resultDate = res.info
        this.isShow = true
        console.log(this.resultDate)
      } else {
        this.$message.error('请选择日期或月份');
      }
    },
    cancel() {
      this.SETOPTIONS({ isShowRecord: false });
    },
    clickItem(list, title) {
      console.log(list)
      this.statusText = title
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
        e.banci = this.statusText.split("-")[0]
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
          return '按时打卡'
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
      let data = JSON.parse(JSON.stringify(this.resultDate))
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
    async getExcel() {
      // let search = this.search;
      console.log(this.resultDate)
      console.log("开始下载")
      // let res = await this.$parent.loadTextbooks(1)
      // let courses = res || [];
      // if (!courses.length) {
      //   this.$message({
      //     message: '下载失败！无数据可下载',
      //     type: 'warning'
      //   });
      //   return;
      // }
      tableToExcel(
        [
          {
            name: "时间",
            formatter: item => this.dateByDay
          },
          {
            name: "按时上班",
            formatter: item => (item.morning ? item.morning.ok.count :0 )+" " + (!item.morning.ok.users ? '人':  ( '次/'+ (item.morning.ok.users ? item.morning.ok.users.length : 0)+'人'))
          },
          {
            name: "迟到",
            formatter: item => (item.morning ? item.morning.late.count :0 )+" " + (!item.morning.late.users ? '人':  ( '次/'+ (item.morning.late.users ? item.morning.late.users.length : 0)+'人'))
          },
          {
            name: "严重迟到",
            formatter: item => (item.morning ? item.morning.later.count :0 )+" " + (!item.morning.later.users ? '人':  ( '次/'+ (item.morning.later.users ? item.morning.later.users.length : 0)+'人'))
          },
          {
            name: "按时下班",
            formatter: item => (item.morning_end ? item.morning_end.ok.count :0 )+" " + (!item.morning_end.ok.users ? '人':  ( '次/'+ (item.morning_end.ok.users ? item.morning_end.ok.users.length : 0)+'人'))
          },
          {
            name: "早退",
            formatter: item => (item.morning_end ? item.morning_end.early.count :0 )+" " + (!item.morning_end.early.users ? '人':  ( '次/'+ (item.morning_end.early.users ? item.morning_end.early.users.length : 0)+'人'))
          },
          {
            name: "未打卡",
            formatter: item => (item.morning ? item.morning.not.count :0 )+" " + (!item.morning.not.users ? '人':  ( '次/'+ (item.morning.not.users ? item.morning.not.users.length : 0)+'人'))
          },

          {
            name: "按时上班",
            formatter: item => (item.afternoon ? item.afternoon.ok.count :0 )+" " + (!item.afternoon.ok.users ? '人':  ( '次/'+ (item.afternoon.ok.users ? item.afternoon.ok.users.length : 0)+'人'))
          },
          {
            name: "迟到",
            formatter: item => (item.afternoon ? item.afternoon.late.count :0 )+" " + (!item.afternoon.late.users ? '人':  ( '次/'+ (item.afternoon.late.users ? item.afternoon.late.users.length : 0)+'人'))
          },
          {
            name: "严重迟到",
            formatter: item => (item.afternoon ? item.afternoon.later.count :0 )+" " + (!item.afternoon.later.users ? '人':  ( '次/'+ (item.afternoon.later.users ? item.afternoon.later.users.length : 0)+'人'))
          },
          {
            name: "按时下班",
            formatter: item => (item.evening ? item.evening.ok.count :0 )+" " + (!item.evening.ok.users ? '人':  ( '次/'+ (item.evening.ok.users ? item.evening.ok.users.length : 0)+'人'))
          },
          {
            name: "早退",
            formatter: item => (item.evening ? item.evening.early.count :0 )+" " + (!item.evening.early.users ? '人':  ( '次/'+ (item.evening.early.users ? item.evening.early.users.length : 0)+'人'))
          },
          {
            name: "未打卡",
            formatter: item => (item.afternoon ? item.afternoon.not.count :0 )+" " + (!item.afternoon.not.users ? '人':  ( '次/'+ (item.afternoon.not.users ? item.afternoon.not.users.length : 0)+'人'))
          },

          { 
            name: "请假",
            formatter: item => (item.other ? item.other.leave.count :0 )+" " + (!item.other.leave.users ? '人':  ( '次/'+ (item.other.leave.users ? item.other.leave.users.length : 0)+'人'))
          },
          {
            name: "出差",
            formatter: item => (item.other ? item.other.travel.count :0 )+" " + (!item.other.travel.users ? '人':  ( '次/'+ (item.other.travel.users ? item.other.travel.users.length : 0)+'人'))
          },
          {
            name: "外出",
            formatter: item => (item.other ? item.other.away.count :0 )+" " + (!item.other.away.users ? '人':  ( '次/'+ (item.other.away.users ? item.other.away.users.length : 0)+'人'))
          },
        ],
        [this.resultDate],
        "考勤管理" + this.dateByDay,
        [
          {
            name:'',
            colspan:1
          },
          {
            name:'上午',
            colspan:6
          },
          {
            name:'下午',
            colspan:6
          },
          {
            name:'其他',
            colspan:3
          }
        ]
      );
    },
    async getExcel3() {
      // let search = this.search;
      console.log("开始下载")
      // let res = await this.$parent.loadTextbooks(1)
      // let courses = res || [];
      if (!this.tableList.length) {
        this.$message({
          message: '下载失败！无数据可下载',
          type: 'warning'
        });
        return;
      }
      tableToExcel(
        [
          {
            name: "姓名",
            formatter: item => `${item.name}`
          },
          {
            name: "日期",
            formatter: item => this.dateByDay
          },
          {
            name: "时间段",
            formatter: item => item.banci
          },
          {
            name: "类型",
            formatter: item => `${item.isRecord}`
          },
          {
            name: "打卡时间",
            formatter: item => `${item.time}`
          },
          {
            name: "打卡状态",
            formatter: item => this.dakaSwitch(item.status)
          },
        ],
        this.tableList,
        "考勤记录" + this.dateByDay
      );
    }
  }
});
