/**
 * 通知管理 APP
 */
import {Util} from "../../common/utils";
import {Constants} from "../../common/constants";
import AddNotice from '../../components/manageNotices/AddNotice';

if(document.getElementById('notice-manager-app')){
    new Vue({
        el:'#notice-manager-app',
        components:{
          AddNotice
        },
        data(){
            return {
                notice:{
                    id:'',
                    schoolId:'',
                    title:'',
                    content:'',
                    image:'',
                    release_time:'',
                    note:'',
                    inspect_id:'',
                    type:'1',
                    user_id:'',
                    status:false,
                    attachments:[],
                    selectedOrganizations:[],
                },
                types:[], // 通知类型
                inspectTypes:[], // 可动态添加的检查类型
                organizations:[],
                showFileManagerFlag: false,
                showAttachmentManagerFlag: false,
                isLoading: false,
                // 可见范围选择
                showOrganizationsSelectorFlag: false,
                showInspectTypesSelectorFlag: false,
                releaseDrawer: false,
                userUuid: null
            }
        },
        computed: {
            'isUrlOnly': function(){
                return this.banner.type === 2;
            },
            'isPictureAndText': function(){
                return this.banner.type === 1;
            },
            'isStatic': function(){
                return this.banner.type === 0;
            },
        },
        watch:{
            'notice.type': function(val){
                if(parseInt(val) === Constants.NOTICE_TYPE_INSPECT){
                    this.showInspectTypesSelectorFlag = true;
                }
                else{
                    this.showInspectTypesSelectorFlag = false;
                }
            }
        },
        created(){
            const dom = document.getElementById('app-init-data-holder');
            this.notice.schoolId = dom.dataset.school;
            this.userUuid = dom.dataset.useruuid;
            this.types = JSON.parse(dom.dataset.types);
            this.inspectTypes = JSON.parse(dom.dataset.inspecttypes);
        },
        methods: {
          handleClose() {
            this.releaseDrawer = false
          },
          // handleOpen(val) {
          //   console.log('BBB',val)
          //   this.$nextTick(() => {

          //     this.$refs.childDrawer.handleOpen(val)
          //   })
          // },
          edit(id) {
            console.log('BBB',id)
            this.releaseDrawer = true
            this.$nextTick(() => {
              this.$refs.childDrawer.handleOpen(id)
            })
          },
            loadNotice: function(id){
                this.isLoading = true;
                axios.post(
                    '/school_manager/notice/load',
                    {id: id}
                ).then(res => {
                    if(Util.isAjaxResOk(res)){
                        this.notice = res.data.data.notice;
                        this.notice.type += '';
                    }
                    else{
                        this.$message.error(res.data.message);
                    }
                    this.isLoading = false;
                })
            },
            onSubmit: function(){
                if(this.notice.title.trim() === ''){
                    this.$message.error('标题必须填写');
                    return false;
                }
                if(Constants.NOTICE_TYPE_INSPECT === parseInt(this.notice.type)){
                    if(this.notice.inspect_id === ''){
                        this.$message.error('请指定检查的类型');
                        return false;
                    }
                }
                else{
                    this.notice.inspect_id = '';
                }

                this.isLoading = true;
                axios.post(
                    '/school_manager/notice/save-notice',
                    {notice: this.notice}
                ).then(res => {
                    if(Util.isAjaxResOk(res)){
                        // window.location.reload();
                    }
                    else{
                        this.$message.error(res.data.message);
                    }
                    this.isLoading = false;
                })
            },
            pickFileHandler: function(payload){
                this.showFileManagerFlag = false;
                this.notice.image = payload.file.url;
            },
            pickAttachmentHandler: function(payload){
                this.showAttachmentManagerFlag = false;
                this.notice.attachments.push(payload.file);
            },
            newNotice: function(){
              this.releaseDrawer = true
              this.$nextTick(() => {
                this.$refs.childDrawer.addhandleOpen()
              })
            },
            deleteNotice: function(id){
                this.$confirm('此操作将永久删除该通知, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    window.location.href = '/school_manager/notice/delete?id=' + id;
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });
            },
            deleteNoticeMedia: function(id){
                this.isLoading = true;
                axios.post(
                    '/school_manager/notice/delete-media',
                    {id: id}
                ).then(res => {
                    if(Util.isAjaxResOk(res)){
                        const idx = Util.GetItemIndexById(id, this.notice.attachments);
                        this.notice.attachments.splice(idx, 1);
                        this.$message({
                            type:'success',
                            message:'删除成功'
                        });
                    }
                    else{
                        this.$message.error(res.data.message);
                    }
                    this.isLoading = false;
                });
            },
            // 可见范围选择器
            onOrganizationsSelectedHandler: function (payload) {
                this.showOrganizationsSelectorFlag = false;
                this.notice.selectedOrganizations = payload.data.org;
            }
        }
    })
}