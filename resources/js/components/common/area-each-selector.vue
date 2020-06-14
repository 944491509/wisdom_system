<template>
  <div>
    <el-select v-model="checked.province" filterable placeholder="省">
      <span slot="prefix" style="color: #F56C6C">*</span>
      <el-option
        v-for="item in options.province"
        :key="item.area_code"
        :label="item.name"
        :value="item.name"
      ></el-option>
    </el-select>
    <el-select v-model="checked.city" filterable placeholder="市" v-if="level > 1">
      <span slot="prefix" style="color: #F56C6C">*</span>
      <el-option
        v-for="item in options.city"
        :key="item.area_code"
        :label="item.name"
        :value="item.name"
      ></el-option>
    </el-select>
    <el-select v-model="checked.area" filterable placeholder="区/县" v-if="level > 2">
      <span slot="prefix" style="color: #F56C6C">*</span>
      <el-option
        v-for="item in options.area"
        :key="item.area_code"
        :label="item.name"
        :value="item.name"
      ></el-option>
    </el-select>
    <el-select v-model="checked.town" filterable placeholder="乡/镇" v-if="level > 3">
      <el-option
        v-for="item in options.town"
        :key="item.area_code"
        :label="item.name"
        :value="item.name"
      ></el-option>
    </el-select>
    <el-select v-model="checked.country" filterable placeholder="村" v-if="level > 4">
      <el-option
        v-for="item in options.country"
        :key="item.area_code"
        :label="item.name"
        :value="item.name"
      ></el-option>
    </el-select>
  </div>
</template>
<script>
import { Util } from "../../common/utils";

// const BASE_URL = "http://localhost:9999";
const BASE_URL = "";

export default {
  name: "AreaSelector",
  props: {
    level: {
      type: Number,
      default: 5
    },
    value: {}
  },
  watch: {
    checked: {
      deep: true,
      immediate: true,
      handler: function(checked) {
        this.$emit("input", [
          checked.province || "",
          checked.city || "",
          checked.area || "",
          checked.town || "",
          checked.country || ""
        ]);
      }
    },
    "checked.province": function(val) {
      this.setOptions("city", val, "province");
    },
    "checked.city": function(val) {
      this.setOptions("area", val, "city");
    },
    "checked.area": function(val) {
      this.setOptions("town", val, "area");
    },
    "checked.town": function(val) {
      this.setOptions("country", val, "town");
    }
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
      checked: {
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
    getArea(area_code) {
      //api/location/get-area
      return this.getOptions(area_code).then(options => {
        return options;
      });
    },
    getOptions(area_code) {
      return new Promise(resolve => {
        if (this.cache[area_code]) {
          resolve(this.cache[area_code]);
        } else {
          axios
            .post(BASE_URL + "/api/location/get-area", {
              area_code
            })
            .then(res => {
              if (Util.isAjaxResOk(res)) {
                this.cache[area_code] = res.data.data;
                resolve(this.cache[area_code]);
              }
            });
        }
      });
    },
    clean(type) {
      this.checked[type] = ""; // 清除当前值
      let index = this.sorted.findIndex(function(c) {
        return c === type;
      });
      // 清除剩余选项和剩余值
      for (let i = index + 1; i < this.sorted.length; i++) {
        this.options[this.sorted[i]] = [];
        this.checked[this.sorted[i]] = "";
      }
    },
    setOptions(type, name, findBy) {
      // 设置次级选项，次级值，同时清除 剩余选项和剩余值
      let area_code = this.findCodeByName(findBy, name);
      this.getArea(area_code).then(options => {
        this.options[type] = options;
        this.callInit(type);
      });
      this.clean(type);
    },
    callInit(type) {
      if (this.initMap[type]) {
        this.checked[type] = this.initMap[type];
        this.initMap[type] = null;
      }
    },
    findCodeByName(type, name) {
      if (!type || !this.options[type]) {
        return 0;
      }
      let area = this.options[type].find(item => item.name === name);
      return area ? area.area_code : 0;
    },
    setData(arr) {
      if (arr[0]) {
        this.getArea(0).then(options => {
          this.checked.province = arr[0]; // 自动初始化了二级
          this.initMap = {
            city: arr[1],
            area: arr[2],
            town: arr[3],
            country: arr[4]
          };
        });
      }
    }
  },
  created() {
    this.cache = {};
    this.initMap = {};
    this.getArea(0).then(options => {
      this.options.province = options;
    });
  }
};
</script>