@extends('layouts.app')
@section('content')
    <script>
        window.onload = function(){
            function getQueryString(name) {
                var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
                var r = window.location.search.substr(1).match(reg);
                if (r != null) return unescape(r[2]); return null;
            }
            var notice_id = getQueryString('notice_id')
            if(notice_id){
                window.axios.get('/api/notification/news-info?notice_id='+notice_id).then(res=>{
                    if(res.data.code !== 1000){
                        alert(res.data.message)
                    }else{
                        var title = res.data.data.title
                        var content = res.data.data.content
                        var created_at = res.data.data.created_at
                        document.getElementById('notice-title').innerText = title
                        document.getElementById('notice-created_at').innerText = created_at
                        document.getElementById('notice-content').innerHTML = content
                    }
                })
            }
        }
    </script>
    <div>
        <div style="width: 1000px;margin: 0 auto;">
            <div class="title" style="text-align: center;color: #333333;font-weight: 500;font-size: 40px" id="notice-title"></div>
            <div class="time" id="notice-created_at" style="text-align: center;color: #BFC0C5;font-size: 20px;margin-bottom: 40px"></div>
            <div class="content" id="notice-content">
                
            </div>
        </div>
    </div>
@endsection