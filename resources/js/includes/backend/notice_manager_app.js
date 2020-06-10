/**
 * 通知管理 APP
 */
import {Util} from "../../common/utils";
import {Constants} from "../../common/constants";
import AddNotice from '../../components/manageNotices/AddNotice';
import page from '../../components/page/page';

if(document.getElementById('notice-manager-app')){
    new Vue({
        el:'#notice-manager-app',
        components:{
          AddNotice,
          page
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
                userUuid: null,
                screen: {
                  type: '',
                  range: '',
                  start_time: '',
                  end_time: '',
                  keyword: ''
                },
                tableData: {
                  table: [],
                  tableHolder: [
                    {
                      prop: 'type',
                      label: '类型',
                      width: '80'
                    },
                    {
                      prop: 'title',
                      label: '标题',
                      width: '300'
                    },
                    {
                      prop: 'accept',
                      label: '接收',
                      width: '80',
                      type: 1
                    },
                    {
                      prop: 'rangeList',
                      label: '可见范围',
                      width: '450',
                      type: 2
                    },
                    {
                      prop: 'created_at',
                      label: '创建时间',
                      width: '250'
                    },
                    {
                      prop: 'release_time',
                      label: '发布时间',
                      width: '250'
                    },
                    {
                      prop: 'create_user',
                      label: '创建人',
                      width: '120'
                    },
                    {
                      prop: 'status',
                      label: '状态',
                      width: '100'
                    }
                  ],
                  currentPage: 1,
                  total: 0
                }
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
          this.getTableList()
        },
        methods: {
          // 列表数据
          getTableList() {
            let params = {
              school_id: this.notice.schoolId,
              type: this.screen.type,
              range: this.screen.range,
              start_time: this.screen.start_time,
              end_time: this.screen.end_time,
              keyword: this.screen.keyword,
            }
            axios.post('/api/notice/show-notice', params).then(res => {
              if(Util.isAjaxResOk(res)){
                console.log(res)
                let list = res.data.data.list
                console.log('AAA',list)
                list = list.map(e => {
                  e.rangeList = []
                  Object.entries(e.range).map(([key, value], i) => {
                    let str = ''
                    value.map(e => {
                      str = str + e.name + ';'
                    })
                    e.rangeList.push(str)
                    e.rangeList.reverse()
                  })
                  return e
                })
                this.tableData.table = list
                this.tableData.currentPage = list.currentPage
                this.tableData.total = list.total
                this.$message({
                    type:'success',
                    message:'查询通知列表成功！'
                });
              }
              else{
                  this.$message.error('查询列表失败！');
              }
            })
          },
          selectgetTableList() {

          },
          handleClose() {
            this.releaseDrawer = false
          },
          edit(id) {
            axios.post('/api/notice/notice-info', {notice_id: id}).then(res => {
              if(Util.isAjaxResOk(res)){
                console.log(res)
                let o = res.data.data.notice
                o.organization = o.scope.teacher ? o.scope.teacher : []
                o.grade = o.scope.student ? o.scope.student : []
                // this.$message({
                //     type:'success',
                //     message:'查询通知列表成功！'
                // });
                // this.notice = res.data.notice
                this.releaseDrawer = true
                this.$nextTick(() => {
                  this.$refs.childDrawer.handleOpen(o)
                })
              }
              else{
                  this.$message.error('查询详情数据失败！');
              }
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
              console.log(id)
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