<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{$info['notice_title']}</title>
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <!--开启对web app的支持-->
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <!--主要是正对苹果手机将数字自动识别为号码-->
    <meta name="format-detection" content="telephone=no"/>
    <!-- 忽略识别邮箱，主要是针对安卓手机会自动将符合邮箱格式的字符串识别为邮箱地址-->
    <meta content="email=no" name="format-detection"/>
    <meta name="apple-itunes-app" content="app-id=myAppStoreID, affiliate-data=myAffiliateData, app-argument=myURL"/>
    <!-- 添加智能 App 广告条 Smart App Banner：告诉浏览器这个网站对应的app，并在页面上显示下载banner:https://developer.apple.com/library/ios/documentation/AppleApplications/Reference/SafariWebContent/PromotingAppswithAppBanners/PromotingAppswithAppBanners.html -->
    <!-- 针对手持设备优化，主要是针对一些老的不识别viewport的浏览器，比如黑莓 -->
    <meta name="HandheldFriendly" content="true">
    <!-- 微软的老式浏览器 -->
    <meta name="MobileOptimized" content="320">
    <!-- uc强制竖屏 -->
    <meta name="screen-orientation" content="portrait">
    <!-- QQ强制竖屏 -->
    <meta name="x5-orientation" content="portrait">
    <!-- UC强制全屏 -->
    <meta name="full-screen" content="yes">
    <!-- QQ强制全屏 -->
    <meta name="x5-fullscreen" content="true">
    <!-- UC应用模式 -->
    <meta name="browsermode" content="application">
    <!-- QQ应用模式 -->
    <meta name="x5-page-mode" content="app">
    <!-- windows phone 点击无高光 -->
    <meta name="msapplication-tap-highlight" content="no">
	<link href="{{ asset('assets/css/wifinotice/mescroll.min.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/css/wifinotice/common.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/css/wifinotice/quesDet.css') }}" rel="stylesheet">
</head>
<body>
<!--标题-->
<div class="header">
    <a class="btn-left back"></a>
    {{ $dataOne->notice_title }}
</div>
<!--滑动区域-->
<div id="mescroll" style="width: 95%;margin: 50px auto 30px;">
    {{!! $dataOne->notice_content !!}}
</div>
<script src="{{ asset('assets/js/wifinotice/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/wifinotice/common.js') }}"></script>
</body>
</html>
