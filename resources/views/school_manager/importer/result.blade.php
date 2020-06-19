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
                    <header>结果展示</header>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                                <thead>
                                <tr class="center">
                                    <th>#</th>
                                    <th>表格行数</th>
                                    <th>姓名</th>
                                    <th>身份证</th>
                                    <th>未导入原因</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($messages) == 0)
                                    <tr>
                                        <td colspan="5">还没有内容 </td>
                                    </tr>
                                @endif
                                @foreach($messages as $index=>$info)
                                    <tr class="center">
                                        <td>{{ $index+1 }}</td>
                                        <td>{{ $info->number }}</td>
                                        <td>{{ $info->name }}</td>
                                        <td>{{ $info->id_number }}</td>
                                        <td>{{ $info->error_log }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <?php
                                Anchor::Print(['text'=>trans('general.return'),'href'=>route('school_manager.importer.manager'),'class'=>'pull-right link-return'], Button::TYPE_SUCCESS,'arrow-circle-o-right')
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
