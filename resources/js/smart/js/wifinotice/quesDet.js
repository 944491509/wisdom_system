$(function () {
    var sub = 'http://app.etongxue.net/';
    //截取url后字符串
    function getQueryString(name) {
        var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
        var r = window.location.search.substr(1).match(reg);
        if (r != null) {
            //return unescape(r[2]);
            return r[2];
        }
        return null;
    }
    var noticeid=getQueryString('noticeid');

    function getHelpdet(){
        var data={
            noticeid:noticeid
        };
        $.ajax({
            type: 'post',
            url: sub + 'api/index/getApiWifiNoticeInfo',
            dataType:'jsonp',
            jsonp:'callback',
            data:data,
            success: function (res) {
                console.log(res)
                console.log(JSON.res)
            },
            error: function (err) {
                console.log(err)
            }
        })
    }

    getHelpdet();






})