<template>
    <div style="padding-bottom: 30px;">
        <h3 class="text-center mt-4">
            <el-button v-if=" subTitle !== '' " v-on:click="switchWeekViewHandler" type="text">{{ isWeekOdd ? '目前是单周课表, 点击切换为双周课表' : '目前是双周课表, 点击切换为单周课表' }}</el-button>&nbsp;
            课程表预览: {{ subTitle }}
        </h3>
        <el-divider></el-divider>
        <div class="timetable-wrap mb-4">
            <time-slots-column :time-slots="timeSlots" class="first-column"></time-slots-column>
            <div v-for="(rows, idx) in timetable" :key="idx" class="the-column">
                <timetable-column
                        :rows="rows"
                        :weekday="idx"
                        :as-manager="asManager"
                        v-on:create-new-for-current-column="createNewForCurrentColumnHandler"
                        v-on:edit-for-current-unit-column="editForCurrentUnitColumnHandler"
                        v-on:clone-for-current-unit-column="cloneForCurrentUnitColumnHandler"
                        v-on:create-special-case-column="createSpecialCaseColumnHandler"
                        v-on:show-special-cases-column="showSpecialCasesColumnHandler"
                        v-on:make-enquiry-column="makeEnquiryColumnHandler"
                ></timetable-column>
            </div>
        </div>
        <el-dialog title="克隆课程表项目" :visible.sync="cloneFormVisible">
            <el-form :model="cloned">
                <el-form-item label="哪一天">
                    <el-select v-model="cloned.weekday_index" style="width: 100%;">
                        <el-option :label="theWeekday"
                                   :value="(idx+1)"
                                   :key="theWeekday"
                                   v-for="(theWeekday, idx) in weekdays"></el-option>
                    </el-select>
                    <span class="help-text">说明: 指定本次安排是哪一天</span>
                </el-form-item>
                <el-form-item label="时间段">
                    <el-select v-model="cloned.time_slot_id" style="width: 100%;">
                        <el-option
                                :label="timeSlot.name"
                                :value="timeSlot.id"
                                :key="timeSlot.id"
                                v-for="timeSlot in timeSlots"
                        ></el-option>
                    </el-select>
                    <span class="help-text">说明: 指定本次安排是针对一天中的那个时段的</span>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="cancelCloneAction">取 消</el-button>
                <el-button type="primary" @click="confirmCloneAction">确 定</el-button>
            </div>
        </el-dialog>
        <el-dialog :title="'调课表单-'+subTitle" :visible.sync="specialCaseFormVisible" :close-on-click-modal="false">
            <timetable-item-special-form
                v-if="specialCaseFormVisible"
                :user-uuid="userUuid"
                :school-id="schoolId"
                :courses="coursesForSpecial"
                :specialTimeTableItem="specialCase"
                :timeTableItem = "timeTableItem"
                :to-be-replaced-item="toBeReplacedItem"
                :subtitle="subTitle"
                :special-case-cancelled="cancelSpecialCaseHandler"
                :special-case-confirmed="confirmSpecialCaseHandler"
            ></timetable-item-special-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="cancelSpecialCaseHandler">取 消</el-button>
                <el-button type="primary" @click="confirmSpecialCaseHandler">确 定</el-button>
            </div>
        </el-dialog>
        <el-dialog class="specialsListDialog" :visible.sync="specialsListVisible" :before-close="beforeSpecialListClose">
          <div slot="title"><i class="fa fa-exchange title-icon"></i> 调课记录表</div>
          <div class="tpItems" v-for="(special,index) in specials" :key="special.timetable_id">
            <div class="tpLeft">
              <div class="tpItem">
                <img class="icon-image" src="/assets/img/previewer/type.svg" />
                <span class="itemLeft first">调课类型:</span>
                <span class="temRight first">{{special.type}}</span>
              </div>
              <div class="tpItem">
                <i class="fa fa-clock-o icon"></i>
                <span class="itemLeft">开始时间:</span>
                <span class="temRight">{{special.start_time}}</span>
              </div>
              <div class="tpItem">
                <i class="fa fa-clock-o icon"></i>
                <span class="itemLeft">结束时间:</span>
                <span class="temRight">{{special.end_time}}</span>
              </div>
              <div class="tpItem">
                <i class="fa fa-clock-o icon"></i>
                <span class="itemLeft">实际开始时间:</span>
                <span class="temRight">{{special.practical_start_time}}</span>
              </div>
              <div class="tpItem">
                <i class="fa fa-user-circle-o icon"></i>
                <span class="itemLeft">操作人:</span>
                <span class="temRight">{{special.updated_by}}</span>
              </div>
            </div>
            <div class="tpRight">
              <div class="tpItem">
                <img class="icon-image" src="/assets/img/previewer/method.svg" />
                <span class="itemLeft first" >调课方式:</span>
                <span class="temRight first">{{special.initiative}}</span>
              </div>
              <div class="tpItem">
                <i class="fa fa-file-text-o icon"></i>
                <span class="itemLeft">课程名称:</span>
                <span class="temRight">{{special.course}}</span>
              </div>
              <div class="tpItem">
                <img class="icon-image" src="/assets/img/previewer/room.svg" />
                <span class="itemLeft">上课地点:</span>
                <span class="temRight">{{special.room}}</span>
              </div>
              <div class="tpItem">
                <img class="icon-image" src="/assets/img/previewer/teacher.svg" />
                <span class="itemLeft">授课老师:</span>
                <span class="temRight">{{special.teacher}}</span>
              </div>
              <div class="tpItem">
                <img class="icon-image" src="/assets/img/previewer/resource.svg" />
                <span class="itemLeft">课程来源:</span>
                <span class="temRight">{{special.course_source}}</span>
              </div>
            </div>
            <label class="delete-label"><i class="delete-icon fa fa-trash" @click="deleteSpecial(index,special)"></i></label>
          </div>
        </el-dialog>

        <el-dialog title="请求事宜表单" :visible.sync="makeEnquiryFormVisible">
            <general-enquiry-form
                :enquiry-form="enquiryForm"
            ></general-enquiry-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="makeEnquiryFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="makeEnquiryFormSubmitHandler">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import TimetableColumn from './TimetableColumn.vue';
    import TimeSlotsColumn from './TimeSlotsColumn.vue';
    import TimetableItemSpecialForm from './TimetableItemSpecialForm.vue';
    import GeneralEnquiryForm from '../misc/GeneralEnquiryForm';
    import { Constants } from '../../common/constants';
    import { Util } from '../../common/utils';

    export default {
        name: "TimetablePreviewer",
        components: {
            TimetableColumn, TimeSlotsColumn, TimetableItemSpecialForm,GeneralEnquiryForm
        },
        computed: {
            'isWeekOdd': function(){
                return this.weekType === Constants.WEEK_NUMBER_ODD;
            }
        },
        props: {
            // 传递来的表单数据
            timeTableItem: {
                type: Object,
                required: true,
            },
            timetable: {
                type: Array,
                required: true
            },
            asManager: { // 是否具备管理员的功能选项
                type: Boolean,
                required: false,
                default: false
            },
            timeSlots:{
                type: Array,
                required: true
            },
            subTitle: {
                type: String,
                required: false,
                default: '',
            },
            schoolId: {
                type: [Number, String],
                required: true
            },
            userUuid: {
                type: [Number, String],
                required: true
            },
            weekType: {    // 默认的为单周
                type: Number,
                required: false,
                default: Constants.WEEK_NUMBER_ODD
            }
        },
        data(){
            return {
                cloneFormVisible: false,
                specialCaseFormVisible: false,
                specialsListVisible: false,
                formLabelWidth: '80px',
                // 克隆表单用
                cloned: {
                    time_slot_id: '',
                    weekday_index: '',
                    from_unit_id: null,
                },
                weekdays:[],
                // 调课用
                specialCase: {
                    at_special_datetime: '',
                    to_special_datetime: '',
                    course_id: '',
                    teacher_id: '',
                    building_id: '',
                    room_id: '',
                    published: false,
                    to_replace: 0,
                    type: 0,
                    class_id: '',
                    week_id: '',
                },
                toBeReplacedItem: {},
                coursesForSpecial:[],
                // 显示调课的列表
                specials:[],
                anySpecialItemRemoved: false,
                // 请假等事宜
                makeEnquiryFormVisible: false,
                enquiryForm:{},
            }
        },
        created() {
            this.weekdays = Constants.WEEK_DAYS;
        },
        methods: {
            createNewForCurrentColumnHandler: function(payload){
                this.$emit('create-new-by-click',payload);
            },
            editForCurrentUnitColumnHandler: function (payload) {
                this.$emit('edit-unit-by-click',payload);
            },
            cloneForCurrentUnitColumnHandler: function (payload) {
                this.cloneFormVisible = true;
                this.cloned.from_unit_id = payload.unit.id;
                this.cloned.time_slot_id = payload.unit.time_slot_id;
                this.cloned.weekday_index = payload.unit.weekday_index;
            },
            cancelCloneAction: function(){
                this.cloneFormVisible = false;
                this.cloned.from_unit_id = null;
                this.cloned.time_slot_id = '';
                this.cloned.weekday_index = '';
            },
            confirmCloneAction: function () {
                // 保存克隆的项
                axios.post(
                    Constants.API.TIMETABLE.CLONE_ITEM,
                    {
                        item: {
                            id: this.cloned.from_unit_id,
                            time_slot_id: this.cloned.time_slot_id,
                            weekday_index: this.cloned.weekday_index
                        }
                    }
                ).then(res => {
                    if(Util.isAjaxResOk(res)){
                        this.cloneFormVisible = false;
                        this.$notify({
                            title: '成功',
                            message: '课程表项已经已经克隆成功!',
                            type: 'success',
                            position: 'bottom-right'
                        });
                        this.$emit('timetable-refresh',{});
                    }
                });
            },
            showSpecialCasesColumnHandler: function(payload){
                axios.post(
                    Constants.API.TIMETABLE.LOAD_SPECIAL_CASES,
                    {timetable_ids: payload}
                ).then(res => {
                    if (Util.isAjaxResOk(res)){
                        this.specialsListVisible = true;
                        this.anySpecialItemRemoved = false;
                        this.specials = res.data.data;
                    }
                });
            },
            // 创建调课记录
            createSpecialCaseColumnHandler: function (payload) {
                // 获取调课可能涉及到的课程列表
                axios.post(
                    Constants.API.LOAD_COURSES_BY_MAJOR,
                    {itemId: payload.unit.id, as: 'timetable-item-id'}
                ).then(res => {
                    if(Util.isAjaxResOk(res)){
                        this._resetSpecialForm(payload.unit.id); // 初始化调课表单数据
                        this.toBeReplacedItem = payload.unit; // 获取到被调课的项
                        this.coursesForSpecial = res.data.data.courses;
                        this.specialCaseFormVisible = true;
                    }
                });
            },
            _resetSpecialForm: function(id){
                this.specialCase = {
                    at_special_datetime: '',
                    to_special_datetime: '',
                    course_id: '',
                    teacher_id: '',
                    building_id: '',
                    room_id: '',
                    published: false,
                    to_replace: id,
                };
            },
            cancelSpecialCaseHandler: function(){
                this.specialCaseFormVisible = false;
                this._resetSpecialForm(null); // 重置调课表单数据
                this.toBeReplacedItem = {}; // 获取到被调课的项
            },
            confirmSpecialCaseHandler: function(e, affirm = 0){
              // console.log('AA',this.specialCase)
              // console.log('AA',this.userUuid)
              let params = {
                timetable_id: this.specialCase.to_replace,
                at_special_datetime: this.specialCase.at_special_datetime,
                to_special_datetime: this.specialCase.to_special_datetime,
                type: Number(this.specialCase.type || 0) + 1,
                affirm: affirm || 0,
                teacher_id:this.specialCase.teacher_id || '',
                building_id:this.specialCase.building_id || '',
                room_id:this.specialCase.room_id || '',
                weekday_index:this.specialCase.week_id || '',
                time_slot_id:this.specialCase.course_id || '',
                grade_id:this.specialCase.class_id || '',
              }
               axios.post( `/api/timetable/switchingCheck`, params).then(res=>{
                    if(Util.isAjaxResOk(res)){
                        if(res.data.code == 1000){
                            // 创建成功, 去刷新课程表的表单
                            this.$emit('timetable-refresh',{});
                            this.$notify({
                                title: '成功',
                                message: '调课操作成功, 正为您刷新课程表 ...',
                                type: 'success',
                                position: 'bottom-right'
                            });
                            this.specialCaseFormVisible = false;
                        }else{
                            if(params.type == 1){
                                this.$confirm(res.data.message, '提示', {
                                    confirmButtonText: '继续保存',
                                    cancelButtonText: '取消',
                                    type: 'warning'
                                }).then(() => {
                                    this.confirmSpecialCaseHandler(e,1)
                                }).catch(()=>{
                                    this.$notify.error({
                                        title: '提示',
                                        message: '已取消保存',
                                        position: 'bottom-right'
                                    });
                                })
                            }else{
                                this.$notify.error({
                                    title: '提示',
                                    message: res.data.message,
                                    position: 'bottom-right'
                                });
                            }

                        }
                    }else{
                        if(params.type == 1){
                          this.$confirm(res.data.message, '提示', {
                              confirmButtonText: '继续保存',
                              cancelButtonText: '取消',
                              type: 'warning'
                          }).then(() => {
                              this.confirmSpecialCaseHandler(e,1)
                          }).catch(()=>{
                              this.$notify.error({
                                  title: '提示',
                                  message: '已取消保存',
                                  position: 'bottom-right'
                              });
                          })
                      }else{
                          this.$notify.error({
                              title: '提示',
                              message: res.data.message,
                              position: 'bottom-right'
                          });
                      }
                    }
                })
                // axios.post(
                //     Constants.API.TIMETABLE.CREATE_SPECIAL_CASE,
                //     {specialCase: this.specialCase, user: this.userUuid}
                // ).then(res=>{
                //     if(Util.isAjaxResOk(res)){
                //         // 创建成功, 去刷新课程表的表单
                //         this.$emit('timetable-refresh',{});
                //         this.$notify({
                //             title: '成功',
                //             message: '调课操作成功, 正为您刷新课程表 ...',
                //             type: 'success',
                //             position: 'bottom-right'
                //         });
                //         this.specialCaseFormVisible = false;
                //     }else{
                //         this.$notify.error({
                //             title: '系统错误',
                //             message: '调课操作失败, 请稍候再试 ...',
                //             position: 'bottom-right'
                //         });
                //     }
                // })
            },
            // 发布调课信息
            handleSpecialCasePublish: function(idx, row){
                this.$confirm('您将发布此调课信息, 是否确认?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    axios.post(
                        Constants.API.TIMETABLE.PUBLISH_ITEM,{id: row.id, user: this.userUuid}
                    ).then(res=>{
                        if(Util.isAjaxResOk(res)){
                            this.$notify({
                                title: '成功',
                                message: '调课信息已经发布成功',
                                type: 'success',
                                position: 'bottom-right'
                            });
                            this.specials[idx].published = true;
                        }
                    });
                }).catch((e) => {
                    this.$notify.info({
                        title: '消息',
                        message: '发布操作已取消',
                        position: 'bottom-right'
                    });
                });
            },
            // 删除调课项
            deleteSpecial: function(idx, row){
                this.$confirm('此操作将永久删除该调课记录, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    axios.post(
                        Constants.API.TIMETABLE.DELETE_ITEM,{id: row.timetable_id, user: this.userUuid}
                    ).then(res=>{
                        if(Util.isAjaxResOk(res)){
                            this.$notify({
                                title: '成功',
                                message: '删除成功',
                                type: 'success',
                                position: 'bottom-right'
                            });
                            this.specials.splice(idx, 1);
                            this.anySpecialItemRemoved = true;
                        }else{
                          this.$notify({
                              title: '失败',
                              message: '删除失败',
                              type: 'error',
                              position: 'bottom-right'
                          });
                        }
                    });
                }).catch(() => {
                    this.$notify.info({
                        title: '消息',
                        message: '删除操作已取消',
                        position: 'bottom-right'
                    });
                });
            },
            // 当调课记录 modal 关闭时: 发布事件, 让课程表刷新
            beforeSpecialListClose: function(){
                if(this.anySpecialItemRemoved){
                    // 去从新加载 preview
                    this.$emit('timetable-refresh',{})
                }
                this.specialsListVisible = false;
            },
            // 切换单双周的视图
            switchWeekViewHandler: function(){
                let weekType = Constants.WEEK_NUMBER_ODD;
                if(this.weekType === Constants.WEEK_NUMBER_ODD){
                    weekType = Constants.WEEK_NUMBER_EVEN;
                }
                this.$emit('timetable-refresh',{weekType: weekType});
            },
            makeEnquiryColumnHandler: function (payload){
                // payload 就是课表的 item
                this.makeEnquiryFormVisible = true;
                this.enquiryForm = payload;
            },
            makeEnquiryFormSubmitHandler: function () {
                this.enquiryForm.school_id = this.schoolId;
                axios.post(
                    Constants.API.ENQUIRY_SUBMIT,
                    {enquiry: this.enquiryForm, logic: Constants.LOGIC.TIMETABLE.ENQUIRY, userUuid: this.userUuid}
                ).then(res => {
                    if(Util.isAjaxResOk(res)){
                        this.$notify({
                            title: '成功',
                            message: '您的申请已经成功提交!',
                            type: 'success',
                            position: 'bottom-right'
                        });
                        this.makeEnquiryFormVisible = false;
                    }else{
                        this.$notify.error({
                            title: '系统错误',
                            message: res.data.message,
                            position: 'bottom-right'
                        });
                    }
                }).catch( e => {
                    console.log(e);
                    this.$notify.error({
                        title: '系统错误',
                        message: '提交申请操作失败, 请稍候再试 ...',
                        position: 'bottom-right'
                    });
                })
            }
        }
    }
