@csrf
<input type="hidden" name="school_id" value="{{ $school->id }}">

<div class="form-group">
    <label>管理员姓名</label>
    <input required type="text" class="form-control" value="{{ $user->name }}" placeholder="必填: 管理员真实姓名" name="user[name]">
</div>

<div class="form-group">
    <label for="facility-name-input">类型</label>

    <select required type="select" class="form-control" id="user-type-select" value="" placeholder="类型" name="user[type]">
        <option value="">请选择</option>
        @foreach($type as $key => $val)
            <option value="{{$val['id']}}"
                    @if(isset($user['user_type']))
                    @if($val['id'] == $user['user_type']) selected @endif
                    @endif >
                {{$val['name']}}
            </option>
        @endforeach
    </select>

</div>


