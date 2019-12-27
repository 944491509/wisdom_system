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
                    <header>{{ session('school.name') }} 考勤管理</header>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                                <thead>
                                <tr>
                                    <th>创建时间</th>
                                    <th>学校</th>
                                    <th>组名称</th>
                                    <th style="width: 30%">组成员</th>
                                    <th>上班时间</th>
                                    <th>下班时间</th>
                                    <th>迟到时间</th>
                                    <th>严重迟到时间</th>
                                    <th>wifi名称</th>
                                    <th>状态</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($groups as $index=>$group)
                                    <tr>
                                        <td>{{ _printDate($group->created_at) }}</td>
                                        <td>
                                            {{ $group->school->name }}
                                        </td>
                                        <td>
                                            {{ $group->name }}
                                        </td>
                                        <td size="">
                                            @foreach($group->members as $member)
<span class="text-primary m-2"><a href="{{route('school_manager.oa.attendances-del-member',['id'=>$member->user_id,'group'=>$group->id])}}" class="btn-need-confirm">{{ $member->user->name }} </a></span>
                                            @endforeach
                                        </td>
                                        <td>
                                            {{ $group->online_time }}
                                        </td>
                                        <td>
                                            {{ $group->offline_time }}
                                        </td>
                                        <td>
                                            {{ $group->late_duration }}
                                        </td>
                                        <td>
                                            {{ $group->serious_late_duration }}
                                        </td>
                                        <td>
                                            {{ $group->wifi_name }}
                                        </td>
                                        <td class="text-center">
                                            {{ Anchor::Print(['text'=>'查看','class'=>'btn-edit-major','href'=>route('school_manager.oa.attendances-group',['id'=>$group->id])], Button::TYPE_DEFAULT,'edit') }}
                                            {{ Anchor::Print(['text'=>'添加成员','class'=>'btn-edit-major','href'=>route('school_manager.oa.attendances-members',['id'=>$group->id])], Button::TYPE_DEFAULT,'edit') }}
                                            {{ Anchor::Print(['text'=>'补卡处理','class'=>'btn-edit-major','href'=>route('school_manager.oa.attendances-messages')], Button::TYPE_DEFAULT,'edit') }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            {{ $groups->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection