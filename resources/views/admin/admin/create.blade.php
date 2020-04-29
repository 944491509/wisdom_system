<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
?>
@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card">
                <div class="card-body " id="bar-parent">
                    <form action="{{ route('admin.admin.create') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label>姓名</label>
                            <input required type="text" class="form-control" value="" placeholder="姓名" name="name">
                        </div>
                        <div class="form-group">
                            <label>登陆账号</label>
                            <input required type="text" class="form-control" value="" placeholder="登陆账号" name="mobile">
                        </div>
                        <div class="form-group">
                            <label>登陆密码</label>
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