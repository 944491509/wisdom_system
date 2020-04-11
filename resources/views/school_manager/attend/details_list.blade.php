@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card-box">
                <div class="card-head">

                </div>
                <div class="card-body">
                    <div class="row">

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column valign-middle text-center">
                                <thead>
                                <tr>
                                    <th>序号</th>
                                    <th>课程</th>
                                    <th>课节</th>
                                    <th>老师</th>
                                    <th>学周</th>
                                    <th>星期</th>
                                    <th>状态</th>
                                    <th>分数</th>
                                    <th>备注</th>

                                </tr>
                                </thead>
                                <tbody>
                                @foreach($list as $key => $item)
                                <tr>
                                    <td>{{ $key +1 }}</td>
                                    <td>{{ $item->course->name }}</td>
                                    <td> {{ $item->timetable->timeSlot->name }}</td>
                                    <td>{{ $item->timetable->teacher->name }}</td>
                                    <td>{{ $item->week }}学周</td>
                                    <td>{{ $item->getWeekIndex() }}</td>
                                    <td>{{ $item->getMold() }}</td>
                                    <td>{{ $item->score }}</td>
                                    <td>{{ $item->remark }}</td>
                                </tr>
                                @endforeach


                                </tbody>
                            </table>
                            {{ $list->links()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection