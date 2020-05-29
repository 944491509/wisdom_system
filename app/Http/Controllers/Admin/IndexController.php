<?php

namespace App\Http\Controllers\Admin;

use App\Dao\Notice\AppProposalDao;
use App\Http\Requests\MyStandardRequest;
use App\Utils\JsonBuilder;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class IndexController extends Controller
{

    /**
     * PC 办公页面
     */
    public function officeIcon()
    {
        $data = [
            [
                'name' => '通知公告',
                'icon' => asset('assets/img/teacher/ass6.png'),
                'url' => 'notices-center'
            ],
            [
                'name' => '日志',
                'icon' => asset('assets/img/teacher/ass1.png'),
                'url' => 'logs',
            ],
            [
                'name' => '内部信',
                'icon' => asset('assets/img/teacher/ass2.png'),
                'url' => 'internal-messages',
            ],
            [
                'name' => '会议',
                'icon' => asset('assets/img/teacher/ass3.png'),
                'url' => 'meetings',
            ],
//            [
//                'name' => '公文',
//                'icon' => asset('assets/img/teacher/ass4.png'),
//                'url' => 'applications',
//            ],
            [
                'name' => '任务',
                'icon' => asset('assets/img/teacher/ass5.png'),
                'url' => 'tasks',
            ],
        ];

        return JsonBuilder::Success($data);
    }

    /**
     * PC 助手页
     * @param MyStandardRequest $request
     * @return string
     */
    public function helpIcon(MyStandardRequest $request)
    {
        $data = [
            ['name' => '教学助手', 'helper_page' =>
               [
                   [
                        'name'=> '课表',
                        'icon' => asset('assets/img/teacher/ass-icon4.png'),
                        'url' => 'course',
                    ],
                   [
                        'name'=> '教学资料',
                        'icon' => asset('assets/img/teacher/ass-icon7.png'),
                        'url' => 'material',

                    ],
                   [
                       'name' => '签到',
                       'icon' => asset('assets/img/teacher/ass-icon9.png'),
                       'url'  => 'check-in',
                   ],
                   [
                       'name' => '评分',
                       'icon' => asset('assets/img/teacher/ass-icon8.png'),
                       'url'  => 'evaluation',
                   ],
                   [
                       'name' => '选课',
                       'icon' => asset('assets/img/teacher/ass9.png'),
                       'url'  => 'electives',
                   ],
               ]
            ],
            ['name' => '班主任助手', 'helper_page' =>
               [
                    [
                        'name'=> '班级管理',
                        'icon' => asset('assets/img/teacher/ass10.png'),
                        'url' => 'grades-manager',
                    ],
                    [
                        'name'=> '学生信息',
                        'icon' => asset('assets/img/teacher/ass-icon12.png'),
                        'url' => 'students-manager',
                    ],
                    [
                        'name'=> '班级签到',
                        'icon' => asset('assets/img/teacher/ass-icon10.png'),
                        'url' => 'grades-check-in',
                    ],
                    [
                        'name'=> '班级评分',
                        'icon' => asset('assets/img/teacher/ass-icon11.png'),
                        'url' => 'grades-evaluations'
                    ],
               ]
            ]
        ];

        return JsonBuilder::Success($data);
    }

    /**
     * 意见反馈列表
     * @param MyStandardRequest $request
     * @return Factory|View
     */
    public function proposal(MyStandardRequest $request)
    {
        $dao = new AppProposalDao;

        $data = $dao->getAllProposal();
        foreach ($data as $key => $val) {
            $val->user;
        }

        $this->dataForView['data'] = $data;
        $this->dataForView['pageTitle'] = '意见反馈';
        return view('admin.proposal.list', $this->dataForView);
    }

    /**
     * 意见反馈详情
     * @param MyStandardRequest $request
     * @return Factory|View
     */
    public function proposalInfo(MyStandardRequest $request)
    {
        $id = $request->get('id');

        $dao = new AppProposalDao;

        $data = $dao->getProposalById($id);
        $this->dataForView['data'] = $data;
        $this->dataForView['pageTitle'] = '意见反馈详情';
        return view('admin.proposal.info', $this->dataForView);
    }


}
