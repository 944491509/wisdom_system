@csrf
<div class="form-group">
    <label for="evaluate-title-input">设备名称</label>
    <input required type="text" class="form-control"  value="{{$code['title'] ?? old('title')}}" placeholder="设备名称" name="code[title]">
</div>

<div class="form-group">
    <label for="code-number-input">设备型号</label>
    <input required type="number"  class="form-control"  value="{{$code['type'] ?? old('type')}}" placeholder="设备型号" name="code[type]">
</div>

<div class="form-group">
    <label for="code-number-input">状态</label>
    <select  name="code[status]"  class="form-control" >
             <option value="0"
                     @if(isset($code))
                        @if($code['status']) selected @endif
                    @endif>
                 关闭
             </option>
            <option value="1"
                     @if(isset($code))
                        @if($code['status']) selected @endif
                    @endif>
                 正常
             </option>
    </select>
</div>
