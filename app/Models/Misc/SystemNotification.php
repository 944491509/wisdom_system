<?php
/**
 * 表示系统内部消息的模型
 */
namespace App\Models\Misc;

use App\Models\Notices\Notice;
use App\Utils\Pipeline\IFlow;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\Self_;
use function Complex\sec;

class SystemNotification extends Model
{
    const PRIORITY_LOW      = 0;// 一般消息
    const PRIORITY_MEDIUM   = 2;// 重要消息
    const PRIORITY_HIGH     = 4;// 紧急消息

    const TO_ALL            = 0;// To 所有人
    const TO_TEACHER        = -1;// To 教师
    const TO_STUDENT        = -2;// To 学生
    const FROM_SYSTEM       = 0;// 发自系统的广播消息
    const TYPE_NONE         = 0;// 消息类别: 无
    const TYPE_STUDENT_REGISTRATION = 0;// 消息类别: 学生填写招生报名表

    const SCHOOL_EMPTY = 0; // 针对哪个学校 0 表示不针对学校

    //消息分类 用于客户端展示
    const STUDENT_CATEGORY_QRCODE = 101;//学生端一码通
    const STUDENT_CATEGORY_RECRUITMENT = 102;//学生端招生
    const STUDENT_CATEGORY_ENROLMENT = 103;//学生端迎新
    const STUDENT_CATEGORY_ATTENDANCE = 106;//学生端值周
    const STUDENT_CATEGORY_COURSE = 107;//学生端选课
    const STUDENT_CATEGORY_COURSEWARE = 108;//学生端课件
    const STUDENT_CATEGORY_COURSEINFO = 109;//学生端课程
    const STUDENT_CATEGORY_EXAM = 110;//学生端考试
    const STUDENT_CATEGORY_EXAMRESULT = 111;//学生端成绩
    const STUDENT_CATEGORY_SIGNIN = 112;//学生端签到
    const STUDENT_CATEGORY_ORDER = 113;//学生端订单
    const STUDENT_CATEGORY_JOB = 114;//学生端就业
    const STUDENT_CATEGORY_VIP = 115;//学生端会员
    const STUDENT_CATEGORY_COUPON = 116;//学生端优惠券
    const STUDENT_CATEGORY_MESSAGE = 117;//学生端消息
    const STUDENT_CATEGORY_APPLY = 118;//学生审批

    const TEACHER_CATEGORY_ATTENDANCE = 201;//教师端值周
    const TEACHER_CATEGORY_OAATTENDANCE = 202;//教师端考勤
    const TEACHER_CATEGORY_APPLY = 204;//教师端审批
    const TEACHER_CATEGORY_MEETING = 205;//教师端会议
    const TEACHER_CATEGORY_PROJECT = 206;//教师端项目
    const TEACHER_CATEGORY_TASK = 207;//教师端任务
    const TEACHER_CATEGORY_IMAIL = 208;//教师端内部信
    const TEACHER_CATEGORY_DOCUMENT = 209;//教师端公文
    const TEACHER_CATEGORY_COURSEINFO = 210;//教师端课程
    const TEACHER_CATEGORY_EXAM = 211;//教师端考试
    const TEACHER_CATEGORY_COURSE = 212;//教师端选课
    const TEACHER_CATEGORY_APPLY_STUDENT = 213;//教师端学生审批
    const TEACHER_CATEGORY_QRCODE = 214;//教师端一码通
    const TEACHER_CATEGORY_MESSAGE = 216;//教师端消息

    const COMMON_CATEGORY_NOTICE_NOTIFY = 301;//通知
    const COMMON_CATEGORY_NOTICE_NOTICE = 302;//公告
    const COMMON_CATEGORY_NOTICE_INSPECTION = 303;//检查
    const COMMON_CATEGORY_WIFI = 304;//校园网
    const COMMON_CATEGORY_PIPELINE = 305;//审批 @TODO 工作流程加入事件监听后会拆分不同类型
    const COMMON_CATEGORY_MESSAGE = 306; // 后台发布的系统消息


    // 类型
    const TEACHER_CATEGORY_APPLY_TEXT = '审批';
    const TEACHER_CATEGORY_TASK_TEXT = '任务';
    const COMMON_CATEGORY_NOTICE_NOTIFY_TEXT = '通知';
    const COMMON_CATEGORY_NOTICE_NOTICE_TEXT = '公告';
    const COMMON_CATEGORY_NOTICE_INSPECTION_TEXT = '检查';
    const TEACHER_CATEGORY_MEETING_TEXT = '会议';
    const TEACHER_CATEGORY_IMAIL_TEXT = '内部信';
    const COMMON_CATEGORY_MESSAGE_TEXT = '消息';
    const TEACHER_CATEGORY_COURSE_TEXT = '选课';
    const TEACHER_CATEGORY_APPLY_STUDENT_TEXT = '学生审批';


    protected $fillable = [
        'uuid',
        'sender',
        'to',
        'type',
        'priority',
        'school_id',
        'content',
        'next_move',
        'title',
        'category',
        'app_extra'
    ];


    public $casts = [
        'created_at'=>'datetime:Y-m-d H:i'
    ];

