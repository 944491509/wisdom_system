<?php

namespace App\Models\Schools;

use App\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @property integer $id
 * @property string $facility_number
 * @property string $facility_name
 * @property int $school_id
 * @property int $campuse_id
 * @property int $building_id
 * @property int $room_id
 * @property string $detail_addr
 * @property boolean $status
 * @property boolean $type
 */
class Facility extends Model
{
    use SoftDeletes;

    const LOCATION_INDOOR = 1;
    const LOCATION_OUTDOOR = 2;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'facilitys';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['facility_number','location', 'facility_name', 'school_id', 'campus_id', 'building_id', 'room_id', 'detail_addr', 'status', 'type', 'card_type', 'grade_id'];


    const TYPE_MONITORING  = 1;
    const TYPE_ENTRANCE_GUARD  = 2;
    const TYPE_CLASS_SIGN  = 3;
    const TYPE_CLASS_CLASSROOM  = 4;

    const STATUS_OPEN  = 1;
    const STATUS_CLOSE = 0;

    const STATUS_OPEN_TEXT  = '开启';
    const STATUS_CLOSE_TEXT = '关闭';

    const TYPE_MONITORING_TEXT  = '监控设备';
    const TYPE_ENTRANCE_GUARD_TEXT  = '门禁设备';
    const TYPE_CLASS_SIGN_TEXT  = '班牌设备';
    const TYPE_CLASS_CLASSROOM_TEXT  = '教室设备';

    const  CARD_TYPE_PUBLIC  = 0;
    const  CARD_TYPE_PRIVATE = 1;
    const  CARD_TYPE_PUBLIC_TEXT = '公共班牌';
    const  CARD_TYPE_PRIVATE_TEXT = '私有班牌';

    public function campus() {
        return $this->belongsTo(Campus::class, 'campus_id', 'id');
    }

    public function room() {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }


    public function building() {
        return $this->belongsTo(Building::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * 获取type属性
     * @return string
     */
    public function getTypeTextAttribute() {
        switch ($this->type) {
            case self::TYPE_MONITORING:
                return self::TYPE_MONITORING_TEXT;break;
            case self::TYPE_ENTRANCE_GUARD:
                return self::TYPE_ENTRANCE_GUARD_TEXT;break;
            case self::TYPE_CLASS_SIGN:
                return self::TYPE_CLASS_SIGN_TEXT;break;
            case self::TYPE_CLASS_CLASSROOM:
                return self::TYPE_CLASS_CLASSROOM_TEXT;
            default : return '';

        }
    }

    public function getCardTypeTextAttribute()
    {
        switch ($this->card_type) {
            case self::CARD_TYPE_PUBLIC:
                return self::CARD_TYPE_PUBLIC_TEXT;break;
            case self::CARD_TYPE_PRIVATE:
                return self::CARD_TYPE_PRIVATE_TEXT;break;
            default : return '';
        }
    }
}
