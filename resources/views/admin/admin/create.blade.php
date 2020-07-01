<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
?>
@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card">
                {{--此处是为了欺骗浏览器--}}
                <form style="display:none">
                    <input type="password"/>
                </form>
                <input type="password" style="width:0;height:0;float:left;visibility:hidden"/>
                <div class="card-body " id="bar-parent">
                    <form action="{{ route('admin.admin.create') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label>姓名</label>
                            <input required type="text" class="form-control" value=""  placeholder="姓名" name="name" AUTOCOMPLETE="OFF">
                        </div>
                        <div class="form-group">
                            <label>登录账号</label>
                            <input required type="text" class="form-control"  placeholder="登陆账号" name="mobile" AUTOCOMPLETE="OFF">
                        </div>
                        <div class="form-group">
                            <label>登录密码</label>
                            <input required type="password" class="form-control" value="" placeholder="登陆密码" name="password">
                        </div>

                        <?php
                        Button::Print(['id'=>'btnSubmit','text'=>trans('general.submit')], Button::TYPE_PRIMARY);
                        ?>&nbsp;
                        <?php
                        Anchor::Print(['text'=>trans('general.return'),'href'=>route('admin.admin.list'),'class'=>'pull-right'], Button::TYPE_SUCCESS,'arrow-circle-o-right')
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
