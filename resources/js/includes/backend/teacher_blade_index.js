import { Util } from "../../common/utils";
import { startedByMe, waitingByMe, processedByMe, copyByMe } from "../../common/flow";
import { Constants } from "../../common/constants";

if (document.getElementById('teacher-assistant-index-app')) {
    new Vue({
        el: '#teacher-assistant-index-app',
        data() {
            return {
                schoolId: null,
                bannerData: [], // Banner
                position: 2, // 学生审批
                input: '', // 搜索框
                show: '待审批',
                page: 1, // 当前页
                size: 10, // 条数
                total: '', // 总条数
                statusMap: {
                    0: '审核中',
                    1: '已通过',
                    2: '未通过',
                    3: '已撤回'
                },
                tableData: []
            }
        },
        created() {
            const dom = document.getElementById('app-init-data-holder');
            this.schoolId = dom.dataset.school;
            this.getBunnerData(); // Banner
            this.waitingForMe(); // 待审批
        },
        methods: {
            // Banner
            getBunnerData: function () {
                const url = Util.buildUrl(Constants.API.TEACHER_WEB.INDEX);
                axios.post(url).then((res) => {
                    if (Util.isAjaxResOk(res)) {
                        this.bannerData = res.data.data;
                    }
                }).catch((err) => {
                    console.log(err)
                });
            },
            // tab切换
            handleClick: function (tab) {
                this.show = tab.label
                if (tab.label === '待审批') {
                    this.waitingForMe();
                } else if (tab.label === '已审批') {
                    this.loadFlowsProcessedByMe();
                } else {
                    this.loadFlowsCopyByMe();
                }
            },
            // 搜索
            serach() {
                if (this.show === '待审批') {
                    this.waitingForMe();
                } else if (this.show === '已审批') {
                    this.loadFlowsProcessedByMe();
                } else {
                    this.loadFlowsCopyByMe();
                }
            },
            // 待审批
            waitingForMe: function () {
                waitingByMe(this.userUuid, this.input, this.position, this.page, this.size).then(res => {
                    if (Util.isAjaxResOk(res)) {
                        this.tableData = res.data.data.flows;
                        this.total = res.data.data.total;
                    }
                });
            },
            // 已审批
            loadFlowsProcessedByMe: function () {
                processedByMe(this.userUuid, this.input, this.position, this.page, this.size).then(res => {
                    if (Util.isAjaxResOk(res)) {
                        this.tableData = res.data.data.flows;
                        this.total = res.data.data.total;
                    }
                });
            },
            // 我抄送的
            loadFlowsCopyByMe: function () {
                copyByMe(this.userUuid, this.input, this.position, this.page, this.size).then(res => {
                    if (Util.isAjaxResOk(res)) {
                        this.tableData = res.data.data.flows;
                        this.total = res.data.data.total;
                    }
                });
            },
            // 分页
            handleCurrentChange(val) {
                this.page = val;
                if (this.show === '待审批') {
                    this.waitingForMe();
                } else if (this.show === '已审批') {
                    this.loadFlowsProcessedByMe();
                } else {
                    this.loadFlowsCopyByMe();
                }
            },
        }
    });
}
