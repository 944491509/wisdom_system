@extends('layouts.app')
@section('content')
    <div class="col-sm-12 col-md-12 col-xl-12">
        <div class="card">

            <div class="card-body">
                <div class="row">
                    <div class="row table-padding">
                        <div class="col-md-6 col-sm-6 col-6">
                            <a href="{{ route('school_manager.facility.add') }}" class="btn btn-primary">
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
                                <th>账号</th>
                                <th>姓名</th>
                                <th>类型</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list as $key => $item)
                            <tr>
                                <td>{{ $key +1 }}</td>
                                <td>{{ $item->user->mobile }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->userTypeText() }}</td>
                                <td class="text-center">
                                    {{ \App\Utils\UI\Anchor::Print(['text'=>'编辑','class'=>'btn-edit-facility','href'=>route('school_manager.facility.edit',['id'=>$item->id])], \App\Utils\UI\Button::TYPE_DEFAULT,'edit') }}
                                </td>
                            </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                    {{ $list->appends(['school_id'=>$school_id])->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection