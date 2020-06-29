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
            axios.get(`/school_manager/consult/delete?id=${itemId}`).then((res) => {
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
