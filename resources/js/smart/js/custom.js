
$('#facility-campus-select').change(function () {
    var campus_id = $('#facility-campus-select option:selected').val();

    $.ajax({
        //请求方式
        type : "get",
        //请求地址
        url : "/school_manager/facility/getBuildingList",
        //数据，json字符串
        data : {campus_id:campus_id},
        dataType : "json",
        //请求成功
        success : function(result) {
            var option = "<option value=''>请选择</option>";
            $('#facility-building-select').empty();
            $('#facility-room-select').empty();
            $('#facility-room-select').append(option);
            if(result.code === 1000) {
                $(result.data.building).each(function (index,val){
                    option += "<option value='"+val.id+"'>"+ val.name +"</option>";
                });
                $('#facility-building-select').append(option);
            } else {
                $('#facility-building-select').append(option);
                console.log('暂无数据');
            }},
        //请求失败，包含具体的错误信息
        error : function(e){
            console.log('服务器繁忙,请稍后重试')
        }
    });
});

$('#facility-building-select').change(function () {
    var building_id = $('#facility-building-select option:selected').val();
    $.ajax({
        //请求方式
        type : "get",
        //请求地址
        url : "/school_manager/facility/getRoomList",
        //数据，json字符串
        data : {building_id:building_id},
        dataType : "json",
        //请求成功
        success : function(result) {
            var option = "<option value=''>请选择</option>";
            $('#facility-room-select').empty();
            if(result.code === 1000) {
                $(result.data.room).each(function (index,val){
                    option += "<option value='"+val.id+"'>"+ val.name +"</option>";
                });
                $('#facility-room-select').append(option);
            } else {
                $('#facility-room-select').append(option);
                console.log('暂无数据');
            }},
        //请求失败，包含具体的错误信息
        error : function(e){
                console.log(e.status);
                console.log(e.responseText);
            }
    });
});

// 选择学年学期
$('#year_term').change(function () {
    var year= $('#year_term option:selected').attr('year');
    var term= $('#year_term option:selected').attr('term');
    // console.log(year);
    // console.log(term);
    $('#year').val(year);
    $('#term').val(term);

});

// 密码框
$(function () {
  $("#txtPwd").remove();
  /*隐藏的密码框显示并且获取焦点 只读属性去掉*/
  $("#Pwd").show().attr('readonly', false);
})
