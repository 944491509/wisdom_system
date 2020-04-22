<div class="sidebar-container">
    <div class="sidemenu-container navbar-collapse collapse fixed-menu">
        <div id="remove-scroll" class="left-sidemenu">
            <ul class="sidemenu  page-header-fixed sidemenu-closed" data-keep-expanded="false"
                data-auto-scroll="true" data-slide-speed="200" style="padding-top: 20px">
                <li class="sidebar-toggler-wrapper hide">
                    <div class="sidebar-toggler">
                        <span></span>
                    </div>
                </li>
                <li class="sidebar-user-panel">
                    <div class="user-panel">
                        <div class="pull-left image">
                            <img src="{{ \Illuminate\Support\Facades\Auth::user()->profile->avatar ?? \App\User::DEFAULT_USER_AVATAR }}" class="img-circle user-img-circle"
                                 alt="User Image" />
                        </div>
                        <div class="pull-left info">
                            <p>{{ Auth::user()->name }}</p>
                            <a href="#"><i class="fa fa-circle user-online"></i><span class="txtOnline">
												Online</span></a>
                        </div>
                    </div>
                </li>
                @include('layouts.desktop.elements.aclmenu')
            </ul>
        </div>
    </div>
</div>
