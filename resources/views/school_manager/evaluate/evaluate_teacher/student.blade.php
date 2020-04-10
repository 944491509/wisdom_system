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

                <div class="card-body">
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                                <thead>
                                <tr>
                                    <th>序号</th>
                                    <th>学号</th>
                                    <th>头像</th>
                                    <th>姓名</th>
                                    <th>性别</th>
                                    <th>联系电话</th>
                                    <th>分数</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($students as $index=>$gradeUser)

                                    <tr>
                                        <td>{{ $index +1 }}</td>
                                        <td>{{ $gradeUser->profile->serial_number }}</td>
                                        <td class="text-center">
                                            <img src="{{ $gradeUser->profile->avatar }}" style="width: 60px;border-radius: 50%;">
                                        </td>
                                        <td>{{ $gradeUser->user->name ?? 'n.a' }}</td>
                                        <td>{{ $gradeUser->profile->getGenderTextAttribute() }}</td>
                                        <td>
                                            {{ $gradeUser->user->mobile }}
                                            {{ $gradeUser->user->getStatusText() }}
                                        </td>
                                        <td>{{ $gradeUser->score }}</td>
                                        <td class="text-center">
                                            {{ \App\Utils\UI\Anchor::Print(['text'=>'查看','class'=>'btn-edit-evaluate','href'=>route('school_manager.evaluate.record-list',['evaluate_student_id'=>$gradeUser->id])], \App\Utils\UI\Button::TYPE_DEFAULT,'edit') }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            {{ $students->links() }}

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
