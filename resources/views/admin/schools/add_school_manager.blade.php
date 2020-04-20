<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
?>

@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card">
                <div class="card-head">
                    <header>学校管理员账户: {{ $school->name }}</header>
                </div>
                <div class="card-body " id="bar-parent">
                    <form action="{{ route('admin.create.school-manager') }}" method="post">
                        <div class="form-group">
                            <label>登陆账户名</label>
                            <input required type="text" class="form-control" value="" placeholder="必填: 登陆账号" name="user[mobile]">
                        </div>
                        <div class="form-group">
                            <label>登陆密码</label>
                            <input required type="password" class="form-control" value="" placeholder="登陆密码, 必填" name="user[password]">
                        </div>
                        @include('admin.schools._form')
                        <?php
                        Button::Print(['id'=>'btnSubmit','text'=>trans('general.submit')], Button::TYPE_PRIMARY);
                        ?>&nbsp;
                        <?php
                        Anchor::Print(['text'=>trans('general.return'),'href'=>route('admin.list.school-manager',['school_id'=>$school->id]),'class'=>'pull-right'], Button::TYPE_SUCCESS,'arrow-circle-o-right')
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection