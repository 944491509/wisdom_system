<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
use App\User;
?>
@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card">
                <div class="card-head">
                    <header class="full-width">
                        <span class="pull-left pt-2">{{ $parent->name??session('school.name') }} 教职工列表: (总数: )</span>
                        <a href="{{ route('school_manager.teachers.add-new') }}" class="btn btn-primary pull-right">
                            添加新教职工 <i class="fa fa-plus"></i>
                        </a>
                        <a href="{{ route('school_manager.teachers.export') }}" class="btn btn-primary pull-right">
                            导出教职工 <i class="fa fa-plus"></i>
                        </a>
                    </header>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="table-padding col-12">
                            @include('school_manager.school.reusable.nav',['highlight'=>'teacher'])
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                                <thead>
                                <tr>
                                    <th>是否聘用</th>
                                    <th>姓名</th>
                                    <th>头像</th>
                                    <th>行政职务</th>
                                    <th>教学职务</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td>
                                            <img style="width: 60px;border-radius: 50%;" src="" alt="">
                                        </td>
                                        <td>

                                        </td>
                                        <td>

                                        </td>
                                        <td class="text-center">
                                            {{ Anchor::Print(['text'=>'照片','href'=>route('school_manager.teachers.edit-avatar',['uuid'=>'user_id'])], Button::TYPE_DEFAULT,'picture-o') }}
                                            {{ Anchor::Print(['text'=>'档案管理','href'=>route('school_manager.teachers.edit-profile',['uuid'=>'user_id'])], Button::TYPE_DEFAULT,'edit') }}
                                            {{ Anchor::Print(['text'=>'修改密码','href'=>route('teacher.profile.update-password',['uuid'=>'user_id'])], Button::TYPE_DEFAULT,'key') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
