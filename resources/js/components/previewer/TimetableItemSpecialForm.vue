<template>
	<div class="timetable-item-form-wrap">
		<p style="display: flex;justify-content: space-between;margin-right: 40px;">
			<span>课程: {{ toBeReplacedItem.course }}</span>
			<span>老师: {{ toBeReplacedItem.teacher }}</span>
			<span>{{ toBeReplacedItem.building }}-{{ toBeReplacedItem.room }}</span>
		</p>
		<el-divider></el-divider>
		<el-form
			ref="specialTimeTableItemForm"
			:model="specialTimeTableItem"
			label-width="80px"
			class="the-form"
		>
			<div class="row">
				<div class="col-12">
					<div
						class="card"
						style="margin-left:11px;box-shadow:none;padding-right:11px;padding-top:14px;border:none;"
					>
						<div style="display: flex;">
							<el-form-item label="开始日期">
								<el-date-picker
									v-model="specialTimeTableItem.at_special_datetime"
									value-format="yyyy-MM-dd"
									type="date"
									:editable="false"
                  @change = "startPickerChange"
									:picker-options="startPickerOptions"
									placeholder="选择日期"
								></el-date-picker>
							</el-form-item>
							<el-form-item label="结束日期" style="margin-left: 30px;">
								<el-date-picker
									v-model="specialTimeTableItem.to_special_datetime"
									type="date"
                  value-format="yyyy-MM-dd"
									:picker-options="endPickerOptions"
									placeholder="选择日期"
								></el-date-picker>
							</el-form-item>
						</div>
						<el-form-item label="调课类型">
							<el-radio-group
								v-model="specialTimeTableItem.type"
								@change="selectChange"
								style="line-height: 48px;"
							>
								<el-radio label="0">其他教师代课</el-radio>
								<el-radio label="1">本班教师课节互换</el-radio>
								<el-radio label="2">其他班互换课</el-radio>
							</el-radio-group>
						</el-form-item>
						<el-form-item label="班级" v-if="selectType3">
							<el-select v-model="specialTimeTableItem.class_id" style="width: 50%;">
								<el-option
									:label="grade.name"
									:value="grade.grade_id"
									:key="grade.grade_id"
									v-for="grade in grades"
								></el-option>
							</el-select>
							<!-- <span class="help-text">说明: 请选择要教授哪门课程</span> -->
						</el-form-item>
						<el-form-item label="星期" v-if="selectType2 || selectType3">
							<el-select v-model="specialTimeTableItem.week_id" style="width: 50%;">
								<el-option
									:label="day"
									:value="index + 1"
									:key="index"
									v-for="(day,index) in weekdays"
								></el-option>
							</el-select>
							<!-- <span class="help-text">说明: 请选择要教授哪门课程</span> -->
						</el-form-item>
						<el-form-item label="课节" v-if="selectType2 || selectType3">
							<el-select v-model="specialTimeTableItem.course_id" style="width: 50%;">
								<el-option
									:label="course.name"
									:value="course.id"
									:key="course.id"
									v-for="course in timeslots"
								></el-option>
							</el-select>
							<!-- <span class="help-text">说明: 请选择要教授哪门课程</span> -->
						</el-form-item>
						<el-form-item label="代课教师" v-if="selectType1">
							<el-select v-model="specialTimeTableItem.teacher_id" style="width: 50%;">
								<el-option
									:label="teacher.name"
									:value="teacher.id"
									:key="teacher.id"
									v-for="teacher in teachers"
								></el-option>
							</el-select>
							<!-- <span class="help-text">说明: 请选择授课的老师</span> -->
						</el-form-item>
						<el-form-item label="教学楼" v-if="selectType1">
							<el-select v-model="specialTimeTableItem.building_id" placeholder="请选择" style="width: 50%;">
								<el-option-group v-for="item in campuses" :key="item.campus" :label="item.campus">
									<el-option
										v-for="building in item.buildings"
										:key="building.id"
										:label="building.name"
										:value="building.id"
									></el-option>
								</el-option-group>
							</el-select>
							<!-- <span class="help-text">说明: 请选择在哪栋楼上课</span> -->
						</el-form-item>
						<el-form-item label="教室/地点" v-if="selectType1">
							<el-select v-model="specialTimeTableItem.room_id" style="width: 50%;">
								<el-option :value="room.id" :label="room.name+': '+room.description+'(可容纳: '+room.seats+'人)'" :key="room.id" v-for="room in rooms">
                  {{room.name+': '+room.description+'(可容纳: '+room.seats+'人)'}}
                </el-option>
							</el-select>
							<!-- <span class="help-text">说明: 请选择上面选择的楼的那个房间上课</span> -->
						</el-form-item>
						<el-divider></el-divider>
						<!-- <p class="text-center">
							<el-switch v-model="specialTimeTableItem.published" active-text="立即生效" inactive-text="存为草稿"></el-switch>
						</p> -->
					</div>
				</div>
			</div>
		</el-form>
	</div>
