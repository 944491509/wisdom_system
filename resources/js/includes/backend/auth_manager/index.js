
import {Util} from "../../../common/utils";
import ManagerMent from "./components/ManagerMent";

const authManager = document.getElementById(
  "new-authManage-app"
);
if (authManager) {

  new Vue({
    el: "#new-authManage-app",
    data() {
      return {
        tableData: [],
        isShowAuthGroupDrawer: false,
        isShowAddAuthDrawer: false,
        isShowPermission: true,
        setAuthPage: false,
        options:[
          {
            label: '超级管理员',
            value: 1
          },
          {
            label: '学校',
            value: 2
          },
          {
            label: '教师',
            value: 9
          },
        ],
        role: {
          name: '',
          type: '',
          description: ''
        },
        school_id: '',
        formData: {
          managers:[],
        },
        scoped: '',
        rowXXId: '',
        msg:"2312",
        permissionsList:[],
        permission: [],
        checkedUsers:[]
      }
    },
    created() {
      this.getList()
      const d = document.getElementById('new-authManage-app');
      this.school_id = d.getAttribute("data-school")
      // console.log(school_id)
    },

    methods: {
      getList() {
        axios.post(
          '/admin/simpleacl/list',
        ).then(res => {
          if(Util.isAjaxResOk(res)){
            console.log(res)
              let list = res.data.data.data.map(e => {
                if (e.type == 9) {
                  e.typeName = '教师'
                } else if(e.type == 2) {
                  e.typeName = '学校'
                } else if(e.type == 1) {
                  e.typeName = '超级管理员'
                }
                return e
              })
             this.tableData = list
          }
        })
      },
      addRole() {
        if (!this.role.name) {
          this.$message({
            message: '请输入名称！',
            type: 'warning'
          });
          return
        }
        if (!this.role.type) {
          this.$message({
            message: '请选择类型！',
            type: 'warning'
          });
          return
        }
        if (!this.role.description) {
          this.$message({
            message: '请输入描述！',
            type: 'warning'
          });
          return
        }
        axios.post(
          '/admin/simpleacl/add',{role: this.role}
        ).then(res => {
            if(Util.isAjaxResOk(res)){
              if(res.data.code == 1000) {
                this.$message({
                  message: '添加成功！',
                  type: 'success'
                });
              }
              this.isShowAddAuthDrawer = false
              this.role = {}
              this.getList()
            }
        })
      },
      deleteAuth(val) {
        axios.post('/admin/simpleacl/delete', {id: val}).then(res => {
          if(Util.isAjaxResOk(res)){
            if(res.data.code == 1000) {
              this.$message({
                message: '删除成功！',
                type: 'success'
              });
              this.getList()
            }
          }
        })
      },
      handleClose() {
        this.isShowAddAuthDrawer = false
        this.isShowAuthGroupDrawer = false
      },
      setCount(val, id, row) {
        if (val == 1) {
          this.scoped = 'super'
        } else if (val == 2) {
          this.scoped = 'school'
        } else if (val == 9) {
          this.scoped = 'teacher'
        }
        this.rowXXId = id
        this.checkedUsers = row.users
        console.log('this.rowXXId',this.rowXXId)
        this.isShowAuthGroupDrawer = true
      },
      setPermission(val,permissions){
        this.isShowPermission = false
        this.rowXXId = val
        axios.post('/admin/simpleacl/menu-permission', {id: val}).then(res => {
          if(Util.isAjaxResOk(res)){
            this.permission = res.data.data
          }
        })
        this.permissionsList = permissions
      },
      savePermission() {
        axios.post('/admin/simpleacl/add-permission', {id: this.rowXXId, permissions: this.permissionsList}).then(res => {
          if(Util.isAjaxResOk(res)){
            if(res.data.code == 1000) {
              this.$message({
                message: '操作成功！',
                type: 'success'
              });
              this.permissionsList = []
              this.isShowPermission = true;
              this.getList();
            }
          }
        })
      },
      chanegMenu(val){
        console.log('chanegMenu',val)
      }
    }
  })
}