    public static function getNoticeTypeToCategory()
    {
        return [
            Notice::TYPE_NOTIFY => self::COMMON_CATEGORY_NOTICE_NOTIFY,
            Notice::TYPE_NOTICE => self::COMMON_CATEGORY_NOTICE_NOTICE,
            Notice::TYPE_INSPECTION => self::COMMON_CATEGORY_NOTICE_INSPECTION
        ];
    }
    public static function getCategoryByPipelineTypeAndBusiness($type, $business = null, $isStudent = false){
        $category = null;
        if ($isStudent) {
            if ($business) {

            }
            if (!$category) {
                switch ($type) {
                    case IFlow::TYPE_2_01:
                    case IFlow::TYPE_2_02:
                    case IFlow::TYPE_2_03:
                    case IFlow::TYPE_2_04:
                    case IFlow::TYPE_3_01:
                    case IFlow::TYPE_3_02:
                        $category = self::STUDENT_CATEGORY_APPLY;
                        break;
                    default:
                        break;
                }
            }
        }else {
            if ($business) {
                if ($business == IFlow::BUSINESS_TYPE_CLOCKIN) {
                    $category = self::TEACHER_CATEGORY_APPLY;
                }
                if ($business == IFlow::BUSINESS_TYPE_MACADDRESS) {
                    $category = self::TEACHER_CATEGORY_APPLY;
                }
            }
            if (!$category) {
                switch ($type) {
                    case IFlow::TYPE_2_01:
                    case IFlow::TYPE_2_02:
                    case IFlow::TYPE_2_03:
                    case IFlow::TYPE_2_04:
                        $category = self::TEACHER_CATEGORY_APPLY_STUDENT;
                        break;
                    case IFlow::TYPE_1_01:
                    case IFlow::TYPE_1_02:
                    case IFlow::TYPE_1_03:
                    case IFlow::TYPE_1_04:
                    case IFlow::TYPE_1_05:
                    case IFlow::TYPE_1_06:
                    case IFlow::TYPE_3_01:
                    case IFlow::TYPE_3_02:
                        $category = self::TEACHER_CATEGORY_APPLY;
                        break;
                    default:
                        break;
                }
            }
        }
        return $category;
    }
    public function systemNotificationsOrganizations()
    {
        return $this->hasMany(SystemNotificationsOrganization::class, 'system_notifications_id');
    }


    /**
     * 教师pc端查看消息列表的类型
     * @return int[]
     */
    public static function teacherPcNewsCategory() {
        return [
            self::TEACHER_CATEGORY_APPLY,
            self::TEACHER_CATEGORY_TASK,
            self::COMMON_CATEGORY_NOTICE_NOTIFY,
            self::COMMON_CATEGORY_NOTICE_NOTICE,
            self::COMMON_CATEGORY_NOTICE_INSPECTION,
            self::TEACHER_CATEGORY_MEETING,
            self::TEACHER_CATEGORY_IMAIL,
            self::COMMON_CATEGORY_MESSAGE,
            self::TEACHER_CATEGORY_COURSE,
            self::TEACHER_CATEGORY_APPLY_STUDENT,
        ];
    }


    /**
     * 教师pc端的类型
     * @return string[]
     */
    public function categoryText() {
        return [
            self::TEACHER_CATEGORY_APPLY => self::TEACHER_CATEGORY_APPLY_TEXT,
            self::TEACHER_CATEGORY_TASK => self::TEACHER_CATEGORY_TASK_TEXT,
            self::COMMON_CATEGORY_NOTICE_NOTIFY => self::COMMON_CATEGORY_NOTICE_NOTIFY_TEXT,
            self::COMMON_CATEGORY_NOTICE_NOTICE => self::COMMON_CATEGORY_NOTICE_NOTICE_TEXT,
            self::COMMON_CATEGORY_NOTICE_INSPECTION => self::COMMON_CATEGORY_NOTICE_INSPECTION_TEXT,
            self::TEACHER_CATEGORY_MEETING => self::TEACHER_CATEGORY_MEETING_TEXT,
            self::TEACHER_CATEGORY_IMAIL => self::TEACHER_CATEGORY_IMAIL_TEXT,
            self::COMMON_CATEGORY_MESSAGE => self::COMMON_CATEGORY_MESSAGE_TEXT,
            self::TEACHER_CATEGORY_COURSE => self::TEACHER_CATEGORY_COURSE_TEXT,
            self::TEACHER_CATEGORY_APPLY_STUDENT => self::TEACHER_CATEGORY_APPLY_STUDENT_TEXT,
        ];
    }


    public function categoryUrl() {
        return [
            self::TEACHER_CATEGORY_APPLY => 'teacher/ly/oa/index',
            self::TEACHER_CATEGORY_TASK => 'teacher/ly/oa/tasks',
            self::COMMON_CATEGORY_NOTICE_NOTIFY => 'teacher/ly/oa/notices-center',
            self::COMMON_CATEGORY_NOTICE_NOTICE => 'teacher/ly/oa/notices-center',
            self::COMMON_CATEGORY_NOTICE_INSPECTION => 'teacher/ly/oa/notices-center',
            self::TEACHER_CATEGORY_MEETING => 'teacher/ly/oa/meetings',
            self::TEACHER_CATEGORY_IMAIL => 'teacher/ly/oa/internal-messages',
            self::COMMON_CATEGORY_MESSAGE => 'teacher/notice/info',
            self::TEACHER_CATEGORY_COURSE => 'teacher/elective-course/manager',
            self::TEACHER_CATEGORY_APPLY_STUDENT => 'teacher/ly/assistant/index',
        ];
    }


    /**
     * 类型
     * @return string
     */
    public function getCategoryText() {
        $data = $this->categoryText();
        return $data[$this->category] ?? '';
    }


    public function getCategoryUrl() {
        $data = $this->categoryUrl();
        return $data[$this->category] ?? '';
    }


    public function getUserFlowId() {
        if($this->category != self::TEACHER_CATEGORY_APPLY) {
            return null;
        }
        $appExtra = json_decode($this->app_extra,true);
        $url= $appExtra['param1'];
        $arr = parse_url($url);
        $params = $this->convertUrlQuery($arr['query']);
        return $params['user_flow_id'];
    }


    public function convertUrlQuery($query){
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }
}
