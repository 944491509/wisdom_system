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
                    <header>已开通学校系统</header>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="row table-padding">
                            <div class="col-md-6 col-sm-6 col-6">
                                <a href="{{ route('admin.schools.add') }}" class="btn btn-primary">
                                    创建新学校 <i class="fa fa-plus"></i>
                                </a>
                            </div>
                            <div class="col-md-6 col-sm-6 col-6">
                                <a href="{{ route('admin.admin.list') }}" class="btn btn-primary">
                                    平台管理员
                                </a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table
                                    class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>学校名称</th>
                                    <th>最多学生账户数</th>
                                    <th>最多教工账户数</th>
                                    {{--<th>学校管理员</th>--}}
                                    <th>最后修改</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($schools as $index=>$school)
                                    <tr>
                                        <td>{{ $index+1 }}</td>
                                        <td>
                                            <a href="{{ route('operator.schools.enter',['uuid'=>$school->uuid]) }}">{{ $school->name }}</a>
                                        </td>
                                        <td>{{ $school->max_students_number > 0 ? $school->max_students_number : '不限' }}</td>
                                        <td>{{ $school->max_employees_number > 0 ? $school->max_employees_number : '不限' }}</td>
                                        {{--<td>--}}
                                            {{--@foreach($school->schoolManagers as $manager)--}}
                                                {{--<a href="{{ route('admin.edit.school-manager',['school'=>$school->uuid, 'user'=>$manager->user->uuid]) }}">{{ $manager->user->name }}</a>--}}
                                            {{--@endforeach--}}
                                            {{--@if(count($school->schoolManagers) === 0)--}}
                                                {{--<a href="{{ route('admin.create.school-manager',['school'=>$school->uuid]) }}">创建学校的管理员账户</a>--}}
                                            {{--@endif--}}
                                        {{--</td>--}}
                                        <td>{{ $school->last_updated_by ? $school->lastUpdatedBy->mobile : '超级管理员' }} {{ $school->updated_at }}</td>
                                        <td class="text-center">
{{--                                            {{ Anchor::Print(['text'=>'管理员','href'=>route('admin.list.school-manager',['school_id'=>$school->id])], Button::TYPE_PRIMARY,'arrow-circle-o-right') }}--}}
                                            {{ Anchor::Print(['text'=>'编辑','href'=>route('admin.schools.edit',['uuid'=>$school->uuid])], Button::TYPE_DEFAULT,'edit') }}
                                            {{ Anchor::Print(['text'=>'进入','href'=>route('operator.schools.enter',['uuid'=>$school->uuid])], Button::TYPE_SUCCESS,'arrow-circle-o-right') }}
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
