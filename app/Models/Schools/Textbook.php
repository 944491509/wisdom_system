<?php

namespace App\Models\Schools;

use App\Models\Course;
use App\Models\Courses\CourseTextbook;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $press
 * @property string $author
 * @property string $edition
 * @property int $course_id
 * @property int $school_id
 * @property int $type
 * @property float $purchase_price
 * @property float $price
 * @property string $introduce
 * @property int $status
 */
class Textbook extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'press',
        'author',
        'edition',
        'school_id',
        'type',
        'purchase_price',
        'price',
        'introduce'
    ];

    protected $hidden = ['updated_at', 'deleted_at'];

    const TYPE_MAJOR  = 1;
    const TYPE_COMMON = 2;
    const TYPE_SELECT = 3;
    const TYPE_MISC   = 4;
    const TYPE_SCHOOL_BOOK   = 5;

    const TYPE_MAJOR_TEXT  = '专业教材';
    const TYPE_COMMON_TEXT = '普通教材';
    const TYPE_SELECT_TEXT = '选读教材';
    const TYPE_MISC_TEXT   = '辅助材料';
    const TYPE_SCHOOL_BOOK_TEXT   = '校本教材';


    /**
     * 教材类型
     * @return string[]
     */
    public function getAllType() {
        return [
            self::TYPE_MAJOR => self::TYPE_MAJOR_TEXT,
            self::TYPE_COMMON => self::TYPE_COMMON_TEXT,
            self::TYPE_SELECT => self::TYPE_SELECT_TEXT,
            self::TYPE_MISC => self::TYPE_MISC_TEXT,
            self::TYPE_SCHOOL_BOOK => self::TYPE_SCHOOL_BOOK_TEXT,
        ];
    }


    /**
     * 获取type属性
     * @return string
     */
    public function getTypeTextAttribute() {
        $all = $this->getAllType();
        return $all[$this->type] ?? '';
    }

    /**
     * 图书关联的图片
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function medias(){
        return $this->hasMany(TextbookImage::class)->orderBy('position','asc');
    }

    /**
     * 使用此教材的课程
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function courses(){
        return $this->hasMany(CourseTextbook::class);
    }
}
