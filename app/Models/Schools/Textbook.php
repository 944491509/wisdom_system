<?php

namespace App\Models\Schools;

use App\Models\Course;
use App\Models\Courses\CourseTextbook;
use Illuminate\Database\Eloquent\Model;
use function Complex\sec;

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
        'name', 'press', 'author', 'edition', 'school_id', 'type',
        'purchase_price', 'price', 'introduce', 'year', 'term'
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
    const TYPE_MISC_TEXT   = '辅助教材';
    const TYPE_SCHOOL_BOOK_TEXT   = '校本教材';


    const TERM_1 = '第一学期';
    const TERM_2 = '第二学期';

    const YEAR_1 = '一年级';
    const YEAR_2 = '二年级';
    const YEAR_3 = '三年级';
    const YEAR_4 = '四年级';
    const YEAR_5 = '五年级';


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


    public function getTermTextAttribute() {
        $term  = [
            1=>self::TERM_1,
            2=>self::TERM_2,
        ];
        return $term[$this->term] ?? '';
    }

    /**
     * @return string
     */
    public function getYearTextAttribute() {
        $year = [
            1=>self::YEAR_1,
            2=>self::YEAR_2,
            3=>self::YEAR_3,
            4=>self::YEAR_4,
            5=>self::YEAR_5,
        ];
        return $year[$this->year];
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
