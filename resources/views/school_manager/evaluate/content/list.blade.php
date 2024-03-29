
@extends('layouts.app')
@section('content')
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card">
                <div class="card-head">
                    <header>评价模板</header>
                </div>

                <div class="card-body" id="evaluateContentList">
                    <div class="row">
                        <div class="row table-padding">
                            <div class="col-md-6 col-sm-6 col-6">
                                <a href="{{ route('school_manager.evaluate.content-create') }}" class="btn btn-primary">
                                    创建 <i class="fa fa-plus"></i>
                                </a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table
                                    class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>标题</th>
                                    <th>分值</th>
                                    <th>对谁评价</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($list as $key => $val)
                                        <tr>
                                            <td>{{ $key +1 }}</td>
                                            <td> {{ $val->title }}</td>
                                            <td> {{ $val->score }}</td>
                                            <td> {{ $val->typeText() }}</td>
                                            <td> {{ $val->created_at }}</td>
                                            <td class="text-center">
                                            {{ \App\Utils\UI\Anchor::Print(['text'=>'编辑','class'=>'btn-edit-evaluate','href'=>route('school_manager.evaluate.content-edit',['id'=>$val->id])], \App\Utils\UI\Button::TYPE_DEFAULT,'edit') }}
                                            {{-- {{ \App\Utils\UI\Anchor::Print(['text'=>'删除','class'=>'btn-delete-evaluate btn-need-confirm','href'=>route('school_manager.evaluate.content-delete',['id'=>$val->id])], \App\Utils\UI\Button::TYPE_DANGER,'trash') }} --}}
                                            <a  href="javascript:void(0)" class="btn btn-round btn-danger" itemid="{{$val['id']}}"  @click="deleteItem" class="btn btn-round btn-danger"><i class="fa fa-trash"></i>删除</a>
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
    </div>
@endsection
