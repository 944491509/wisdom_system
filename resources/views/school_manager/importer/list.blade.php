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
                    <header>导入管理 </header>
                    <a href="{{ route('school_manager.importer.download', ['type' => \App\Models\Importer\ImportTask::IMPORT_TASK_EXECUTION]) }}">下载新生导入模板</a>
                    <a href="{{ route('school_manager.importer.download', ['type' => \App\Models\Importer\ImportTask::IMPORT_TYPE_NO_IDENTITY]) }}">下载未认证用户导入模板</a>
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
                                        <td>{{ $task->total }}</td>
                                        <td>{{ $task->surplus }}</td>
                                        <td>
                                            @if ($task->status == \App\Models\Importer\ImportTask::IMPORT_TASK_WAITING)
                                                {{ Anchor::Print(['text'=>'撤回','class'=>'btn-edit-building','href'=>route('school_manager.importer.withdraw',['id' => $task->id])], Button::TYPE_WARNING,'result') }}
                                            @elseif($task->status == \App\Models\Importer\ImportTask::IMPORT_TASK_WITHDRAW)
                                                {{ Anchor::Print(['text'=>'已撤回','class'=>'btn-edit-building','href'=> '#'], Button::TYPE_INFO,'result') }}
                                            @else
                                                {{ Anchor::Print(['text'=>'错误详情','class'=>'btn-edit-building','href'=>route('school_manager.importer.result',['id' => $task->id])], Button::TYPE_DEFAULT,'result') }}
                                            @endif
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
