@extends('layouts.app')
@section('content')
  <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card">
                <div class="card-head">
                    <header>申请列表</header>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="table-responsive">
                            <table
                                    class="table table-striped table-bordered
                                    table-hover table-checkable order-column
                                    valign-middle">
                                <thead>
                                <tr>
                                    @if ($list_type == 2)
                                    <th>序号</th>
                                    <th>姓名</th>
                                    <th>性别</th>
                                    <th>学院</th>
                                    <th>专业</th>
                                    <th>班级</th>
                                    <th>类型</th>
                                    <th>申请时间</th>
                                    <th>状态</th>
                                    @endif

                                    @if ($list_type == 1)
                                        <th>序号</th>
                                        <th>姓名</th>
                                        <th>类型</th>
                                        <th>申请时间</th>
                                        <th>状态</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($list as $key => $val)
                                    <tr>
                                        @if ($list_type == 2)
                                        <td>{{ $val->id }}</td>
                                        <td>{{ $val->user->name }}</td>
                                        <td>@if($val->user->profile->gender == 1)男@endif
                                            @if($val->user->profile->gender == 2)女@endif</td>

                                        <td>{{ $val->user->gradeUser->institute->name ?? '' }}</td>
                                        <td>{{ $val->user->gradeUser->major->name ?? '' }}</td>
                                        <td>{{ $val->user->gradeUser->grade->name ?? '' }}</td>
                                        <td>{{ $val->flow->name }}</td>
                                        <td>{{ $val->created_at }}</td>
                                        <td>
                                            @if($val->done == \App\Utils\Pipeline\IUserFlow::IN_PROGRESS)<span class="text-warning">审核中</span>@endif
                                            @if($val->done == \App\Utils\Pipeline\IUserFlow::DONE)<span class="text-success">已通过</span>@endif
                                            @if($val->done == \App\Utils\Pipeline\IUserFlow::TERMINATED)<span class="text-danger">被拒绝</span>@endif
                                            @if($val->done == \App\Utils\Pipeline\IUserFlow::REVOKE)<span class="">已撤销</span>@endif
                                        </td>
                                        @endif

                                        @if ($list_type == 1)
                                            <td>{{ $val->id }}</td>
                                            <td>{{ $val->user->name }}</td>
                                            <td>{{ $val->flow->name }}</td>
                                            <td>{{ $val->created_at }}</td>
                                            <td>
                                                @if($val->done == \App\Utils\Pipeline\IUserFlow::IN_PROGRESS)<span class="text-warning">审核中</span>@endif
                                                @if($val->done == \App\Utils\Pipeline\IUserFlow::DONE)<span class="text-success">已通过</span>@endif
                                                @if($val->done == \App\Utils\Pipeline\IUserFlow::TERMINATED)<span class="text-danger">被拒绝</span>@endif
                                                @if($val->done == \App\Utils\Pipeline\IUserFlow::REVOKE)<span class="">已撤销</span>@endif
                                            </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                             {{ $list->appends(['position' => $list_type])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
