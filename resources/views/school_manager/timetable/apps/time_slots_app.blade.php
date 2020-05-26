@php
    use App\Utils\UI\Anchor;
    use App\Utils\UI\Button;
    use App\User;
    use App\Utils\Misc\ConfigurationTool;
    /**
     * @var \App\Models\Schools\SchoolConfiguration $config
     */
    $ecFrom1 = $config->getElectiveCourseAvailableFrom(1); // 第一学期选修课开始选课的时间
    $ecTo1= $config->getElectiveCourseAvailableTo(1); // 第一学期选修课结束选课的时间
    $ecFrom2 = $config->getElectiveCourseAvailableFrom(2); // 第2学期选修课开始选课的时间
    $ecTo2= $config->getElectiveCourseAvailableTo(2); // 第2学期选修课结束选课的时间
    $term1Start= $config->getTermStartDate(1); // 第1学期的开学时间
    $term2Start= $config->getTermStartDate(2); // 第2学期开学时间
    $summerStart= $config->summer_start_date ?? \Carbon\Carbon::now(); // 第2学期开学时间
    $winterStart= $config->winter_start_date ?? \Carbon\Carbon::now(); // 第2学期开学时间
@endphp

<div class="row" id="school-time-slots-manager" schoolid="{{$school->id}}">
    <div class="col-4">
        <div class="card">
            <div class="card-head">
                <header>{{ session('school.name') }} 日期配置</header>
            </div>
            <div class="card-body">
                <form action="{{ route('school_manager.school.config.update') }}" method="post"  id="edit-school-config-form">
                    @csrf
                    <input type="hidden" id="school-config-id-input" name="uuid" value="{{ session('school.uuid') }}">
                    <input type="hidden" name="redirectTo" value="{{ route('school_manager.timetable.manager',['uuid'=>session('school.uuid')])  }}">
                    <div class="form-group">
                        <label>秋季开学, 学生可以在以下时间段选择选修课</label>
                        <div class="clearfix"></div>
                        @php
                            $months = range(1,12);
                            $days = range(1,31);
                        @endphp
                        <select class="form-control pull-left mr-2" name="ec1[from][month]" style="width: 20%;">
                            @foreach($months as $month)
                                <option value="{{ $month }}" {{ $month===$ecFrom1->month ? 'selected':null }}>{{ $month }}月</option>
                            @endforeach
                        </select>
                        <select class="form-control pull-left" name="ec1[from][day]" style="width: 20%;">
                            @foreach($days as $day)
                                <option value="{{ $day }}" {{ $ecFrom1->day === $day ? 'selected':null }}>{{ $day }}日</option>
                            @endforeach
                        </select>
                        <p class="pull-left m-2"> - </p>
                        <select class="form-control pull-left mr-2" name="ec1[to][month]" style="width: 20%;">
                            @foreach($months as $month)
                                <option value="{{ $month }}" {{ $month===$ecTo1->month ? 'selected':null }}>{{ $month }}月</option>
                            @endforeach
                        </select>
                        <select class="form-control pull-left" name="ec1[to][day]" style="width: 20%;">
                            @foreach($days as $day)
                                <option value="{{ $day }}" {{ $ecTo1->day === $day ? 'selected':null }}>{{ $day }}日</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="clearfix"></div>
                    <div class="form-group mt-4">
                        <label>春季开学, 学生可以在以下时间段选择选修课</label>
                        <div class="clearfix"></div>
                        <select class="form-control pull-left mr-2" name="ec2[from][month]" style="width: 20%;">
                            @foreach($months as $month)
                                <option value="{{ $month }}" {{ $month===$ecFrom2->month ? 'selected':null }}>{{ $month }}月</option>
                            @endforeach
                        </select>
                        <select class="form-control pull-left" name="ec2[from][day]" style="width: 20%;">
                            @foreach($days as $day)
                                <option value="{{ $day }}" {{ $ecFrom2->day === $day ? 'selected':null }}>{{ $day }}日</option>
                            @endforeach
                        </select>
                        <p class="pull-left m-2"> - </p>
                        <select class="form-control pull-left mr-2" name="ec2[to][month]" style="width: 20%;">
                            @foreach($months as $month)
                                <option value="{{ $month }}" {{ $month===$ecTo2->month ? 'selected':null }}>{{ $month }}月</option>
                            @endforeach
                        </select>
                        <select class="form-control pull-left" name="ec2[to][day]" style="width: 20%;">
                            @foreach($days as $day)
                                <option value="{{ $day }}" {{ $ecTo2->day === $day ? 'selected':null }}>{{ $day }}日</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="clearfix"></div>

                    <hr>
                    <div class="form-group">
                        <label for="config-radio">状态</label>&nbsp&nbsp&nbsp&nbsp
                        <input type="radio" class="form-control-radio" id="config-status-radio-close" value="0"  name="config[apply_status]"
                                                       @if($config['apply_status'] == 0) checked @endif> 关闭  &nbsp&nbsp&nbsp&nbsp
                        <input type="radio" class="form-control-radio" id="config-status-radio-open"  value="1"  name="config[apply_status]"
                               @if($config['apply_status'] == 1) checked @endif> 开启


                    </div>

                    <hr>

                    <div class="form-group mt-4">
                        <label>本学年秋季开学时间</label>
                        <div class="clearfix"></div>
                        <select class="form-control pull-left mr-2" name="term_start[term1][month]" style="width: 20%;">
                            @foreach($months as $month)
                                <option value="{{ $month }}" {{ $month===$term1Start->month ? 'selected':null }}>{{ $month }}月</option>
                            @endforeach
                        </select>
                        <select class="form-control pull-left" name="term_start[term1][day]" style="width: 20%;">
                            @foreach($days as $day)
                                <option value="{{ $day }}" {{ $term1Start->day === $day ? 'selected':null }}>{{ $day }}日</option>
                            @endforeach
                        </select>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group mt-4">
                        <label>本学年春季开学时间</label>
                        <div class="clearfix"></div>
                        <select class="form-control pull-left mr-2" name="term_start[term2][month]" style="width: 20%;">
                            @foreach($months as $month)
                                <option value="{{ $month }}" {{ $month===$term2Start->month ? 'selected':null }}>{{ $month }}月</option>
                            @endforeach
                        </select>
                        <select class="form-control pull-left" name="term_start[term2][day]" style="width: 20%;">
                            @foreach($days as $day)
                                <option value="{{ $day }}" {{ $term2Start->day === $day ? 'selected':null }}>{{ $day }}日</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="clearfix"></div>
                    <hr>

                    {{--<div class="form-group mt-4">
                        <label>夏季/秋季作息时间开始日期</label>
                        <div class="clearfix"></div>
                        <select class="form-control pull-left mr-2" name="summer_start_date[month]" style="width: 20%;">
                            @foreach($months as $month)
                                <option value="{{ $month }}" {{ $month===$summerStart->month ? 'selected':null }}>{{ $month }}月</option>
                            @endforeach
                        </select>
                        <select class="form-control pull-left" name="summer_start_date[day]" style="width: 20%;">
                            @foreach($days as $day)
                                <option value="{{ $day }}" {{ $summerStart->day === $day ? 'selected':null }}>{{ $day }}日</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="clearfix"></div>
                    <hr>

                    <div class="form-group mt-4">
                        <label>冬季/春季作息时间开始日期</label>
                        <div class="clearfix"></div>
                        <select class="form-control pull-left mr-2" name="winter_start_date[month]" style="width: 20%;">
                            @foreach($months as $month)
                                <option value="{{ $month }}" {{ $month===$winterStart->month ? 'selected':null }}>{{ $month }}月</option>
                            @endforeach
                        </select>
                        <select class="form-control pull-left" name="winter_start_date[day]" style="width: 20%;">
                            @foreach($days as $day)
                                <option value="{{ $day }}" {{ $winterStart->day === $day ? 'selected':null }}>{{ $day }}日</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="clearfix"></div>
                    <hr>--}}
                    <?php
                    Button::Print(['id'=>'btn-save-school-config','text'=>trans('general.submit')], Button::TYPE_PRIMARY);
                    ?>&nbsp;
                </form>
            </div>
        </div>
    </div>
    <div class="col-8">
        <div class="card">
            <time-slots-manager
                    school="{{ $school->uuid }}"
                    schoolid="{{ $school->id }}"
                    v-on:edit-time-slot="editTimeSlotHandler"
            ></time-slots-manager>
        </div>
    </div>
    <el-drawer :title="mode == 'add'?'添加时间':'编辑时间'" :visible.sync="showEditForm">
        <div class="pr-4">
            <el-form ref="form" :model="currentTimeSlot" label-width="80px">
                <el-form-item label="年级" v-if="mode == 'add'" >
                    <el-select v-model="currentTimeSlot.grade_id" style="width:100%;">
                        <el-option :label="grade.text" :value="grade.year" :key="grade.year" v-for="grade in grades"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="名称">
                    <el-input v-model="currentTimeSlot.name"></el-input>
                </el-form-item>
                <el-form-item label="时间">
                    <el-time-picker
                            style="width:49%;"
                            v-model="currentTimeSlot.from"
                            format="HH:mm"
                            value-format="HH:mm"
                            :picker-options="{selectableRange: '05:00:00 - 22:00:00'}"
                            placeholder="开始时间">
                    </el-time-picker>
                    <el-time-picker
                            arrow-control
                            style="width:49%;"
                            v-model="currentTimeSlot.to"
                            format="HH:mm"
                            value-format="HH:mm"
                            :picker-options="{selectableRange: '05:00:00 - 22:00:00'}"
                            @change="toChangedHandler"
                            placeholder="截止时间">
                    </el-time-picker>
                </el-form-item>
                <el-form-item label="类型">
                    <el-select v-model="currentTimeSlot.type" placeholder="请选择" style="width:100%;">
                        @foreach(\App\Models\Timetable\TimeSlot::AllTypes() as $key=>$value)
                        <el-option
                                :key="{{ $key }}"
                                label="{{ $value }}"
                                :value="{{ $key }}">
                        </el-option>
                        @endforeach
                    </el-select>
                </el-form-item>
                <el-form-item label="状态" v-if="mode == 'edit'">
                    <el-switch
                    v-model="currentTimeSlot.status">
                    </el-switch>
                </el-form-item>
                <el-form-item v-if="mode == 'edit'">
                    <p><span style="color:red;font-size: 22px;">* &nbsp; </span>&nbsp;注: 已关联课程表，不可删除</p>
                </el-form-item>
                <el-form-item style="text-align: center;
    margin-top: 200px;">
                    <el-button type="primary" @click="onSubmit" style="padding: 13px 39px;">保存</el-button>
                    <el-button v-if="mode == 'edit'" type="danger" @click="deleteItem" style="padding: 13px 39px;">删除</el-button>
                </el-form-item>
            </el-form>
        </div>
    </el-drawer>
</div>
