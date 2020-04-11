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
                                <th>姓名</th>
                                <th>签到</th>
                                <th>请假</th>
                                <th>旷课</th>
                                <th>操作</th>

                            </tr>
                            </thead>
                            <tbody>
                                @foreach($list as $key => $item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item['username'] }}</td>
                                        <td>{{ $item['signIn_num'] }}</td>
                                        <td>{{ $item['leave_num'] }}</td>
                                        <td>{{ $item['truant_num'] }}</td>
                                        <td>{{ \App\Utils\UI\Anchor::Print(['text'=>'查看','class'=>'btn-edit-evaluate','href'=>route('school_manager.students.attend.details-list',['year'=>$year,'term'=>$term, 'user_id'=>$item['user_id']])], \App\Utils\UI\Button::TYPE_DEFAULT,'edit') }}</td>
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