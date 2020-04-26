<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
use App\User;
?>
@extends('layouts.app')
@section('content')
 <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="table-responsive">
                            <table
                                    class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                                <thead>
                                <tr style="text-align: center">
                                    <th>序号</th>
                                    <th>姓名</th>
                                    <th>身份</th>
                                    <th>是否使用</th>
                                    <th>使用次数</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($list as $key => $val)
                                    <tr style="text-align: center">
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $val->user->name }}</td>
                                        <td>{{ \App\Models\Acl\Role::AllNames()[\App\Models\Acl\Role::GetRoleSlugByUserType($val->user_type)] }}</td>
                                        <td>是</td>
                                        <td>0 次</td>
                                        <td>  {{ \App\Utils\UI\Anchor::Print(['text'=>'获取二维码','class'=>'btn-edit-evaluate','href'=> '#'], \App\Utils\UI\Button::TYPE_DEFAULT,'edit') }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $list->links() }}
                    </div>
                </div>
            </div>
        </div>
@endsection
