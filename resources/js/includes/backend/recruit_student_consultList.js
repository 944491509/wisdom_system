import {Util} from "../../common/utils";

if(document.getElementById('recruitStudentConsultList')){
  new Vue({
      el: '#recruitStudentConsultList',
      methods:{
        deleteItem(e){
          let itemId = $(e.currentTarget).attr('itemid');
          this.$confirm('此操作将永久该咨询信息, 是否继续?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            window.axios.get(`/school_manager/consult/delete?id=${itemId}`).then((res) => {
              if (res.data.code ? Util.isAjaxResOk(res) : (res.status == 200 )) {
                this.$message({
                  message: "删除成功, 作息表正在重新加载 ...",
                  type: "success"
                });
                window.location.reload();
              } else {
                this.$message.error("删除失败！");
              }
            })
          }).catch(() => {
            this.$message({
              type: 'info',
              message: '已取消删除'
            });
          });
        }
      }
  })
}


if(document.getElementById('notificationsList')){
  // /admin/notifications/delete?uuid=c3f8d686-2f29-41c7-8233-77a69d8275a6
  new Vue({
      el: '#notificationsList',
      methods:{
        deleteItem(e){
          let itemId = $(e.currentTarget).attr('itemid');
          this.$confirm('此操作将永久该消息信息, 是否继续?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            window.axios.get(`/admin/notifications/delete?uuid=${itemId}`).then((res) => {
              if (res.data.code ? Util.isAjaxResOk(res) : (res.status == 200 )) {
                this.$message({
                  message: "删除成功, 消息正在重新加载 ...",
                  type: "success"
                });
                window.location.reload();
              } else {
                this.$message.error("删除失败！");
              }
            })
          }).catch(() => {
            this.$message({
              type: 'info',
              message: '已取消删除'
            });
          });
        }
      }
  })
}

if(document.getElementById('evaluateContentList')){
  // http://teacher.test/school_manager/evaluate/delete?id=16
  new Vue({
      el: '#evaluateContentList',
      methods:{
        deleteItem(e){
          let itemId = $(e.currentTarget).attr('itemid');
          this.$confirm('此操作将永久该评价模版, 是否继续?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            window.axios.get(`/school_manager/evaluate/delete?id=${itemId}`).then((res) => {
              if (res.data.code ? Util.isAjaxResOk(res) : (res.status == 200 )) {
                this.$message({
                  message: "删除成功, 正在重新加载 ...",
                  type: "success"
                });
                window.location.reload();
              } else {
                this.$message.error("删除失败！");
              }
            })
          }).catch(() => {
            this.$message({
              type: 'info',
              message: '已取消删除'
            });
          });
        }
      }
  })
}



