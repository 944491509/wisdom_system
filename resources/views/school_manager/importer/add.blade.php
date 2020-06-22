<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
?>

@extends('layouts.app')
@section('content')
    <div id="school-importer-student">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-xl-12">
                <div class="card-box">
                    <div class="card-head">
                        <header>创建新导入任务</header>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('school_manager.importer.save') }}" method="post" id="add-task-form"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="school-input">导入方式</label>
                                <select class="form-control" name="task[type]" required v-model="type">
                                    <option value="0">无专业班级数据</option>
                                    <option value="1">带专业班级</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="task-title-input">任务名称</label>
                                <input required type="text" class="form-control" id="task-title-input"
                                       placeholder="任务名称" name="task[title]">
                            </div>
                            <div class="form-group">
                                <label for="task-file-input">选择文件</label>
                                <input id="file" type="file" class="form-control" name="source" required>
                            </div>
                            <?php
                        Button::Print(['id'=>'btn-create-questionnaire','text'=>trans('general.submit')], Button::TYPE_PRIMARY);
                        ?>&nbsp;
                        <?php
                        Anchor::Print(['text'=>trans('general.return'),'href'=>route('school_manager.importer.manager'),'class'=>'pull-right link-return'], Button::TYPE_SUCCESS,'arrow-circle-o-right')
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>

@endsection
