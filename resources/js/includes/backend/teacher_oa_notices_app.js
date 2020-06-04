/**
 * 教师办公 app
 */
import { Util } from "../../common/utils";
import { Constants } from "../../common/constants";
import { startedByMe, waitingForMe } from "../../common/flow";

if (document.getElementById("teacher-oa-notices-app")) {
  new Vue({
    el: "#teacher-oa-notices-app",
    data() {
      return {
        schoolId: null,
        userUuid: null,
        url: {
          flowOpen: ""
        },
        isLoading: false,
        flowsStartedByMe: [],
        flowsWaitingForMe: [],
        oneList: [],
        twoList: [],
        threeList: [],
        detail: {},
        drawer: false, // 通知侧边栏
        titleName: "",
        attachments: [], // 附件
        releaseDrawer: false, // 发布通知drawer
        form: {
          title: "",
          textarea: "",
          organizations: []
        },
        innerDrawer: false,
        selecttags: [ ],
        props: {
          isLeaf: "status",
          label: "name",
          disabled:function(data,node){
            if(data.status){
              return false
            }
            return true
          }
        },
        organizansList: [],
        allOran:false
      };
    },
    created() {
      const dom = document.getElementById("app-init-data-holder");
      this.schoolId = dom.dataset.school;
      this.userUuid = dom.dataset.useruuid;
      this.url.flowOpen = dom.dataset.flowopen;
      this.getnoticeList1();
      this.getnoticeList2();
      this.getnoticeList3();
      // this.getOrganizansList()
    },
    methods: {
      checkChange(){
        console.log(this.$refs.tree.getCheckedNodes());
        this.selecttags = this.$refs.tree.getCheckedNodes() || []
      },
      async loadNode(node, resolve) {
        if (node.level === 0) {
          await this.getOrganizansList();
          return resolve(this.organizansList);
        }
        await this.getOrganizansList(node.data.id);
        resolve(this.organizansList);
      },
      handleClose1() {
        this.releaseDrawer = false;
      },
      handleClose2() {
        this.innerDrawer = false;
      },
      deleteTag(tag) {
        this.selecttags.splice(this.selecttags.indexOf(tag), 1);
        this.$refs.tree.setCheckedNodes(this.selecttags)
      },
      reload() {},
      async getOrganizansList(parent_id) {
        await axios
          .post("/Oa/tissue/getOrganization", {
            school_id: this.schoolId,
            parent_id
          })
          .then(res => {
            if (Util.isAjaxResOk(res)) {
              this.organizansList = res.data.data.organ || [];
              this.organizansList.forEach(e => (e.status = !e.status));
            }
          });
      },
      // 最后发布接口
      release() {},
      // 获取本页列表
      getnoticeList1() {
        axios.post("/api/notice/notice-list", { type: 1 }).then(res => {
          if (Util.isAjaxResOk(res)) {
            this.oneList = res.data.data.list;
          }
        });
      },
      // 获取本页列表
      getnoticeList2() {
        axios.post("/api/notice/notice-list", { type: 2 }).then(res => {
          if (Util.isAjaxResOk(res)) {
            this.twoList = res.data.data.list;
          }
        });
      },
      // 获取本页列表
      getnoticeList3() {
        axios.post("/api/notice/notice-list", { type: 3 }).then(res => {
          if (Util.isAjaxResOk(res)) {
            this.threeList = res.data.data.list;
          }
        });
      },
      // 获取详情
      oneDetail(id) {
        axios.post("/api/notice/notice-info", { notice_id: id }).then(res => {
          if (Util.isAjaxResOk(res)) {
            this.drawer = true;
            this.detail = res.data.data.notice;
            this.titleName =
              res.data.data.notice.type == 1
                ? "通知"
                : res.data.data.notice.type == 2
                ? "公告"
                : "检查";
            this.attachments = res.data.data.notice.attachments;
            console.log(res);
          }
        });
      },
      // 关闭
      handleClose(done) {
        done();
        window.location.reload();
      },
      startFlow: function(flowId) {
        const url =
          this.url.flowOpen + "?flow=" + flowId + "&uuid=" + this.userUuid;
        window.open(url, "_blank");
      },
      loadFlowsStartedByMe: function() {
        this.isLoading = true;
        startedByMe(this.userUuid).then(res => {
          if (Util.isAjaxResOk(res)) {
            this.flowsStartedByMe = res.data.data.actions;
          }
          this.isLoading = false;
        });
      },
      loadFlowsWaitingForMe: function() {
        waitingForMe(this.userUuid).then(res => {
          if (Util.isAjaxResOk(res)) {
            this.flowsWaitingForMe = res.data.data.actions;
          }
        });
      },
      viewApplicationDetail: function(action) {
        window.location.href =
          "/pipeline/flow/view-history?action_id=" + action.id;
      },
      reloadThisPage: function() {
        Util.reloadCurrentPage(this);
      }
    }
  });
}
