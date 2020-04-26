import {Util} from "../../../../common/utils";
Vue.component("ManagerMent", {
  template: `
  <el-form ref="form" :model="form" label-width="80px">
    <el-form-item label="管理员" prop="managers">
      <search-bar style="width: 100%;" :school-id="school_id" full-tip="请输入教职工名字" :scope="scoped" :init-query="teacher" v-on:result-item-selected="_selectManager"></search-bar>
      <br/>
    </el-form-item>
    <br/>
    <el-tag style="margin-left: 25px;" :key="idx" v-for="(item, idx) in form.managers" closable :disable-transitions="false" @close="removeFromOrg(idx)">
        {{ item.name }}
    </el-tag>
    <div class="btn-create">
      <el-button  @click="addAuthGroup" type="primary">保存</el-button>
    </div>
  </el-form>
  `,

  data () {
    return {
      teacher:'',
      form:{
        managers:[]
      },
    }
  },
  props: {
    rowXXId: {
      type: String
    },
    school_id: {
      type: String
    },
    scoped: {
      type: String
    },
    users:{
      type: Array,
      default(){
        return [];
      }
    }
  },
  watch: {
    users:{
      handler(val){
        console.log('users',val)
        this.form.managers = val;
      },
      immediate:true
    }

  },
  mounted() {
    console.log(this.rowXXId)
  },
  methods: {
    _selectManager(payload) {
      const { value, id } = payload.item;
      const { managers } = this.form;
      const findIndex = managers.findIndex(item => item.id == id)
      if(findIndex !=-1) {
        this.$message.error('不能重复添加')
        return false
      }
      this.form.managers.push({ id, name: value })
    },
    removeFromOrg(index) {
      this.form.managers.splice(index, 1);
    },
    addAuthGroup() {
      console.log(this.$parent.$parent.rowXXId)
      let users = this.form.managers.map(e => e.id)
      axios.post('/admin/simpleacl/add-role', {id: this.$parent.$parent.rowXXId, users: users}).then(res => {
        if(Util.isAjaxResOk(res)){
          if (res.data.code == 1000) {
            this.$message({
              message: '添加成功！',
              type: 'success'
            });
            this.$emit('close')
          }
        }
      })
    }
  }
});
