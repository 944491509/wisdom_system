$(function(){
    //创建MeScroll对象
    var mescroll = new MeScroll("mescroll", {
        down: {
            auto: false, //是否在初始化完毕之后自动执行下拉回调callback; 默认true
            callback: downCallback //下拉刷新的回调
        },
        up: {
            auto: true, //是否在初始化时以上拉加载的方式自动加载第一页数据; 默认false
            isBounce: false, //此处禁止ios回弹,解析(务必认真阅读,特别是最后一点): http://www.mescroll.com/qa.html#q10
            callback: upCallback, //上拉回调,此处可简写; 相当于 callback: function (page) { upCallback(page); }
            toTop:{ //配置回到顶部按钮
                //src : "../res/img/mescroll-totop.png", //默认滚动到1000px显示,可配置offset修改
                //offset : 1000
            }
        }
    });

    /*下拉刷新的回调 */
    function downCallback(){
        //联网加载数据
        getListDataFromNet(0, 1, function(data){
            //联网成功的回调,隐藏下拉刷新的状态
            mescroll.endSuccess();
            //设置列表数据
            setListData(data, false);
        }, function(){
            //联网失败的回调,隐藏下拉刷新的状态
            mescroll.endErr();
        });
    }

    /*上拉加载的回调 page = {num:1, size:10}; num:当前页 从1开始, size:每页数据条数 */
    function upCallback(page){
        //联网加载数据
        getListDataFromNet(page.num, page.size, function(curPageData){
            //联网成功的回调,隐藏下拉刷新和上拉加载的状态;
            //mescroll会根据传的参数,自动判断列表如果无任何数据,则提示空;列表无下一页数据,则提示无更多数据;
            console.log("page.num="+page.num+", page.size="+page.size+", curPageData.length="+curPageData.length);

            //方法一(推荐): 后台接口有返回列表的总页数 totalPage
            //mescroll.endByPage(curPageData.length, totalPage); //必传参数(当前页的数据个数, 总页数)

            //方法二(推荐): 后台接口有返回列表的总数据量 totalSize
            //mescroll.endBySize(curPageData.length, totalSize); //必传参数(当前页的数据个数, 总数据量)

            //方法三(推荐): 您有其他方式知道是否有下一页 hasNext
            //mescroll.endSuccess(curPageData.length, hasNext); //必传参数(当前页的数据个数, 是否有下一页true/false)

            //方法四 (不推荐),会存在一个小问题:比如列表共有20条数据,每页加载10条,共2页.如果只根据当前页的数据个数判断,则需翻到第三页才会知道无更多数据,如果传了hasNext,则翻到第二页即可显示无更多数据.
            mescroll.endSuccess(curPageData.length);

            //设置列表数据
            setListData(curPageData, true);
        }, function(){
            //联网失败的回调,隐藏下拉刷新和上拉加载的状态;
            mescroll.endErr();
        });
    }

    /*设置列表数据*/
    function setListData(curPageData, isAppend) {
        var listDom=document.getElementById("newsList");
        for (var i = 0; i < curPageData.length; i++) {
            var newObj=curPageData[i];

            var str='<p>'+newObj.title+'</p>';
            str+='<p class="new-content">'+newObj.content+'</p>';
            var liDom=document.createElement("li");
            liDom.innerHTML=str;

            if (isAppend) {
                listDom.appendChild(liDom);//加在列表的后面,上拉加载
            } else{
                listDom.insertBefore(liDom, listDom.firstChild);//加在列表的前面,下拉刷新
            }
        }
    }

    /*联网加载列表数据
     在您的实际项目中,请参考官方写法: http://www.mescroll.com/api.html#tagUpCallback
     请忽略getListDataFromNet的逻辑,这里仅仅是在本地模拟分页数据,本地演示用
     实际项目以您服务器接口返回的数据为准,无需本地处理分页.
     * */
    var downIndex=0;
    function getListDataFromNet(pageNum,pageSize,successCallback,errorCallback) {
        //延时一秒,模拟联网
        setTimeout(function () {
            try{
                var newArr=[];
                if(pageNum==0){
                    //此处模拟下拉刷新返回的数据
                    downIndex++;
                    var newObj={title:"【新增新闻"+downIndex+"】 新增新闻的标题", content:"新增新闻的内容"};
                    newArr.push(newObj);
                }else{
                    //此处模拟上拉加载返回的数据
                    for (var i = 0; i < pageSize; i++) {
                        var upIndex=(pageNum-1)*pageSize+i+1;
                        var newObj={title:"【新闻"+upIndex+"】 标题标题标题标题标题标题", content:"内容内容内容内容内容内容内容内容内容内容"};
                        newArr.push(newObj);
                    }
                }
                //联网成功的回调
                successCallback&&successCallback(newArr);
            }catch(e){
                //联网失败的回调
                errorCallback&&errorCallback();
            }
        },1000)
    }

});
