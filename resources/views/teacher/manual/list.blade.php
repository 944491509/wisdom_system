@extends('layouts.app')
@section('content')
    <div class="col-sm-6">
        <div class="card card-topline-green">
            <div class="card-head">
                <header>手册列表</header>
                {{--<div class="tools">--}}
                    {{--<a class="fa fa-repeat btn-color box-refresh" href="javascript:;"></a>--}}
                    {{--<a class="t-collapse btn-color fa fa-chevron-down"--}}
                       {{--href="javascript:;"></a>--}}
                    {{--<a class="t-close btn-color fa fa-times" href="javascript:;"></a>--}}
                {{--</div>--}}
            </div>
            <div class="card-body ">
                <div class="table-scrollable">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>资料名称</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($manual as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $item['name'] }}</td>
                                    <td>
                                        <button type="button"
                                                class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect m-b-10 btn-circle btn-success"
                                                disabled><a href="{{ $item['url'] }}" download="{{ $item['val'] }}">下载</a></button>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection