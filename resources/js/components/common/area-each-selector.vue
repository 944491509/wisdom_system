<template>
  <div>
    <el-select v-model="value.province" filterable placeholder="省">
      <el-option v-for="item in options.province" :key="item.value" :label="item.label" :value="item.value"></el-option>
    </el-select>
    <el-select v-model="value.city" filterable placeholder="市" v-if="level > 1">
      <el-option v-for="item in options.city" :key="item.value" :label="item.label" :value="item.value"></el-option>
    </el-select>
    <el-select v-model="value.area" filterable placeholder="区/县" v-if="level > 2">
      <el-option v-for="item in options.area" :key="item.value" :label="item.label" :value="item.value"></el-option>
    </el-select>
    <el-select v-model="value.town" filterable placeholder="乡/镇" v-if="level > 3">
      <el-option v-for="item in options.town" :key="item.value" :label="item.label" :value="item.value"></el-option>
    </el-select>
    <el-select v-model="value.country" filterable placeholder="村" v-if="level > 4">
      <el-option v-for="item in options.country" :key="item.value" :label="item.label" :value="item.value"></el-option>
    </el-select>
  </div>
</template>
<script>
import { Util } from "../../common/utils";
export default {
  name: "AreaSelector",
  props: {
      level:{
          type:Number,
          default: 5
      },
  },
  data() {
    return {
      options: {
        province: [],
        city: [],
        area: [],
        town: [],
        country: []
      },
      value: {
        province: "",
        city: "",
        area: "",
        town: "",
        country: ""
      },
      sorted: ["province", "city", "area", "town", "country"]
    };
  },
  methods: {
    getArea(area_code, type) {
      //api/location/get-area
      axios
        .post("/api/location/get-area", {
          area_code
        })
        .then(res => {
          if (Util.isAjaxResOk(res)) {
            this.options[type] = res.data.data;
            this.clean(type);
          }
        });
    },
    clean(type) {
      this.value[type] = "";
      let index = this.sorted.findIndex(type);
      for (let i = index; i < this.sorted.length; i++) {
        this.options[this.sorted[i]] = [];
        this.value[this.sorted[i]] = "";
      }
    }
  },
  created() {
    this.getArea(0, "province");
  }
};
</script>