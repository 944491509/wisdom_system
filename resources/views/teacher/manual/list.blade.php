@extends('layouts.app')
@section('content')
    <div class="col-sm-6">
        <div class="card card-topline-lightblue">
            <div class="card-head">
                <header>手册列表</header>
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
                                    <td>{{ $item['filename'] }}</td>
                                    <td>
                                        <button type="button"
                                                class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect m-b-10 btn-circle btn-primary">
                                            <a href=" {{route('teacher.manual.download',['manual_id'=>$key])}} "><div style="color: #FFFFFF"> 下   载 </div></a></button>
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