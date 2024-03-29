<style>
    .pf-notify-drawer{
        width: 410px !important;
        height: calc(100% - 120px) !important;
        top: 75px !important;
        right: 15px !important;
        border-radius: 8px;
    }
    .pf-notify-drawer .el-drawer__body{
        height: 100%;
        flex: initial !important
    }
    .notification-count-badge .el-badge__content{
        border: none !important
    }
</style>
<div class="page-header navbar navbar-fixed-top">
    <div class="page-header-inner ">
        <!-- logo start -->
        <div class="page-logo">
            <a href="{{ route('home') }}">
                <span class="logo-icon material-icons fa-rotate-45">school</span>
                <span class="logo-default">{{ env('APP_NAME') }}</span> </a>
        </div>
        <!-- logo end -->
        <ul class="nav navbar-nav navbar-left in">
            <li><a href="#" class="menu-toggler sidebar-toggler"><i class="icon-menu"></i></a></li>
        </ul>
        <form class="search-form-opened" action="#" method="GET" style="display: none;">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search..." name="query">
                <span class="input-group-btn">
							<a href="javascript:;" class="btn submit">
								<i class="icon-magnifier"></i>
							</a>
						</span>
            </div>
        </form>
        <!-- start mobile menu -->
        <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse"
           data-target=".navbar-collapse">
            <span></span>
        </a>
        <!-- end mobile menu -->
        <!-- start header menu -->
        <div class="top-menu">
            <ul class="nav navbar-nav pull-right">
                <!-- start language menu -->
                <li style="display: none;"><a href="javascript:;" class="fullscreen-btn"><i class="fa fa-arrows-alt"></i></a></li>

                <li id="file-manager-app">
                    <a class="dropdown-toggle" v-on:click="showFileManager">
                        <i class="fa fa-database"></i>&nbsp;我的云盘
                    </a>
                    @include('reusable_elements.section.file_manager_component')
                </li>

                <!-- start notification dropdown -->
                <li class="dropdown dropdown-extended dropdown-notification" id="notification-app-data-top" data-schoolid="{{ session('school.id') }}">
                    <div id="{{ env('APP_DEBUG', true) ? null : 'header_notification_bar' }}" style="padding-top: 13px;">
                        <a href="javascript:;" @click="viewNotifications">
                            <el-badge :value="count" :hidden="count?false:true" class="notification-count-badge">
                                <i class="fa fa-bell-o"></i>
                            </el-badge>
                            <!-- <span style="margin-top: -4px;" class="badge headerBadgeColor1" v-if="hasNew"> 新 </span> -->
                        </a>
                        <el-drawer
                            title=""
                            :destroy-on-close="true"
                            :modal-append-to-body="false"
                            custom-class="pf-notify-drawer"
                            :visible.sync="notifyDrawer"
                            :with-header="false">
                            <Notifications/>
                        </el-drawer>
                    </div>
                </li>

                <li class="dropdown dropdown-user">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown"
                       data-close-others="true">
                        <img alt="" class="img-circle " src="{{ asset('assets/img/dp.jpg') }}" />
                        <span class="username username-hide-on-mobile"> {{ Auth::user()->name }} </span>
                        <i class="fa fa-angle-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-default">
                        @if(\Illuminate\Support\Facades\Auth::user()->isTeacher() || \Illuminate\Support\Facades\Auth::user()->isEmployee() )
                        <li>
                            <a href="{{ route('teacher.profile.edit',['teacher'=>\Illuminate\Support\Facades\Auth::user()->id]) }}">
                                <i class="icon-user"></i> 我的个人资料 </a>
                        </li>
                        @endif
                            <li>
                                <a href="/assets/manual/system.pdf" target ="_blank">
                                    <i class="icon-directions"></i> 系统通用要求
                                </a>
                            </li>
                        <li>
                            <a href="{{ route('teacher.manual.list',['teacher'=>\Illuminate\Support\Facades\Auth::user()->id]) }}">
                                <i class="icon-directions"></i> 用户使用手册
                            </a>
                        </li>
                        <li class="divider"> </li>
                        <li>
                            <a href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                <i class="icon-logout"></i> 退出系统 </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
