@csrf
<input type="hidden" name="school_id" value="{{ $school->id }}">
<div class="form-group">
    <label>登陆账户名</label>
    <input required type="text" class="form-control" value="{{$user['mobile'] ?? old('mobile')}}" placeholder="必填: 登陆账号" name="user[mobile]">
</div>
<div class="form-group">
    <label>登陆密码</label>
    <input type="password" class="form-control" value="" placeholder="登陆密码, 创建时必填" name="user[password]">
</div>
<div class="form-group">
    <label>管理员姓名</label>
    <input required type="text" class="form-control" value="" placeholder="必填: 管理员真实姓名" name="user[name]">
</div>

<div class="form-group">
    <label for="facility-name-input">类型</label>

    <select required type="select" class="form-control" id="user-type-select" value="" placeholder="类型" name="user[type]">
        <option value="">请选择</option>
        @foreach($type as $key => $val)
            <option value="{{$val['id']}}"
                    @if(isset($user['type']))
                    @if($val['id'] == $user['type']) selected @endif
                    @endif >
                {{$val['name']}}
            </option>
        @endforeach
    </select>

</div>


<div class="form-group">
    <label>电子邮箱</label>
    <input required type="text" class="form-control" value="" placeholder="必填: 电子邮箱" name="user[email]">
</div>