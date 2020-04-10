@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card-box">
                <div class="card-head">

                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <form action="{{ route('school_manager.students.attend.grade-list') }}" method="get"  >
                                <div class="pull-left col-2">
                                    <label>时间</label>
                                    <select class="el-input__inner el-col-20" id="year_term">
                                        <option value="">请选择</option>
                                        @foreach($school_year as $key => $item)
                                            <option year="{{$item['year']}}" term="{{$item['term']}}"
        @if($item['term'] == $term && $item['year'] == $year)
            selected
         @endif
            >{{ $item['name'] }}</option>
                                            @endforeach
                                    </select>
                                    <input type="hidden" id='year' name="year" value="">
                                    <input type="hidden" id='term' name="term" value="">
                                </div>


                                <button  type="submit" class="btn btn-primary">搜索</button>
                            </form>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column valign-middle text-center">
                                <thead>
                                <tr>
                                    <th>序号</th>
                                    <th>学年</th>
                                    <th>学期</th>
                                    <th>班级</th>
                                    <th>班主任</th>
                                    <th>操作</th>

                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($list as $key => $item)
                                        <tr>
                                            <td>{{ $key +1 }}</td>
                                            <td>{{ $year }}学年</td>
                                            <td>{{ $term }}</td>
                                            <td>{{ $item->grade->name }}</td>
                                            <td>@if($item->grade->gradeManager)
                                                    {{ $item->grade->gradeManager->adviser_name }}
                                            @endif </td>
                                            <td>{{ \App\Utils\UI\Anchor::Print(['text'=>'查看','class'=>'btn-edit-evaluate','href'=>route('school_manager.evaluate.grade.list',[])], \App\Utils\UI\Button::TYPE_DEFAULT,'edit') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection