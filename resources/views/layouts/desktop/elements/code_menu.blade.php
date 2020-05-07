<li class="nav-item">
    <a href="javascript:void(0);" class="nav-link nav-toggle">
        <i class="material-icons"> nfc</i>
        <span class="title">一码通管理</span>
        <span class="arrow"></span>
    </a>
    <ul class="sub-menu">
        <li class="nav-item">
            <a href="{{ route('teacher.code.list',['uuid'=>session('school.uuid')]) }}" class="nav-link">
                <span class="title">使用记录</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('teacher.code.set',['uuid'=>session('school.uuid')]) }}" class="nav-link">
                <span class="title">开通设置</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('teacher.user.code.list',['uuid'=>session('school.uuid')]) }}" class="nav-link">
                <span class="title">二维码</span>
            </a>
        </li>
    </ul>
</li>
