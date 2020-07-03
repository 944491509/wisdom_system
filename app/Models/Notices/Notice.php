<?php


namespace App\Models\Notices;

use App\Models\NetworkDisk\Media;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    // 通知类型
    const TYPE_NOTIFY = 1;
    const TYPE_NOTICE = 2;
    const TYPE_INSPECTION = 3;

    const TYPE_NOTIFY_TEXT = '通知';
    const TYPE_NOTICE_TEXT = '公告';
    const TYPE_INSPECTION_TEXT = '检查';


    // 状态
    const STATUS_UNPUBLISHED = 0;
    const STATUS_PUBLISH     = 1;
    const STATUS_DELETE     = 2;
    const STATUS_UNPUBLISHED_TEXT = '定时发布';
    const STATUS_PUBLISH_TEXT     = '立即发布';
    const STATUS_DELETE_TEXT      = '已删除';

    // 范围
    const RANGE_ALL = 0;  // 教师和学生都可看
    const RANGE_TEACHER = 1; // 教师可看
    const RANGE_STUDENT = 2; // 学生可看


    // 阅读状态
    const UNREAD = 0;
    const READ = 1;

    public $hidden = ['updated_at'];

    public static function allType()
    {
        return [
            self::TYPE_NOTIFY     => self::TYPE_NOTIFY_TEXT,
            self::TYPE_NOTICE     => self::TYPE_NOTICE_TEXT,
            self::TYPE_INSPECTION => self::TYPE_INSPECTION_TEXT,
        ];
    }


    public function getAllStatus() {
        return [
            self::STATUS_UNPUBLISHED => self::STATUS_UNPUBLISHED_TEXT,
            self::STATUS_PUBLISH => self::STATUS_PUBLISH_TEXT,
            self::STATUS_DELETE => self::STATUS_DELETE_TEXT,
        ];
    }

    public function getStatusTest() {
        $all = $this->getAllStatus();
        return $all[$this->status] ?? '';
    }



    protected $fillable = [
        'school_id', 'title', 'content', 'image', 'release_time', 'note',
        'inspect_id', 'type', 'user_id', 'status', 'range',
    ];

    public $casts = [
        'release_time'=>'datetime'
    ];

    public $media_field = ['url'];

    public $inspect_field = ['name'];

    public $attachment_field = ['*'];

    public function getTypeText()
    {
        return self::allType()[$this->type];
    }

    public function getInspectTypeText(){
        if($this->type === self::TYPE_INSPECTION){
            return $this->inspectType->name;
        }
        return null;
    }

    public function inspectType(){
        return $this->belongsTo(NoticeInspect::class, 'inspect_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inspect()
    {
        return $this->hasOne(NoticeInspect::class,
            'id', 'inspect_id')
            ->select($this->inspect_field);
    }

    public function attachments()
    {
        return $this->hasMany(NoticeMedia::class)
            ->select($this->attachment_field);
    }


    /**
     * 教师组织架构范围
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function selectedOrganizations(){
        return $this->hasMany(NoticeOrganization::class);
    }


    /**
     * 学生班级范围
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function grades() {
        return $this->hasMany(NoticeGrade::class);
    }

    public function getImageAttribute($value){
        if(!empty($value)){
            return asset($value);
        }
        return null;
    }

    /**
     * 查看当前用户该通识是否阅读
     * @param $userId
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasOne|object|null
     */
    public function readLog($userId) {
        return $this->hasOne(NoticeReadLogs::class)->where('user_id',$userId)->first();
    }


    /**
     * 创建时间
     * @param $value
     * @return string
     */
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i');
    }


    /**
     * 发布时间
     * @param $value
     * @return string
     */
    public function getReleaseTimeAttribute($value) {
        return Carbon::parse($value)->format('Y-m-d H:i');
    }


    /**
     * 接受者
     * @return string[]
     */
    public function accept() {
        switch ($this->range) {
            case self::RANGE_ALL:
                return ['学生','教师'];
            case self::RANGE_TEACHER:
                return ['教师'];
            case self::RANGE_STUDENT:
                return ['学生'];

        }
    }

    /**
     * 课件范围
     * @return array
     */
    public function range() {
        $data = [];
        if($this->range == self::RANGE_ALL) {
            $data['teacher'] = $this->teacherRange();
            $data['student'] = $this->studentRange();
        }
        if($this->range == self::RANGE_TEACHER) {
            $data['teacher'] = $this->teacherRange();
        }
        if($this->range == self::RANGE_STUDENT) {
            $data['student'] = $this->studentRange();
        }
        return $data;
    }


    /**
     * 教师可见范围
     * @return array
     */
    public function teacherRange() {
        $organizations = $this->selectedOrganizations;
        $org = [];
        foreach ($organizations as $key => $item) {
            if($item->organization_id == 0) {
                $org[] = ['id'=>0, 'name'=>'全部部门'];
            } else {
                $organization = $item->organization;
                $org[] = [
                    'id'=>$organization->id,
                    'name'=>$organization->name
                ];
            }
        }
        return $org;
    }


    /**
     * 学生范围
     * @return array
     */
    public function studentRange() {
        $grades = $this->grades;
        $gra = [];
        foreach ($grades as $key => $item) {
            if($item->grade_id == 0) {
                $gra[] = ['id'=>0, 'name'=>'全部班级'];
            } else {
                $grade = $item->grade;
                $gra[] = ['id'=>$grade->id, 'name'=>$grade->name];
            }
        }
        return $gra;
    }
}
