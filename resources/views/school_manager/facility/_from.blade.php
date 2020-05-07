@csrf

<div class="form-group">
    <label for="facility-number-input">布放环境</label>
    <select name="facility[location]" class="form-control">
        <option {{ isset($facility['location']) && $facility['location'] === \App\Models\Schools\Facility::LOCATION_INDOOR ? 'selected' : null  }} value="{{ \App\Models\Schools\Facility::LOCATION_INDOOR }}">室内设备</option>
        <option {{ isset($facility['location']) && $facility['location'] === \App\Models\Schools\Facility::LOCATION_OUTDOOR ? 'selected' : null  }} value="{{ \App\Models\Schools\Facility::LOCATION_OUTDOOR }}">室外设备</option>
    </select>
</div>
<div class="form-group">
    <label for="facility-number-input">设备编号</label>
    <input required type="text" class="form-control" id="facility-number-input" value="{{$facility['facility_number'] ?? old('facility_number')}}" placeholder="设备编号" name="facility[facility_number]">
</div>
<div class="form-group">
    <label for="facility-name-input">设备名称</label>
    <input required type="text" class="form-control" id="facility-name-input" value="{{$facility['facility_name'] ?? old('facility_name')}}" placeholder="设备名称" name="facility[facility_name]">
</div>

<div id="facility-form">
    <div class="form-group">
        <label for="facility-name-input">类型</label>
        <select required type="select" class="form-control"  value="" placeholder="类型" name="facility[type]">
            <option value="">请选择</option>
            @foreach($type as $key => $val)
            <option value="{{$val['id']}}"
                    @if(isset($facility['type']))
                        @if($val['id'] == $facility['type']) selected @endif
                    @endif >
                    {{$val['val']}}
            </option>
            @endforeach
        </select>
    </div>
    <div class="form-group" v-show="type == 3">
        <label for="facility-name-input">班牌类型</label>
        <select type="select" class="form-control"  value="" placeholder="班牌类型" name="facility[card_type]" v-model="card_type">
            <option value="">请选择</option>
            <option value="0">公共班牌</option>
            <option value="1">独立班牌</option>
        </select>
    </div>

    <el-select v-model="value"  filterable  remote placeholder="请选择班级或搜索" class="search-grade" :remote-method="searchGrade" :loading="loading" :loading-text="loading_text"  v-show="card_type == 1">
    <el-option
      v-for="item in options"
      :label="item.name"
      :value="item.id"
      :key="item.id"
      >
    </el-option>
  </el-select>
    <input type="hidden" name="facility[grade_id]" v-model="value">
</div>

<div class="form-group">
    <label for="facility-campus-select">校区</label>
    <select required type="text" class="form-control" id="facility-campus-select" value="" placeholder="校区" name="facility[campus_id]" >
        <option value="">请选择</option>
        @foreach($campus as $key => $val)
            <option value="{{$val['id']}}"
                    @if(isset($facility['campus_id']))
                    @if($val['id'] == $facility['campus_id'])  selected @endif
                    @endif>
                {{$val['name']}}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="facility-building-select">建筑</label>
    <select required type="text" class="form-control" id="facility-building-select" placeholder="建筑" name="facility[building_id]">
        <option value="">请选择</option>

        @if(!empty($building))
            @foreach($building as $key => $val)
                <option value="{{$val['id']}}"
                        @if(isset($facility['building_id']))
                        @if($val['id'] == $facility['building_id'])  selected @endif
                        @endif>
                    {{$val['name']}}</option>
            @endforeach
        @endif
    </select>
</div>

<div class="form-group">
    <label for="facility-room-select">教室</label>
    <select required type="text" class="form-control" id="facility-room-select" value="" placeholder="教室" name="facility[room_id]">
        <option value="">请选择</option>
        @if(!empty($room))
            @foreach($room as $key => $val)
                <option value="{{$val['id']}}"
                        @if(isset($facility['room_id']))
                        @if($val['id'] == $facility['room_id'])  selected @endif
                        @endif>
                    {{$val['name']}}</option>
            @endforeach
        @endif
    </select>
</div>


<div class="form-group">
    <label for="facility-addr-select">详细地址</label>
    <input  type="text" class="form-control" id="facility-addr-input" value="{{$facility['detail_addr'] ?? old('detail_addr')}}" placeholder="详细地址" name="facility[detail_addr]">
</div>
<div id="app-init-data-holder" data-school="{{ session('school.id') }}"></div>

