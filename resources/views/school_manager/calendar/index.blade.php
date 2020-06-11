@extends('layouts.app')
@section('content')
    <div class="row" id="school-calendar-app">
        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
            <div class="card" style="{{ \Illuminate\Support\Facades\Auth::user()->isSchoolAdminOrAbove() ? null : 'display:none' }}">
                <div class="card-head">
                    <header>
                        校历事件数据表
                        <el-tag size="mini" v-if="!showEventDeleteBtn">新</el-tag>
                    </header>
                </div>
                <div class="card-body">
                    <el-form ref="form" :model="form" label-width="80px">
                        <label for="">活动日期</label>
                        <el-input v-model="form.event_time"></el-input>
                        <br>
                        <br>
                        <label for="">事件标签</label><br>
                        <el-select
                                v-model="form.type"
                                style="width: 100%;"
                                multiple
                                filterable
                                allow-create
                                default-first-option
                                placeholder="请选择事件标签">
                            <el-option
                                    v-for="(item, id) in tags"
                                    :key="id"
                                    :label="item"
                                    :value="id">
                            </el-option>
                        </el-select>
                        <br>
                        <br>
                        <label>活动安排</label>
                        <br>
                        <el-input rows="5" placeholder="必填: 活动安排的简要说明" type="textarea" v-model="form.content"></el-input>
                        <br>
                        <br>
                        <p class="text-center">
                            <el-button type="primary" @click="onSubmit">保 存</el-button>
                            <el-button v-if="!showEventDeleteBtn">取 消</el-button>
                            <el-button type="danger" v-if="showEventDeleteBtn" @click="deleteEvent()">删除</el-button>
                        </p>
                    </el-form>
                </div>
            </div>
            <div class="card">
                <div class="card-head">
                    <header>
                        值班安排 <i class="el-icon-loading" v-if="isLoading"></i>
                    </header>
                </div>
                <div class="card-body">
                    <p class="text-grey" v-if="specialAttendance === null">
                        还没有安排值周,
                        @if(\Illuminate\Support\Facades\Auth::user()->isSchoolAdminOrAbove())
                        <a href="{{ route('school_manager.attendance.add') }}">添加值周</a>
                        @endif
                    </p>
                    <ul v-if="specialAttendance" style="list-style: none;padding-left: 10px;">
                        <li>
                            <p>周次: <span class="text-primary">@{{ specialAttendance.week_name }}</span></p>
                        </li>
                        <li>
                            <p>值班校领导: <span class="text-primary">@{{ specialAttendance.high_level }}</span></p>
                        </li>
                        <li>
                            <p>值班领导: <span class="text-primary">@{{ specialAttendance.middle_level }}</span></p>
                        </li>
                        <li>
                            <p>值班教师: <span class="text-primary">@{{ specialAttendance.teacher_level }}</span></p>
                        </li>
                        <li>
                            <p>组织单位: <span class="text-primary" :key="idx" v-for="(org, idx) in specialAttendance.related_organizations">@{{ org }} </span></p>
                        </li>
                        <li>
                            <p>班级: <span class="text-primary">@{{ specialAttendance.grade.name }} </span></p>
                        </li>
                        <li>
                            <p>备注: <span>@{{ specialAttendance.description }} </span></p>
                        </li>
                        @if(\Illuminate\Support\Facades\Auth::user()->isSchoolAdminOrAbove())
                            <li>
                                <el-button @click="editSpecial" icon="el-icon-edit" class="pull-right" type="primary">修改</el-button>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-9 col-lg-9 col-xl-9">
            <div class="card">
                <div class="card-head">
                    <header class="full-width">
本学期共: <span class="text-primary">{{ $config->study_weeks_per_term }}周</span>, 开学日期: <span class="text-primary">{{ _printDate($config->getTermStartDate()) }}</span>
                        <a href="{{ route('school_manager.attendance.list') }}" class="btn btn-primary">
                            值周管理
                        </a>
                        <el-button
                                icon="el-icon-notebook-2"
                                type="primary"
                                size="small"
                                class="pull-right"
                                v-if="events.length > 0"
                                @click="showAllEvents = true"
                        >查看本学期校历汇总</el-button>
                    </header>
                </div>
                <div class="card-body">
                    <div class="row">
                        <el-calendar v-on:input="dateClicked" :first-day-of-week="7" v-model="calendarDefaultDate">
                            <!-- 这里使用的是 2.5 slot 语法，对于新项目请使用 2.6 slot 语法-->
                            <template
                                    slot="dateCell"
                                    slot-scope="{date, data}">
                                <p :class="data.isSelected ? 'is-selected' : ''" style="margin: 0;">
                                    @{{ data.day.split('-').slice(1).join('月') }}日 @{{ data.isSelected ? '✔️' : ''}}
                                </p>
                                <div v-if="getEvent(data.day)">
                                    <el-tag
                                            v-for="(t, idx) in getEvent(data.day).tag"
                                            :key="idx" size="mini">
                                        @{{ t }}
                                    </el-tag>
                                </div>
                            </template>
                        </el-calendar>
                    </div>
                </div>
            </div>
        </div>

        <el-dialog title="本学期校历汇总" :visible.sync="showAllEvents" :fullscreen="true">
            <el-table :data="events">
                <el-table-column property="event_time" label="日期"></el-table-column>
                <el-table-column label="活动安排">
                    <template slot-scope="scope">
                        <span v-for="(tg,idx) in scope.row.tag" :key="idx" class="mr-2">
                            <el-tag>@{{ tg }}</el-tag>
                        </span>
                    </template>
                </el-table-column>
                <el-table-column property="content" label="活动安排描述"></el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button @click="dateClicked(scope.row.event_time)" type="primary" size="mini" icon="el-icon-edit"></el-button>
                        <el-button @click="deleteEvent(scope.row)" type="danger" size="mini" icon="el-icon-delete"></el-button>
                    </template>
                </el-table-column>
            </el-table>
        </el-dialog>
    </div>
    <div id="app-init-data-holder"
         data-school="{{ session('school.id') }}"
         data-events='{!! $events->toJson() !!}'
         data-tags='{!! json_encode($tags) !!}'
         data-current='{!! $currentDate !!}'
    ></div>
@endsection
