<div>
    <div style="text-align: right;">
        <div id="quick-search-current-school-id" data-school="{{ session('school.id') }}"></div>
        <div id="quick-search-current-scope" data-scope="{{ $highlight }}"></div>
        <div class="btn-group">
            <a href="{{ route('school_manager.school.teachers') }}" class="btn btn-{{ $highlight==='teacher' ? 'primary' : 'default' }}">
                <span class="fa {{ $highlight==='teacher' ? 'fa-check-square' : null }}"></span> 认证-教师/工
            </a>
            <a href="{{ route('school_manager.school.students') }}" class="btn btn-{{ $highlight==='student' ? 'primary' : 'default' }}">
                <span class="fa {{ $highlight==='student' ? 'fa-check-square' : null }}"></span> 认证-学生
            </a>
            <a href="{{ route('school_manager.school.users') }}" class="btn btn-{{ $highlight==='users' ? 'primary' : 'default' }}">
                <span class="fa {{ $highlight==='users' ? 'fa-check-square' : null }}"></span> 未认证
            </a>
        </div>
    </div>
</div>
