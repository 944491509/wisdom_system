
@extends('layouts.app')
@section('content')
    <div class="col-sm-12 col-md-12 col-xl-12">
        <div class="card">
            <div class="card-head">
                <header>资料列表</header>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="table-responsive">
                        <table
                            class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                            <thead>
                            <tr>
                                <th></th>
                                <th>课程名称</th>
                                <th>教师</th>
                                <th>文件名称</th>
                                <th>上传时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list as $key => $item)
                            <tr>
                                <td>{{ $key +1 }}</td>
                                <td>{{ $item->course->name }}</td>
                                <td>{{ $item->teacher->name }}</td>
                                <td>{{ $item->lectureMaterials[0]->description ??'' }}</td>
                                <td>{{ $item->created_at }}</td>
                                <td>
                                    <button type="button"
                                            class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect m-b-10 btn-circle btn-primary">
                                        <a href="{{$item->lectureMaterials[0]->url ?? ''}}"><div style="color: #FFFFFF"> 下   载 </div></a></button>
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
