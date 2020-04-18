@extends('layouts.app')
@section('content')
    <div class="row" id="school-welcome-list-app">

        <div class="col-5">
            <div class="card">
                <div class="card-head">
                    <header class="full-width">
                        <span class="pull-left" style="padding-top: 6px;">迎新配置</span>
                    </header>
                </div>
                <div class="card-body menu-left">
                    <div class="menu-left-title">
                        <div>
                            <!-- <li @click="isShow(1)"><a :class="[this.is_show1?'on-backgroup-green':'']">基础设置</a></li>
                            <li @click="isShow(2)"><a :class="[this.is_show2?'on-backgroup-green':'']">个人信息</a></li>
                            <li @click="isShow(3)"><a :class="[this.is_show3?'on-backgroup-green':'']">报到确认</a></li>
                            <li @click="isShow(4)"><a :class="[this.is_show4?'on-backgroup-green':'']">报到单</a></li> -->
                            <div @click="isShow(1)">
                              <img v-if="is_show1" src="/assets/img/yinxin/jichuxinxikuang.png">
                              <img v-else src="/assets/img/yinxin/jichuxinxi2.png">
                            </div>
                            <div @click="isShow(2)">
                              <img v-if="is_show2" src="/assets/img/yinxin/gerenxinxikuang.png">
                              <img v-else src="/assets/img/yinxin/gerenxinxi2.png">
                            </div>
                            <div @click="isShow(3)">
                              <img v-if="is_show3" src="/assets/img/yinxin/baodaoquerenkuang.png">
                              <img v-else src="/assets/img/yinxin/baodaoqueren2.png">
                            </div>
                            <div @click="isShow(4)">
                              <img v-if="is_show4" src="/assets/img/yinxin/baodaodankuang.png">
                              <img v-else src="/assets/img/yinxin/baodaodan2.png">
                            </div>
                        </div>
                    </div>
                    <div class="menu-left-content">
                        <!--基础设置-->
                        <div v-if="is_show1 == true">
                            <el-form label-width="100px">
                                <el-row>
                                    <el-form-item label="开始时间">
                                        <el-date-picker v-model="baseInfoForm.config_sdata" type="datetime" placeholder="选择日期时间"></el-date-picker>
                                    </el-form-item>
                                </el-row>
                                <el-row>
                                    <el-form-item label="结束时间">
                                        <el-date-picker v-model="baseInfoForm.config_edate" type="datetime" placeholder="选择日期时间"></el-date-picker>
                                    </el-form-item>
                                </el-row>
                                <el-row>
                                    <el-col>
                                        <el-form-item label="迎新指南">
                                            <el-input placeholder="必填: 请填写迎新指南内容" type="textarea" :rows="10" v-model="baseInfoForm.config_content2" ></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                                <el-row>
                                    <el-form-item>
                                        <el-button type="primary" @click="saveBaseInfoForm">保存</el-button>
                                        <el-button @click="resetForm(1)">重置</el-button>
                                    </el-form-item>
                                </el-row>
                            </el-form>
                        </div>
                        <!--个人信息-->
                        <div v-if="is_show2 == true">
                            <el-form label-width="120px">
                                <el-row>
                                    <el-col>
                                        <el-form-item label="一寸照片">
                                            <el-select placeholder="请选择" v-model="userInfoForm.photo">
                                                <el-option label="是" value="1"></el-option>
                                                <el-option label="否" value="0"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                                <el-row>
                                    <el-col>
                                        <el-form-item label="身份证照片">
                                            <el-select placeholder="请选择" v-model="userInfoForm.card_front">
                                                <el-option label="是" value="1"></el-option>
                                                <el-option label="否" value="0"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                                <el-row>
                                    <el-col>
                                        <el-form-item label="注意事项">
                                            <el-input placeholder="必填: 请填写个人信息注意事项" type="textarea" :rows="10" v-model="userInfoForm.config_content1"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                                <el-row>
                                    <el-form-item>
                                        <el-button type="primary" @click="saveUserInfoForm">保存</el-button>
                                        <el-button @click="resetForm(2)">重置</el-button>
                                    </el-form-item>
                                </el-row>
                            </el-form>
                        </div>
                        <!--报到确认-->
                        <div v-if="is_show3 == true">
                            <el-form label-width="100px">
                                <el-row>
                                    <el-form-item>
                                        <el-button type="primary" @click="saveReportConfirmInfo">保存</el-button>
                                    </el-form-item>
                                </el-row>
                            </el-form>
                        </div>
                        <!--报到单-->
                        <div v-if="is_show4 == true">
                            <el-form label-width="100px">
                                <el-row>
                                    <el-form-item>
                                        <el-button type="primary" @click="saveReportBillInfo">保存</el-button>
                                    </el-form-item>
                                </el-row>
                            </el-form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-3" v-if="dataList2 !== undefined && dataList2.length >0">
            <div class="card">
                <div class="card-head">
                    <header>
                        <span>迎新流程</span>
                    </header>
                </div>
                <div class="card-body">
                    <div class="menu-center-title">
                        <ul>
                            <li v-for="(item,index) in dataList2" :key="index">
                                <!-- <a>@{{ item.name }}
                                    <img class="delete" src="/assets/img/yinxin/delete.png" @click="deleteConfigStep(item)">
                                    <img class="up" src="/assets/img/yinxin/up.png" @click="upConfigStep(item)">
                                    <img class="down" src="/assets/img/yinxin/down.png" @click="downConfigStep(item)">
                                </a> -->
                                <div class="itemDiv">
                                  <img class="" src="/assets/img/yinxin/duihao.png">
                                  <span class="title2">@{{ item.name }}</span>
                                  <img class="delete icon" src="/assets/img/yinxin/shanchu.png" @click="deleteConfigStep(item)">
                                  <img class="up icon" src="/assets/img/yinxin/shangyi.png" @click="upConfigStep(item)">
                                  <img class="down icon" src="/assets/img/yinxin/xiayi.png" @click="downConfigStep(item)">
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-4" v-if="dataList2 !== undefined && dataList2.length >0">
            <div class="card">
                <div class="card-head">
                    <header class="full-width">
                        <span class="pull-left" style="padding-top: 6px;">迎新预览</span>
                    </header>
                </div>
                <div class="card-body">
                    <div class="mobile-phone-previewer">
                        <div class="preview-wrap">
                            <div class="preview-wrap-item" v-for="(item, index) in dataList3" :key="index">
                                <span>@{{ item.title }}</span>
                                <ul>
                                    <li v-for="(item_1, index_1) in item.dataList" :key="index_1">
                                        <img :src="item_1.pics">
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="app-init-data-holder"
         data-school="{{ session('school.id') }}"
         data-type="{{ $type }}"
         data-news='{!! $newsList->toJson() !!}'
    ></div>
