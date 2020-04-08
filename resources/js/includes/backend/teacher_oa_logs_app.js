/**
 * 教师办公 app
 */
import { Util } from "../../common/utils";
import { startedByMe, waitingForMe } from "../../common/flow";
import Axios from "axios";

if (document.getElementById('teacher-oa-logs-app')) {
    new Vue({
        el: '#teacher-oa-logs-app',
        data() {
            return {
                schoolId: null,
                userUuid: null,
                url: {
                    flowOpen: ''
                },
                isLoading: false,
                flowsStartedByMe: [],
                flowsWaitingForMe: [],
                nav: [
                    { tit: "未发送", type: 3 },
                    { tit: "已发送", type: 2 },
                    { tit: "已接收", type: 1 },
                ],
                show: 3,
                drawer: false, // 添加和编辑侧边栏
                sendDrawer: false, // 发送至侧边栏
                detailDrawer: false, // 日志详情侧边栏
                sendTitle: '发送至',
                sendSearch: true,
                log: {
                    id: '',
                    title: "",
                    content: "",
                    created_at : '',

                },
                keyword: "",
                logList: [],
                check: false,
                btnText: "全选",
                teachterList: [],
                organizationList:[],
                sendTeacherCheckedList:[],
                teacherKeyword:"",
                drawerTitle: '添加日志',
                isDisabled: false,
                isEdit: false,
                isFromEdit: ''
            }
        },
        created() {
            const dom = document.getElementById('app-init-data-holder');
            this.schoolId = dom.dataset.school;
            this.userUuid = dom.dataset.useruuid;
            this.url.flowOpen = dom.dataset.flowopen;
            this.loadFlowsStartedByMe();
            this.loadFlowsWaitingForMe();
            this.getlogList(3);
        },
        watch: {
            logList: {
                deep: true,
                handler(val) {
                    if (val.every(item => item.sele)) {
                        this.check = true;
                        this.btnText = "取消全选"
                    } else {
                        this.check = false;
                        this.btnText = "全选"
                    }
                }
            }
        },
        methods: {
            add() {
                this.isFromEdit = 'add'
                this.drawer = true
            },
            turnDetailDrawer(item) {
                if(this.show === 2 || this.show === 3) {
                    axios.post('/api/Oa/work-log-info', {id: item.id}).then(res => {
                        if (Util.isAjaxResOk(res)) {
                            if (res.data.data.type === 2) {
                                this.log.created_at = res.data.data.created_at
                                this.log.updated_at = res.data.data.updated_at
                                this.log.collect_user_name = res.data.data.collect_user_name
                            } else if (res.data.data.type === 1) {
                                this.log.updated_at = res.data.data.updated_at
                                this.log.send_user_name = res.data.data.send_user_name
                            }
                        }
                    })
                }
                this.isFromEdit = 'edit'
                this.drawer = true
                this.log.title = item.title
                this.log.content = item.content
                this.log.id = item.id
                this.drawerTitle = '日志详情'
                this.isDisabled = true
                this.isEdit = true
            },
            handleCheckAllChange() {
                this.check = !this.check;
                if (this.check) {
                    this.logList = this.logList.map(item => {
                        item.sele = true;
                        return item;
                    });
                    this.btnText = "取消全选"
                    console.log(this.logList)
                } else {
                    this.logList = this.logList.map(item => {
                        item.sele = false;
                        return item;
                    });
                    this.btnText = "全选";
                    console.log(this.logList)
                }
            },
            // tab切换
            list_click(tab) {
                this.show = tab;
                this.getlogList(tab);
            },
            // 获取日志列表
            getlogList(tab) {
                axios.post(
                    '/api/Oa/list-work-log',
                    { page: 1, type: tab, keyword: this.keyword}
                ).then(res => {
                    if (Util.isAjaxResOk(res)) {
                        this.logList = res.data.data.map((item, index) => {
                            item.sele = false;
                            return item;
                        });
                    }
                })
            },
            // 添加日志
            addlog() {
                // !this.isEdit
                if (this.isEdit) {
                    this.isEdit = false
                    this.isDisabled = false
                } else {
                    if (this.log.title !== '' || this.log.content !== '') {
                        let url = ''
                        let params = {}
                        if (this.isFromEdit === 'add') {
                            url = '/api/Oa/add-work-log'
                            params = {
                                title: this.log.title,
                                content: this.log.content
                            }
                        } else {
                            url = '/api/Oa/update-work-log'
                            params = {
                                data: {
                                    title: this.log.title,
                                    content: this.log.content
                                },
                                id: this.log.id
                            }
                        }
                        axios.post(
                            url,params
                        ).then(res => {
                            if (Util.isAjaxResOk(res)) {
                                this.$message({
                                    message: res.data.message,
                                    type: 'success'
                                });
                                this.getlogList(this.show);
                                this.drawer = false
                            }
                        })
                    } else {
                        this.$message({
                            message: "标题和内容不得为空",
                            type: 'error'
                        });
                    }
                }
            },
            // 添加日志---关闭按钮
            handleClose(done) {
              this.getlogList(this.show);
              done()
            },
            startFlow: function (flowId) {
                const url = this.url.flowOpen + '?flow=' + flowId + '&uuid=' + this.userUuid;
                window.open(url, '_blank');
            },
            loadFlowsStartedByMe: function () {
                this.isLoading = true;
                startedByMe(this.userUuid).then(res => {
                    if (Util.isAjaxResOk(res)) {
                        this.flowsStartedByMe = res.data.data.actions;
                    }
                    this.isLoading = false;
                });
            },
            loadFlowsWaitingForMe: function () {
                waitingForMe(this.userUuid).then(res => {
                    if (Util.isAjaxResOk(res)) {
                        this.flowsWaitingForMe = res.data.data.actions;
                    }
                });
            },
            viewApplicationDetail: function (action) {
                window.location.href = '/pipeline/flow/view-history?action_id=' + action.id;
            },
            reloadThisPage: function () {
                Util.reloadCurrentPage(this);
            },
            async openSendDrawer(){
              this.sendDrawer = true;
              this.getTeachers();
              this.organizationList = await this.getOrganization();
              console.log('organizationList',this.organizationList);
            },
            async getOrganization(praras={},){
              let res =  await axios.post(
                '/Oa/tissue/getOrganization',
                praras
              )
              console.log(res);
              if (Util.isAjaxResOk(res)) {
                return res.data.data || []
              }
              return []

            },
            getTeachers(){
              axios.post(
                '/api/Oa/get-teachers'
              ).then(res => {
                if (Util.isAjaxResOk(res)) {
                    this.teachterList = res.data.data || []
                }
              })
            },
            sendlog(){
              let log_ids = this.logList.filter(e=>e.sele).map(e=>e.id)
              if(!this.sendTeacherCheckedList.length){
                this.$alert('提示', '请选择收件人', {
                  confirmButtonText: '确定',
                  callback: action => {

                  }
                });
                return;
              }
              if(!log_ids.length){
                this.$alert('提示', '请选择日志', {
                  confirmButtonText: '确定',
                  callback: action => {

                  }
                });
                return;
              }
              let t_names = this.teachterList.filter(e=>this.sendTeacherCheckedList.includes(e.id)).map(e=>e.name);
              axios.post(
                '/api/Oa/work-log-send',{
                  id:log_ids.join(','),
                  user_id:this.sendTeacherCheckedList.join(','),
                  user_name: t_names.join(','),
                }
              ).then(res => {
                if (Util.isAjaxResOk(res)) {
                  if(res.data.code==1000){
                    this.$message({
                      type: 'success',
                      message: `发送成功！`
                    });
                    this.drawer = false;

                    //出发数据更新
                    // todo
                  }
                }
              })
            },
            async teatherSearch(){
              console.log(this.teacherKeyword)
              let params = { keyword: this.teacherKeyword,type :2}
              console.log(await this.getOrganization(params));
            }
        }
    });
}
