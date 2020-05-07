
@extends('layouts.app')
@section('content')
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card">
                <div class="card-head">
                    <header>开通设置</header>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="row table-padding">
                            <div class="col-md-6 col-sm-6 col-6">
                                <a href="{{ route('teacher.code.add') }}" class="btn btn-primary">
                                    创建 <i class="fa fa-plus"></i>
                                </a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table
                                    class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                                <thead>
                                <tr>
                                    <th>序号</th>
                                    <th>设备名称</th>
                                    <th>设备型号</th>
                                    <th>状态</th>
                                    <th>添加时间</th>
                                    <th>修改时间</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($list as $key => $val)
                                        <tr>
                                            <td>{{ $key +1 }}</td>
                                            <td> {{ $val->title }}</td>
                                            <td> {{ $val->type }}</td>
                                            @if($val->status == 0)
                                            <td>关闭</td>
                                            @else
                                            <td>正常</td>
                                            @endif
                                            <td> {{ $val->created_at }}</td>
                                            <td> {{ $val->updated_at }}</td>
                                            <td class="text-center">
                                            {{ \App\Utils\UI\Anchor::Print(['text'=>'编辑','class'=>'btn-edit-evaluate','href'=>route('teacher.code.edit',['id'=>$val->id])], \App\Utils\UI\Button::TYPE_DEFAULT,'edit') }}
                                        </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $list->links() }}

                    </div>
                </div>
            </div>
        </div>
@endsection
