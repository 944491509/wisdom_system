<template>
    <div class="courses-list-wrap">
        <el-table
                :data="courses"
                style="width: 100%">
            <el-table-column label="课程名称/编号" width="200">
                <template slot-scope="scope">
                    <el-button type="text" v-on:click="courseNameClickedHandler">
                        <p class="txt-primary">
                        <text-badge :text="scope.row.optional ? '选修' : '必修'" :color="scope.row.optional ? 'default' : 'danger'"></text-badge>
                        {{ scope.row.name }}
                        </p>
                        <p class="txt-primary">编号: {{ scope.row.code }}</p>
                    </el-button>
                </template>
            </el-table-column>
            <el-table-column
                    label="适用年级"
                    width="100">
                <template slot-scope="scope">
                    <p class="txt-primary">
                        {{ yearText(scope.row.year) }}
                    </p>
                    <p class="txt-primary">
                        {{ termText(scope.row.term) }}
                    </p>
                </template>
            </el-table-column>
            <el-table-column
                    label="学分/课时数"
                    width="100">
                <template slot-scope="scope">
                    <p class="txt-primary">
                        {{ scope.row.scores }} / {{ scope.row.duration === 0 ? '未设置' : scope.row.duration }}
                    </p>
                </template>
            </el-table-column>
            <el-table-column
                    label="授课教师"
                    width="300">
                <template slot-scope="scope">
                    <span v-for="(t, idx) in scope.row.teachers" :key="idx" style="margin-bottom:3px;">
                    <el-tag size="medium" effect="plain" style="margin:2px;">
                        {{ t.name }}
                    </el-tag>
                    </span>
                </template>
            </el-table-column>
            <el-table-column
                    label="关联专业"
                    width="400">
                <template slot-scope="scope">
                    <el-tag size="medium" type="info" effect="plain" :key="idx" v-for="(m,idx) in scope.row.majors" style="margin:2px;">
                        {{ m.name }}
                    </el-tag>
                    <el-tag v-if="scope.row.majors.length === 0" size="medium" type="success" effect="plain" style="margin:2px;">
                        对所有专业都开放
                    </el-tag>
                </template>
            </el-table-column>
            <el-table-column
                    label="时间安排"
                    width="100">
                <template slot-scope="scope">
                    <el-popover
                            v-if="scope.row.arrangements && scope.row.arrangements.length > 0"
                            placement="top"
                            width="400"
                            trigger="click">
                        <el-tag size="medium" type="info" effect="plain" :key="idx" v-for="(m,idx) in scope.row.arrangements" style="margin:2px;">
                            {{idx+1}}: 第{{ m.week }}周的星期{{ m.day_index }}的{{ describeTimeSlot(m.time_slot_id) }}
                        </el-tag>
                        <el-button size="mini" type="success" slot="reference">{{ scope.row.arrangements.length }}节课</el-button>
                    </el-popover>

                    <el-tag v-if="!scope.row.arrangements || scope.row.arrangements.length === 0" size="medium" type="success" effect="plain" style="margin:2px;">
                        整个学期
                    </el-tag>
                </template>
            </el-table-column>
            <el-table-column
                    label="教材">
                <template slot-scope="scope">
                    <el-tag size="medium" type="info" effect="plain" :key="idx" v-for="(book,idx) in scope.row.books" style="margin:2px;">
                        {{ book.name }}
                    </el-tag>
                    <p >
                        <el-button type="text" v-on:click="attachTextbook(scope.row)">{{ (!scope.row.books || scope.row.books.length === 0)?'添加教材':'编辑教材'}}</el-button>
                    </p>
                </template>
            </el-table-column>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button-group>
                        <el-button size="mini" icon="el-icon-edit" @click="handleEdit(scope.$index, scope.row)"></el-button>
                        <!-- <el-button size="mini" icon="el-icon-timer" @click="handleViewClick(scope.$index, scope.row)"></el-button> -->
                        <el-button v-if="canDelete" size="mini" type="danger" icon="el-icon-delete" @click="handleDelete(scope.$index, scope.row)"></el-button>
                    </el-button-group>
                </template>
            </el-table-column>
        </el-table>
    </div>
</template>

<script>
    import { Constants } from '../../common/constants';
    import { Util } from '../../common/utils';
    import TextBadge from '../../components/misc/TextBadge';
    export default {
        name: "CoursesList",
        components: {
            TextBadge
        },
        props: {
            courses: { // 学校的 ID
                type: Array, required: true
            },
            canDelete: { // 是否具备删除的权限
                type: Boolean, required: false, default: false
            },
            timeSlots: {
                type: Array, required: true
            }
        },
        data(){
            return {
                textbooks:[],
            };
        },
        methods: {
            handleDelete: function(idx, row){
                this.$emit('course-delete', {idx: idx, course: row});
            },
            handleEdit: function(idx, row){
                this.$emit('course-edit', {idx: idx, course: row});
            },
            handleViewClick: function(idx, course){
                // 查看必修课的课程安排, 根据指定的课程 ID
                window.open(Constants.API.TIMETABLE.VIEW_TIMETABLE_FOR_COURSE + '?uuid=' + course.uuid, '_blank');
            },
            yearText: function(year){
                return Constants.YEARS[year];
            },
            termText: function (term) {
                return Constants.TERMS[term];
            },
            courseNameClickedHandler: function(idx, row){
                this.$emit('course-view', {idx: idx, course: row});
            },
            describeTimeSlot: function(id){
                const slot = Util.GetItemById(id, this.timeSlots);
                if(Util.isEmpty(slot)){
                    return 'N.A';
                }
                else{
                    return slot.name;
                }
            },
            attachTextbook: function(course){
                this.$emit('attach-textbook',{course: course});
            }
        }
    }
</script>

<style scoped lang="scss">
    .courses-list-wrap{
        padding: 10px;
    }
    .txt-primary{
        color: #409EFF;
        margin-bottom: 4px;
    }
</style>
