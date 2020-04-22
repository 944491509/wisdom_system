@foreach(\Illuminate\Support\Facades\Auth::user()->managerMenu() as $menu)
    <li class="nav-item">
        <a href="@empty($menu['href'])javascript:;@else{{ route($menu['href'],$menu['param']) }}@endempty" class="nav-link nav-toggle">
            <i class="material-icons">{{ $menu['icon'] }}</i>
            <span class="title">{{ $menu['name'] }}</span>
            @if($menu['children'])
                <span class="arrow "></span>
            @endif
        </a>
        @if($menu['children'])
            <ul class="sub-menu">
                @foreach($menu['children'] as $child)
                    <li class="nav-item">
                            @if ($child['children'])
                            <a href="@empty($child['href'])javascript:;@else{{ route($child['href'],$child['param']) }}@endempty" class="nav-link nav-toggle">
                                <i class="{{ $child['icon'] }}"></i> {{ $child['name'] }}
                                <span class="arrow"></span>
                            </a>
                            <ul class="sub-menu">
                                @foreach($child['children'] as $ch)
                                    <li class="nav-item">
                                        <a href="@empty($ch['href'])javascript:;@else{{ route($ch['href'],$ch['param']) }}@endempty" class="nav-link">
                                            <span class="title">{{ $ch['name'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            @else
                                <a href="@empty($child['href'])javascript:;@else{{ route($child['href'],$child['param']) }}@endempty" class="nav-link">
                                    <span class="title">{{ $child['name'] }}</span>
                                </a>
                            @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </li>
@endforeach
