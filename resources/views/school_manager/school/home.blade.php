@php
@endphp
@extends('layouts.app')
@section('content')
    <!-- <div class="row">
        <div class="col-sm-12 col-md-9 col-lg-9 col-xl-8">
            <div class="card">
                <div class="card-head">
                    <header>
                        {{ session('school.name') }}
                    </header>
                </div>
            </div>
        </div>
    </div> -->
    <div class="content" id="home_content">
        <div class ="inner">
            <img src="/images/logo.png"/>
            <h1 class="system_title">智慧职专管理系统</h1>
            <h3 class="footer_text"><span>技术支持<span>&nbsp;&nbsp;&nbsp;<span>北京帕菲特通信技术有限公司</span></h3>
        <div>
    </div>
    <script>
    (function (){
        let home_content = document.getElementById('home_content');
        console.log(home_content)
        let classList = home_content.parentElement.classList;
        let classname =home_content.parentElement.className;
        if(!classList.contains('flex-column')) home_content.parentElement.className = classname +' flex-column';
    })()
    </script>
@endsection

<style scoped lang="less">
    .flex-column{
        display: flex;
        flex-direction: column;
    }
    .content {
        background: url(/images/background.png) no-repeat;
        background-size:100% 100%;
        flex:1;
        display:flex;
        justify-content: center;
        align-items: center;
        position: relative;
    }
    .content .inner{
        text-align:center;
    }
    .content .inner img{
        width:191px;
        height:91px;
    }
    .content .inner .system_title{
        font-size:46px;
        font-weight:400;
        color:rgba(255,255,255,1);
        line-height:65px;
        letter-spacing:13px;
    }
    .content .inner .footer_text{
        font-size:20px;
        font-weight:400;
        color:rgba(255,255,255,1);
        line-height:28px;
        letter-spacing:2px;
        position: absolute;
        bottom: 45px;
        transform: translateX(-25px);
    }
</style>