@endsection
<style>
    *{
        margin: 0;
        padding: 0;
    }
    .title2 {
        margin-left: 20px;
    }
    .el-form-item__label {
        font-weight: bold;
    }
    .menu-left-title {
        float: left;
        text-align: left;
        margin-left: -10px;
        height: 990px;
    }
    .menu-left-title ul{
        list-style: none;
    }
    .menu-left-title a{
        display: block;
        min-height: 120px;
        min-width: 120px;
        font-size: 16px;
        text-align: center;
        line-height: 120px;
        font-weight: bold;
        background: #F2F2F2;
        margin-bottom: 10px;
    }
    .menu-left-content{
        float: left;
        margin-left: 10px;
    }
    .menu-center-title{
        float: left;
        height: 1000px;
        text-align: left;
    }
    .menu-center-title ul{
        text-align: center;
        list-style: none;
    }
    .menu-center-title div{
        /* display: block;
        min-height: 120px;
        min-width: 120px; */
        font-size: 16px;
        text-align: left;
        line-height: 120px;
        font-weight: bold;
        /* background: #F2F2F2;
        margin-bottom: 30px; */
        position: relative;
    }
    .menu-center-title .itemDiv::after {
      content: '';
      position: absolute;
      width: 1px;
      height: 80px;
      background-color: #ccc;
      bottom: -31%;
      left: 70%;
      opacity: .5;
    }
    .menu-center-title li:last-child .itemDiv::after {
      content: '';
      display: none
    }
    .menu-center-title  .delete{
      position: absolute;
      top: calc( 50% - 14px );
      right: -114%;
        position: absolute;
    }
    .menu-center-title  .up{
        position: absolute;
        top: calc( 50% - 14px );
        right: -44%;
        position: absolute;
    }
    .menu-center-title  .down{
      position: absolute;
    top: calc( 50% - 14px );
    right: -80%;
        position: absolute;
    }
    .menu-center-title .icon {
      height: 25px;
      width: 25px;
    }
    .on-backgroup-green {
        background: #1B9CD5 !important;
    }
    .preview-wrap{
        background: #809CFF !important;
    }
    .preview-wrap-item{
        overflow: auto;
    }
    .preview-wrap-item span{
        display: block;
        color: white;
        font-size: 20px;
        font-weight: bold;
        margin: 8px 0px;
    }
    .preview-wrap-item ul li{
        float: left;
        list-style: none;
    }
    .preview-wrap-item li img{
        width: 155px;
        height: 100px;
        margin: 5px 5px;
    }
</style>

<style>
@media screen and (max-width: 1800px) and (min-width: 1200px){
    .menu-left-title {
        width: 30%;
    }
    .menu-left-title img {
        width: 158px;
    }
    .menu-left-content {
        width: 70%;
    }
    .el-input {
        font-size: 12px !important;
    }
    .title2 {
        margin-left: 0;
    }
    .el-date-editor.el-input, .el-date-editor.el-input__inner {
        width: 200px !important;
    }
}
</style>