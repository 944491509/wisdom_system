$(function () {
    var sub = 'http://app.etongxue.net/';


    function getHelplist(){
        var data={};
        $.ajax({
            type: 'post',
            url: sub + 'api/index/getApiWifiNoticeListInfo',
            dataType:'jsonp',
            jsonp:'callback',
            data:data,
            success: function (res) {
                console.log(res.data.data)
                var list=res.data.data;
                console.log(JSON.res)
                var htm='';
                $(list).each(function(k,v){
                    htm+='<li id="'+ v.noticeid+'">'+ v.notice_title+'</li>'
                });
                $('.helplist').empty().append(htm)
                $('.helplist>li').click(function(){
                    var noticeid=$(this).attr('id');
                    window.location.href='./quesDet.html?noticeid='+noticeid;
                });
            },
            error: function (err) {
                console.log(err)
            }
        })
    }

    getHelplist();






})