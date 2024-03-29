@php
    use App\Models\Schools\Textbook;
@endphp
@extends('layouts.app')
@section('content')
    <div id="app-init-data-holder"
         data-school="{{ session('school.id') }}"
         data-user="{{ $user->uuid }}"
         data-course="{{ $course->id??null }}"
         data-size="{{ \App\Utils\Misc\ConfigurationTool::DEFAULT_PAGE_SIZE }}"
         data-textbook="{{ $textbook->id??null }}"
    ></div>
    <div class="row" id="textbook-manager-app">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <textbooks-table
                :courses="courses"
                :books="books"
                :types="types"
                :years="years"
                :as-admin="{{ $user->isTeacher() || $user->isEmployee() ? 'false' : 'true' }}"
                v-on:load-textbooks="loadTextbooks"
                v-on:book-edit="editBookAction"
                v-on:connect-courses="connectCoursesAction"
                v-on:book-delete="deleteBookAction"
                @query-text-books="queryTextBooksAction"
            >
                <template>
                    <el-button-group class="pull-right">
                        <el-button type="primary" icon="el-icon-plus" v-on:click="addNewTextbook">添加新教材</el-button>
                        <!-- <el-button icon="el-icon-files" v-on:click="exportByGrade" disabled>按班级导出</el-button>
                        <el-button icon="el-icon-notebook-1" v-on:click="exportByMajor">按专业导出</el-button>
                        <el-button icon="el-icon-notebook-2" v-on:click="exportByCampus">按校区导出</el-button> -->
                    </el-button-group>
                </template>
            </textbooks-table>
            <div class="row">
                <div class="col-12">
                    <el-pagination
                            layout="prev, pager, next"
                            background
                            :hide-on-single-page="total <= pageSize"
                            v-on:current-change="goToPage"
                            :current-page="pageNumber+1"
                            :page-size="pageSize"
                            :total="total">
                    </el-pagination>
                </div>
            </div>
        </div>
        <div>
            <el-dialog
                    title="教材"
                    :visible.sync="showTextbookFormFlag"
                    :fullscreen="true">
                <el-form :model="textbookModel" ref="textbookForm" label-width="100px" class="textbook-form" style="margin-right: 10px;">

                    <el-row>
                        <el-col :span="16">
                            <el-form-item label="教材名称">
                                <el-input @input="updateInput" v-model="textbookModel.name" placeholder="必填: 教材名称"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="版本">
                                <el-input @input="updateInput" v-model="textbookModel.edition" placeholder="必填: 是第几个版本"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>

                    <el-row>
                        <el-col :span="8">
                            <el-form-item label="教材作者">
                                <el-input @input="updateInput" v-model="textbookModel.author" placeholder="必填: 教材作者"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="16">
                            <el-form-item label="出版社">
                                <el-input @input="updateInput" v-model="textbookModel.press" placeholder="必填: 出版社"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>

                    <el-row>
                        <el-col :span="8">
                            <el-form-item label="教材类型">
                                <el-select v-model="textbookModel.type" placeholder="必填: 教材类型">
                                    <el-option v-for="item in types" :key="item.id" :label="item.type" :value="item.id"></el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="课本进价">
                                <el-input @input="updateInput" v-model="textbookModel.purchase_price" placeholder="选填: 课本进价"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="课本零售价">
                                <el-input @input="updateInput" v-model="textbookModel.price" placeholder="必填: 课本零售价"></el-input>
                            </el-form-item>
                        </el-col>

                    </el-row>
                    <el-row>
                        <el-col :span="8">
                            <el-form-item label="年级">
                                <el-select v-model="textbookModel.year" placeholder="请选择年级">
                                    <el-option v-for="item in years" :key="item.year" :label="item.text" :value="item.year"></el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="学期">
                                <el-select v-model="textbookModel.term" placeholder="请选择学期">
                                    <el-option label="第一学期" :value="1"></el-option>
                                    <el-option label="第二学期" :value="2"></el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>

                    </el-row>
                    <el-form-item>
                        <el-button type="primary" icon="el-icon-picture" v-on:click="showFileManagerFlag=true">选择图书封面图片</el-button>
                    </el-form-item>
                    <el-form-item>
                        <div class="row">
                            <div class="col-4" v-for="(media, idx) in textbookModel.medias" :key="idx">
                                <file-preview
                                        :file-dic="media"
                                        v-on:preview-delete="selectedFileDeleted"
                                        :has-delete-button="true"
                                ></file-preview>
                            </div>
                            <div class="col-12" v-if="textbookModel.medias.length === 0">
                                <p class="text-info">还没选择图片</p>
                            </div>
                        </div>
                    </el-form-item>
                    <el-form-item label="课材简介">
                        <el-input @input="updateInput" type="textarea" v-model="textbookModel.introduce" placeholder="可选"></el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="saveTextbook">保 存</el-button>
                        <el-button @click="cancel">取 消</el-button>
                    </el-form-item>
                </el-form>
            </el-dialog>

            <el-dialog :title="textbookModel.name" :visible.sync="showConnectedCoursesFlag">
                <p>采用该教材的所有课程: </p>
                <el-form :model="{}">
                    <el-form-item>
                        <el-select v-model="textbookModel.courses" multiple placeholder="请选择" style="width: 100%;">
                            <el-option
                                    v-for="(course, idx) in courses"
                                    :key="idx"
                                    :label="course.name"
                                    :value="course.id">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-form>
                <div slot="footer" class="dialog-footer">
                    <el-button @click="showConnectedCoursesFlag = false">取 消</el-button>
                    <el-button type="primary" @click="updateTextbookRelatedCourses">确 定</el-button>
                </div>
            </el-dialog>

            <el-dialog title="专业教材汇总表导出工具" :visible.sync="showExportMajorFlag">
                <p>请选择需要导出的专业: </p>
                <el-form :model="exportModel">
                    <el-form-item>
                        <el-select v-model="exportModel.value" placeholder="请选择" style="width: 100%;">
                            <el-option
                                    v-for="(major, idx) in majors"
                                    :key="idx"
                                    :label="major.name"
                                    :value="major.id">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-form>
                <div slot="footer" class="dialog-footer">
                    <el-button @click="showExportMajorFlag = false">取 消</el-button>
                    <el-button type="primary" @click="exportBooksSheet">确 定</el-button>
                </div>
            </el-dialog>

            <el-dialog title="校区教材汇总表导出工具" :visible.sync="showExportCampusFlag">
                <p>请选择需要导出的校区: </p>
                <el-form :model="exportModel">
                    <el-form-item>
                        <el-select v-model="exportModel.value" placeholder="请选择" style="width: 100%;">
                            <el-option
                                    v-for="(campus, idx) in campuses"
                                    :key="idx"
                                    :label="campus.campus"
                                    :value="campus.id">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-form>
                <div slot="footer" class="dialog-footer">
                    <el-button @click="showExportCampusFlag = false">取 消</el-button>
                    <el-button type="primary" @click="exportBooksSheet">确 定</el-button>
                </div>
            </el-dialog>

            @include(
                'reusable_elements.section.file_manager_component',
                ['pickFileHandler'=>'pickFileHandler']
            )
        </div>
    </div>
@endsection