</template>

<script>
import { Constants } from "../../common/constants";
import { Util } from "../../common/utils";

export default {
	name: "TimetableItemSpecialForm",
	props: {
    // 传递来的表单数据
    timeTableItem: {
        type: Object,
        required: true,
    },
		// 传递来的替代表单数据
		specialTimeTableItem: {
			type: Object,
			required: true
		},
		userUuid: {
			type: [Number, String],
			required: true
		},
		toBeReplacedItem: {
			type: Object,
			required: true
		},
		subtitle: {
			type: String,
			required: true
		},
		courses: {
			type: Array,
			required: true
		},
		schoolId: {
			type: [Number, String],
			required: true
		}
	},
	computed: {
		// 上课地点的表述
		locationText: function() {
			let buildingText = "";
			if (
				this.timeTableItem.building_id !== "" &&
				this.timeTableItem.room_id !== ""
			) {
				// 获取建筑物的文本
				const theBuildingId = this.timeTableItem.building_id;
				this.campuses.forEach(item => {
					if (item.buildings) {
						const building = Util.GetItemById(theBuildingId, item.buildings);
						if (building) {
							buildingText = item.campus + ", " + building.name;
						}
					}
				});
				// 获取教室
				const theRoom = Util.GetItemById(
					this.timeTableItem.room_id,
					this.rooms
				);
				if (theRoom) {
					buildingText += ", " + theRoom.name;
				}
			}
			return buildingText;
		},
		// 上课内容的表述
		courseText: function() {
			if (this.timeTableItem.course_id !== "") {
				let course = Util.GetItemById(
					this.timeTableItem.course_id,
					this.courses
				);
				if (!Util.isEmpty(course)) {
					return course.name;
				}
			}
		},
		// 授课教师的表述
		teacherText: function() {
			if (this.timeTableItem.teacher_id !== "") {
				let teacher = Util.GetItemById(
					this.timeTableItem.teacher_id,
					this.teachers
				);
				if (!Util.isEmpty(teacher)) {
					return teacher.name;
				}
			}
		}
	},
	data() {
		return {
			campuses: [],
      rooms: [],
      grades:[],
      timeslots:[],
			teachers: [], // 根据专业加载的候选班级
			// 来自本地,暂无需远程加载的选项
			savingActionInProgress: false,
			selectType1: true,
			selectType2: false,
			selectType3: false,
			startPickerOptions: {
				disabledDate: (time) => {
          return time.getTime() < Date.now();
				}
      },
      endPickerOptions:{
        disabledDate:(time) => {
					return time.getTime() < new Date(this.specialTimeTableItem.at_special_datetime).getTime() - 24 *3600 *1000;
				}
      },
      weekdays: Constants.WEEK_DAYS
		};
	},
	// 监听
	watch: {
		"specialTimeTableItem.building_id": function(newVal, oldVal) {
			if (newVal !== oldVal) {
				// 去加载房间
        this._getRoomsByBuilding(newVal);
			}
		},
		"specialTimeTableItem.course_id": function(newVal, oldVal) {
			if (newVal !== oldVal) {
				// 去加载老师
				this._getTeachersByCourse(newVal);
			}
		}
	},
	created() {
		this._getAllBuildings();

		this.getGrageListByGrade_id();
		this._getTeachersByCourse(this.toBeReplacedItem.course_id);
		this.getTimeslot();
	},
	mounted() {
		this.specialTimeTableItem.type = "0";
	},
	methods: {
    startPickerChange(){
      let start =  new Date(this.specialTimeTableItem.at_special_datetime).getTime();
      let end =  new Date(this.specialTimeTableItem.to_special_datetime).getTime();
      if(start > end){
        this.specialTimeTableItem.to_special_datetime = this.specialTimeTableItem.at_special_datetime
      }
    },
    getGrageListByGrade_id(){
      axios
        .post(`/api/timetable/gradeList`, { grade_id: this.timeTableItem.grade_id })
        .then(res => {
          if (Util.isAjaxResOk(res)) {
            this.grades = res.data.data || [];
          } else {
            this.grades = [];
          }
        });
    },
    getTimeslot(){
        axios
        .post(`/api/timetable/timeslot`, { grade_id: this.timeTableItem.grade_id })
        .then(res => {
          if (Util.isAjaxResOk(res)) {
            this.timeslots = res.data.data || [];
          } else {
            this.timeslots = [];
          }
        });
    },
		// 获取学校的所有建筑, 按校区分组
		selectChange() {
      let val = this.specialTimeTableItem.type
      console.log(val);
			if (val === "0") {
				this.selectType1 = true;
				this.selectType2 = false;
				this.selectType3 = false;
				this.specialTimeTableItem.class_id = ''
				this.specialTimeTableItem.week_id = ''
				this.specialTimeTableItem.course_id = ''
			} else if (val === "1") {
				this.selectType1 = false;
				this.selectType2 = true;
				this.selectType3 = false;
				this.specialTimeTableItem.class_id = ''
				this.specialTimeTableItem.teacher_id = ''
				this.specialTimeTableItem.building_id = ''
				this.specialTimeTableItem.room_id = ''
			} else if (val === "2") {
				this.selectType1 = false;
				this.selectType2 = false;
				this.selectType3 = true;
				this.specialTimeTableItem.teacher_id = ''
				this.specialTimeTableItem.building_id = ''
				this.specialTimeTableItem.room_id = ''
			}
		},
		_getAllBuildings: function() {
			axios
				.post(Constants.API.LOAD_BUILDINGS_BY_SCHOOL, { school: this.schoolId })
				.then(res => {
					if (Util.isAjaxResOk(res)) {
						this.campuses = res.data.data.campuses;
					}
				});
		},
		// 获取某个建筑的所有房间
		_getRoomsByBuilding: function() {
			// 获取房间时, 要根据前面一个步骤选择的时间段来进行判断.
			// 如果给定年度的, 给定学期的, 给定时间段, 给定的建筑物内,
			// 某个教室是可能被占用的, 因此被占用的不可以被返回
			axios
				.post(`/api/school/load-building-rooms`, {
					building: this.specialTimeTableItem.building_id,
				})
				.then(res => {
					if (Util.isAjaxResOk(res)) {
            this.rooms = res.data.data.rooms;
            this.specialTimeTableItem.room_id = ''
					} else {
						this.rooms = [];
					}
				});
		},
		_getTeachersByCourse: function(courseId) {
			if (courseId !== "") {
				// 传入了有效的 course id
				axios
					.post(Constants.API.LOAD_TEACHERS_BY_COURSE, { course: courseId })
					.then(res => {
						if (Util.isAjaxResOk(res)) {
							this.teachers = res.data.data.teachers;
						} else {
							this.teachers = [];
						}
					});
			} else {
				this.teachers = [];
			}
		},
		saveItem: function() {
			// Todo: 课程表的 item, 保存之前应该做一些有效性检查
			this.savingActionInProgress = true;
			const isCreate = this.timeTableItem.id === null;
			axios
				.post(
					isCreate
						? Constants.API.TIMETABLE.SAVE_NEW
						: Constants.API.TIMETABLE.UPDATE,
					{
						timetableItem: this.timeTableItem,
						school: this.schoolId,
						user: this.userUuid
					}
				)
				.then(res => {
					if (Util.isAjaxResOk(res)) {
						// 保存成功, 那么发布一个事件, 表示有更新, 去刷新 preview
						if (!this.timeTableItem.id) {
							// 这个是新创建的
							this.timeTableItem.id = res.data.data.id;
							this.$emit("new-item-created", this._getPayload());
						} else {
							this.$emit("item-updated", this._getPayload());
						}
						this.currentStep = 1;
					}
					this.savingActionInProgress = false;
				});
		}
	}
};
</script>

<style scoped lang="scss">
.timetable-item-form-wrap {
	.the-form {
		padding-right: 10px;
		.summary-wrap {
			padding-top: 14px;
			padding-left: 20%;
			padding-right: 20%;
			.item-summary {
				font-size: 18px;
				font-weight: bold;
				color: #3490dc;
			}
			.item-text {
				font-size: 14px;
				color: #888888;
				line-height: 24px;
				.label-text {
					color: #0c0c0c;
					font-weight: bold;
					width: 80px;
					display: inline-block;
				}
			}
		}
		.help-text {
			color: #888888;
			padding-left: 10px;
		}
	}
}
</style>
