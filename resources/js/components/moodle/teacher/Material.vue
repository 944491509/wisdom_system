<template>
    <div class="row lecture-wrap">
        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 lecture-detail">
            <div class="card">
                <div class="card-body">
                    <h3>
                        添加资料
                    </h3>
                    <hr>
                        <el-form :model="formCourseInfo" label-width="80px" class="course-form" style="margin-top: 20px;">

                            <el-form-item label="课节">
                                <el-select v-model="formCourseInfo.idx" placeholder="请选择" style="width: 100%" @change="getCourseGradeList">
                                    <el-option
                                        v-for="item in durations"
                                        :key="item.idx"
                                        :label="item.name"
                                        :value="item.idx">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="标题">
                                <el-input placeholder="必填: 标题" v-model="formCourseInfo.title" maxlength="50"></el-input>
                            </el-form-item>

                            <el-form-item label="班级">
                                <el-checkbox-group v-model="formCourseInfo.grade_id" size="small">
                                    <el-checkbox-button  v-for="item in grades" :label="item.grade_id" :key="item.grade_id">{{ item.grade_name }}</el-checkbox-button>
                                </el-checkbox-group>
                            </el-form-item>
                        </el-form>
                    <hr>

                    <el-timeline>
                            <el-timeline-item
                                    v-for="(val, key) in materialTypes"
                                    :key="key"
                                    :timestamp="val.name"
                                    placement="top"
                                    size="large"
                                    icon="el-icon-folder-opened"
                            >
                            <el-card>
                                <div v-for="material in materials" :key="material.type_id">
                                    <div v-if="material.type_id == val.type_id">
                                        <p>
                                            <el-tag size="small" v-if="material.list[0].media_id === 0">
                                                外部链接
                                                <span>
                                                    <a :href="material.url" target="_blank">
                                                        {{  material.list[0].url}}
                                                    </a>
                                                </span>
                                            </el-tag>
                                            <span v-else>
                                                <!-- <a v-if="material.list[0].file_name">{{ material.list[0].file_name }}</a> -->
                                                <a :href="material.list[0].url" target="_blank">{{material.list[0].file_name ? material.list[0].file_name : material.list[0].url}}</a>
                                            </span>
                                        </p>
                                        <!-- <p style="font-size: 10px;color: #cccccc;" class="text-right">
                                            上传于{{ material.created_at }} &nbsp;
                                            <el-button type="text" @click="deleteMaterial(material)">
                                                <span class="text-danger">删除</span>
                                            </el-button>
                                            <el-button type="text" @click="editMaterial(material)">
                                                <span>修改</span>
                                            </el-button>
                                        </p> -->
                                        <hr style="margin-top: 3px;">
                                    </div>
                                </div>
                                <p class="text-right">
                                    <el-button icon="el-icon-upload" size="mini" @click="addMaterial(val)">添加{{ val.name }}</el-button>
                                </p>
                            </el-card>
                        </el-timeline-item>
                    </el-timeline>
                    <el-button style="margin-left: 10px;" size="small" type="success" @click="saveMaterials">保存</el-button>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
            <div class="card" v-show="showMaterialForm">
                <div class="card-body">
                    <el-form :model="courseMaterialModel" label-width="120px" class="course-form" style="margin-top: 20px;">
                        <!-- <el-form-item label="当前类型">
                            <p class="text-primary">{{ courseMaterialModel.typeName }}</p>
                        </el-form-item> -->
                        <el-form-item label="课件描述">
                            <el-input placeholder="选填: 课件的描述" type="textarea" v-model="courseMaterialModel.description"></el-input>
                        </el-form-item>

                        <el-form-item label="外部链接">
                            <el-input placeholder="选填: 外部引用的URL链接地址" type="textarea" v-model="courseMaterialModel.url"></el-input>
                        </el-form-item>

                        <el-form-item label="选择课件文件">
                            <el-button type="primary" size="tiny" icon="el-icon-picture" v-on:click="showFileManagerFlag=true">
                                从我的云盘添加
                            </el-button>
                            <p class="text-danger ">注意: 课件只能是外部链接或者云盘文件中的一种</p>
                            <p v-if="selectedFile" class="mt-4">
                                <a :href="selectedFile.url">
                                    {{ selectedFile.description }}
                                </a>
                                &nbsp;&nbsp;
                                 <el-button type="danger" @click="selectedFile = null" icon="el-icon-delete"></el-button>
                            </p>
                        </el-form-item>

                        <el-button icon="el-icon-upload" style="margin-left: 10px;" size="small" type="success" @click="saveInfo(courseMaterialModel.index)">确定</el-button>
                        <el-button icon="el-icon-close" style="margin-left: 10px;" size="small" @click="showMaterialForm = false">取消</el-button>
                    </el-form>
                </div>
            </div>
        </div>
        <el-drawer
                title="我的易云盘"
                :visible.sync="showFileManagerFlag"
                direction="rtl"
                size="100%"
                custom-class="e-yun-pan">
            <file-manager
                    :user-uuid="userUuid"
                    :allowed-file-types="[]"
                    :pick-file="true"
                    v-on:pick-this-file="pickFileHandler"
            ></file-manager>
        </el-drawer>
    </div>
