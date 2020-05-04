<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
?>
@extends('layouts.app')
@section('content')
    <div class="row" id="enrol-note-manager-app">
        <div class="col-sm-10 col-md-12 col-xl-12">
            <div class="card-box">
                <div class="card-head">
                    <header>添加</header>
                </div>
                <div class="card-body">
                    <form action="{{ route('manager_wifi.wifiNotice.add') }}" method="post"  id="add-building-form">
                        @csrf
                        <div class="form-group">
                            <label for="building-name-input">标题</label>
                            <input required type="text" class="form-control" id="building-name-input" value="{{ old('infos.notice_title') }}" placeholder="" name="infos[notice_title]">
                        </div>
                        <div class="form-group">
                            <label for="building-name-input">内容</label>
                            <Redactor v-model="content" placeholder="请填写内容" :config="configOptions" name="infos[notice_content]" />
                            @{{ content }}
                        </div>
                        <?php
                            Button::Print(['id'=>'btn-create-building','text'=>trans('general.submit')], Button::TYPE_PRIMARY);
                        ?>&nbsp;
                        <?php
                            Anchor::Print(['text'=>trans('general.return'),'href'=>url()->previous(),'class'=>'pull-right link-return'], Button::TYPE_SUCCESS,'arrow-circle-o-right')
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
