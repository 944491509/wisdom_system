
@extends('layouts.app')
@section('content')
     <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card">
                <div class="card-head">
                    <header>学生评教列表</header>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="table-responsive">
                            <table
                                    class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                                <thead>
                                <tr>
                                    <th>序号</th>
                                    <th>题目</th>
                                    <th>总分</th>
                                    <th>分数</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($list as $key => $val)
                                    <tr>
                                        <td>{{ $key +1 }}</td>
                                        <td>{{ $val->evaluate->title }}</td>
                                        <td>{{ $val->evaluate->score }}</td>
                                        <td>{{ $val->score }}</td>
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