</template>

<script>
    import {Constants} from '../../../common/constants';
    import {Util} from '../../../common/utils';
    import {saveMaterial, saveLecture, loadLectureMaterials, loadMaterial, loadLectureHomework} from '../../../common/course_material';
    import FileManger from '../../fileManager/FileManager';
    import Homeworks from './Homeworks';

    export default {
        name: "Material",
        components:{
            FileManger,Homeworks
        },
        props:{
            course:{
                type: Object,
                required: true
            },
            lecture:{
                required: true
            },
            loading:{
                required:false,
                default:false
            },
            userUuid:{
                type: String,
                required: true
            },
            // grades:{
            //     type: Array,
            //     required: true
            // },
            // courseId: {
            //     type: String,
            //     // required: true
            // }
        },
        watch: {
            'lecture.id': function(val){
                if(val){
                    this.getLectureMaterials();
                    this.lectureModel.id = this.lecture.id;
                    this.lectureModel.title = this.lecture.title;
                    this.lectureModel.summary = this.lecture.summary;
                    this.showMaterialForm = false;
                    this.loading = false;

                    // 设置课节id
                    //this.courseMaterialModel.lecture_id = this.lecture.id;
                    this.courseMaterialModel.type = null;
                    // 设置选定的班级的默认id
                    this.selectedGrades = [];
                    this.homeworkItems = [];
                }
            }
        },
        computed:{
            currentType: function(){
               // return this.materialTypes[this.courseMaterialModel.type];
            }
        },
        data(){
            return {
                grades:[
                ], // 班级列表
                durations:[
                ], // 课节列表

                materials:[],
                materialTypes:[], // 类型
                typeClasses:[
                    '','success','info','warning','danger'
                ],
                // 添加材料
                formCourseInfo:{
                    id:null,
                    title:null, // 标题
                    teacher_id:null, // 老师id
                    course_id:null, // 课程id
                    idx:null, //课节
                    grade_id:[], // 班级id
                    type: null,
                    index: null,
                    description: null,
                    url: null,
                    media_id: 0,
                    lecture_id: null,
                    // 资料
                    materialArr:[],
                },

                // 添加材料
                courseMaterialModel:{
                    index: null,
                    type: null,
                    typeName: "", // 分类名称
                    description: null, // 描述
                    url: null, // url 地址
                    media_id: 0 // 资源id
                },
                lectureModel: {
                    id: null,
                    title: null,
                    summary: null,
                },
                showMaterialForm: false,
                showFileManagerFlag: false,
                selectedFile:null,
                loadingData: false,
                // 当前选择的班级
                selectedGrades: [],
                homeworkItems:[],
            }
        },
        created(){
            this.getGrades()
            this.getMaterialTypes(); // 获取类型
            // this.getCourseGradeList(); // 获取 班级 和 课节
            this.lectureModel = this.lecture;
        },
        methods: {
            // 获取课程班级列表和课节列表
            getGrades() {
                console.log('this.courseId',this.course.id)
                axios.post(
                    '/api/material/getCourseGradeList',
                    { course_id: this.course.id }
                ).then(res => {
                    if (Util.isAjaxResOk(res)) {
                        this.grades = res.data.data.grades;
                        this.durations = res.data.data.durations;
                    }
                });
            },
            // 获取类型
            getMaterialTypes: function(){
                let _that_ = this;
                axios.post(
                    '/api/study/type-list',
                    {}
                ).then(res => {
                    if(Util.isAjaxResOk(res)){
                        _that_.materialTypes = res.data.data;
                    }
                });
            },
            // 获取列表数据
            getCourseGradeList: function(val){
                axios.post(
                    '/api/material/get-materials',
                    { idx: val, course_id: this.course.id }
                ).then(res => {
                    if (Util.isAjaxResOk(res)) {
                        if(res.data.data.idx){
                            this.formCourseInfo.lecture_id = res.data.data.lecture_id
                            this.formCourseInfo.title = res.data.data.title
                            this.formCourseInfo.idx = res.data.data.idx
                            this.formCourseInfo.grade_id = res.data.data.grade || []
                            this.materials = res.data.data.material || []
                        }else{
                            this.formCourseInfo.lecture_id =null;
                            this.formCourseInfo.title = '';
                            this.formCourseInfo.grade_id = [];
                             this.materials = [];
                        }
                    }
                });
            },
            getLectureMaterials: function(){
                // this.loadingData = true;
                // loadLectureMaterials(this.lecture.id).then(res => {
                //     if(Util.isAjaxResOk(res)){
                //         this.materials = res.data.data.materials;
                //         console.log('CCCCC',this.materials)
                //     }
                //     else{
                //         this.materials = [];
                //     }
                //     this.loadingData = false;
                // })
            },
            isTypeOf: function(typeId, typeIdx){
                return typeId === typeIdx+1;
            },

            // 左边模块保存数据到右边
            addMaterial: function(val){
                if (!this.formCourseInfo.idx) {
                    this.$message.error('请选择课节');
                    return
                }
                let materialsByTypeId = this.materials.find(e=>e.type_id== val.type_id);

                this.courseMaterialModel.index = val.type_id;
                this.selectedFile = null;
                this.courseMaterialModel.type = 0;
                this.courseMaterialModel.description = '';
                this.courseMaterialModel.url =''
                if(materialsByTypeId){

                    this.courseMaterialModel.type = materialsByTypeId.type || 0;
                    this.courseMaterialModel.description = materialsByTypeId.desc || '';
                    if(materialsByTypeId.list[0].media_id === 0){
                      // console.log('走的11')
                        this.courseMaterialModel.url =  materialsByTypeId.list[0].url || '';
                    }else{
                      // console.log('走的22')
                        // this.courseMaterialModel.media_id = materialsByTypeId.list[0].media_id
                        this.selectedFile = {
                            url: materialsByTypeId.list[0].url,
                            description: materialsByTypeId.list[0].url,
                            id: materialsByTypeId.list[0].media_id,
                        }
                    }

                }

                 console.log('materialsByTypeId', materialsByTypeId,this.courseMaterialModel)

                this.showMaterialForm = true;
                this.typeName = val.name; // 分类名称
            },
            deleteMaterial: function(material){

            },
            editMaterial: function(material){
                loadMaterial(material.id).then(res => {
                    if(Util.isAjaxResOk(res)){
                        this.courseMaterialModel = res.data.data.material;
                        this.showMaterialForm = true;
                    }
                    else{
                        this.$message.error('无法加载课件');
                    }
                })
            },
            // 右边模块保存数据到左边
            saveInfo: function(index){
                // this.formCourseInfo.materialArr[index] = JSON.parse(JSON.stringify(this.courseMaterialModel));
                // console.log(index, this.formCourseInfo.materialArr[index])
                let tmp = JSON.parse(JSON.stringify(this.courseMaterialModel));
                // console.log('AAAAAA', tmp)
                let tmpMaterial = {}
                if (this.selectedFile) {
                  // console.log('走的1')
                    tmpMaterial = {
                        type_id: tmp.index,
                        desc: tmp.description,
                        list: [{
                            url: this.selectedFile.url,
                            media_id: this.selectedFile.id,
                            file_name: this.selectedFile.file_name
                        }]
                    }
                } else {
                  // console.log('走的2')
                    tmpMaterial = {
                        type_id: tmp.index,
                        desc: tmp.description,
                        list: [{
                            url: tmp.url,
                            media_id: tmp.media_id ? tmp.media_id : 0,
                            file_name: tmp.url
                        }]
                    }
                    // console.log('BBBB',tmpMaterial)
                }

                console.log('this.materials',this.materials)
                if (this.materials.length > 0) {
                    let id = this.materials.findIndex(e => {
                        return e.type_id == tmp.index
                    })
                    if (id == -1) {
                        this.materials.push(tmpMaterial)
                    } else {
                        this.materials.splice(id, 1, tmpMaterial)
                    }
                } else {
                    this.materials.push(tmpMaterial)
                }
                console.log('this.materials',this.materials)
            },
            // _startSaving: function(){
            //     console.log('this.courseMaterialModel',this.courseMaterialModel)
            //     saveMaterial(params).then(res => {
            //         if(Util.isAjaxResOk(res)){
            //             this.$message({
            //                 type:'success',
            //                 message:'保存成功'
            //             });
            //             // 隐藏表单
            //             this.showMaterialForm = false;
            //             // 从新加载materials
            //             // this.getLectureMaterials();
            //             this.getCourseGradeList()
            //         }
            //         else{
            //             this.$message.error('保存失败. ' + res.data.data);
            //         }
            //     })
            // },
            // 显示编辑可课节的标题，概要的表单
            showLectureSummaryEditForm: function(){
                this.showLectureForm = true;
                this.lectureModel.id = this.lecture.id;
            },
            // 添加数据
            saveMaterials: function(){

                if(Util.isEmpty(this.formCourseInfo.title)){
                    this.$message.error('请填写标题');
                    return false;
                }
                if(Util.isEmpty(this.formCourseInfo.idx)){
                    this.$message.error('请选择课节');
                    return false;
                }
                if(Util.isEmpty(this.formCourseInfo.grade_id)){
                    this.$message.error('请选择班级');
                    return false;
                }
                let materials = JSON.parse(JSON.stringify(this.materials))
                materials = materials.map(e=>{
                    e.media = JSON.parse(JSON.stringify(e.list))
                    delete e.list
                    return e;
                })
                let params = {
                    course_id: this.course.id,
                    idx: this.formCourseInfo.idx,
                    grade_id: this.formCourseInfo.grade_id,
                    title: this.formCourseInfo.title,
                    lecture_id: this.formCourseInfo.lecture_id,
                    material: materials
                }
                // 添加数据
                axios.post(
                    '/api/material/addMaterial',
                    params
                ).then(res => {
                    if(Util.isAjaxResOk(res)){
                        this.$message({
                            type:'success',
                            message: '保存成功'
                        });
                         window.location.reload();
                    } else {
                        this.$message({
                            type: 'info',
                            message: '操作失败,请稍后重试'
                        });
                    }
                });
            },
            // saveLecture: function(){

            //     if(Util.isEmpty(this.formCourseInfo.title)){
            //         this.$message.error('请填写标题');
            //         return false;
            //     }
            //     if(Util.isEmpty(this.formCourseInfo.idx)){
            //         this.$message.error('请选择课节');
            //         return false;
            //     }
            //     if(Util.isEmpty(this.formCourseInfo.grade_id)){
            //         this.$message.error('请选择班级');
            //         return false;
            //     }

            //     // 添加数据
            //     let _that_ = this;
            //     console.log('this.formCourseInfo',this.formCourseInfo)
            //     axios.post(
            //         '/api/material/addMaterial',
            //         {formData:this.formCourseInfo}
            //     ).then(res => {
            //         if(Util.isAjaxResOk(res)){
            //             this.$message({
            //                 type:'success',
            //                 message: '保存成功'
            //             })
            //         } else {
            //             this.$message({
            //                 type: 'info',
            //                 message: '操作失败,请稍后重试'
            //             });
            //         }
            //     });
            // },
            // 当云盘中的文件被选择
            pickFileHandler: function(payload){
                this.selectedFile = payload.file;
                this.showFileManagerFlag = false;
                console.log('添加的可见信息',payload)
                this.courseMaterialModel.description =this.selectedFile.file_name
            },
            // 当选择的班级发生变化, 则去更新作业的数据
            onSelectedGradesChangedHandler: function(updatedGrades){
                if(updatedGrades.length === 0){
                    this.homeworkItems = [];
                }
                else{
                    this.refreshHomeworkItems();
                }
            },
            // 刷新作业列表
            refreshHomeworkItems: function(){
                this.loadingData = true;
                loadLectureHomework(this.lecture.id, this.selectedGrades).then(res => {
                    if(Util.isAjaxResOk(res)){
                        this.homeworkItems = res.data.data.homeworks;
                    }
                    this.loadingData = false;
                })
            }
        }
    }
</script>

<style scoped lang="scss">
.lecture-wrap{
    .lecture-detail{

    }
}
</style>