</script>

<style scoped lang="scss">
.timetable-wrap{
    padding: 5px;
    display: block;
    .first-column, .the-column{
        width: 12.5%;
        float: left;
    }
}


.specialsListDialog{
  .title-icon{
    border-radius: 50%;
    color: #fff;
    background-color: #80dd57;
    padding: 8px;
  }
  .tpItems{
    display: flex;
    padding: 0 15px;
    &:not(:last-child){
      border-bottom: 1px solid #ddd;
    }
    .tpLeft, .tpRight{
      width: 50%;
    }
    label.delete-label {
      opacity: 0;
      background-color: #FA3D3D;
      align-self: self-start;
      padding: 2px 13px;
      color: #fff;
      font-size: 15px;
      border-radius: 11%;
    }
    &:hover{
      label.delete-label{
          opacity: 1;
      }
    }
    .tpItem{
      width: 100%;
      margin-bottom: 10px;

      .icon{
        font-size: 17px;
        color: #4EA5FE;
        vertical-align: middle;
        margin-right: 6px;
      }
      img.icon-image {
        margin-right: 6px;
        width: 15px;
      }
      .itemLeft {
        width: 120px;
        display: inline-block;
        font-size:14px;
        font-weight:400;
        color:rgba(138,147,161,1);
        line-height:20px;
        vertical-align: bottom;
      }
      .itemRight{
        font-size:14px;
        font-weight:400;
        color:rgba(65,74,90,1);
        line-height:20px;
      }
      .first{
        font-size:16px;
        font-weight:500;
        color:rgba(65,74,90,1);
        line-height:22px;
      }
    }
  }
}
</style>
<style>
.specialsListDialog  .el-dialog__header{
  border-bottom: 1px solid #ddd;
}
.specialsListDialog .el-dialog__body{
  height: 57vh;
  overflow-y: auto;
}
</style>
