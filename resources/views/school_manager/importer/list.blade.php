<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
?>

@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card-box">
                <div class="card-head">
                    <header>导入管理</header>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="table-padding col-12">
                            <a href="{{ route('school_manager.importer.add') }}" class="btn btn-primary " id="btn-create-versions-from">
                                创建导入任务 <i class="fa fa-plus"></i>
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                                <thead>
                                <tr class="text-center">
                                    <th>#</th>
                                    <th>任务名</th>
                                    <th>文件信息</th>
                                    <th>文件路径</th>
                                    <th>状态</th>
                                    <th>创建时间</th>
                                    <th>已导入</th>
                                    <th>未导入</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($tasks) == 0)
                                    <tr>
                                        <td colspan="6">还没有内容 </td>
                                    </tr>
                                @endif
                                @foreach($tasks as $index=>$task)
                                    <tr class="text-center">
                                        <td>{{ $task->id }}</td>
                                        <td>{{ $task->title }}</td>
                                        <td>{{ $task->file_name }}</td>
                                        <td>{{ $task->path }}</td>
                                        <td>{{ $task->getStatus() }}</td>
                                        <td>{{ $task->created_at->format('Y-m-d H:i') }}</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>
                                            {{ Anchor::Print(['text'=>'结果','class'=>'btn-edit-building','href'=>route('school_manager.importer.result',['id'=>$task->id])], Button::TYPE_DEFAULT,'result') }}
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
