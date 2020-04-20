<?php

namespace App\Http\Requests;

class SchoolRequest extends MyStandardRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    public function getConfiguration(){
        return $this->get('config');
    }

    /**
     * 获取选修课有效期的表单
     * @param $term
     * @return array
     */
    public function getElectiveCourseAvailableTerm($term){
        return $this->get('ec'.$term,null);
    }

    /**
     * 获取指定学期的起始日期
     * @return array
     */
    public function getTermStart(){
        return $this->get('term_start',null);
    }

    /**
     * 获取夏季作息的起始日期
     * @return array
     */
    public function getSummerStart(){
        return $this->get('summer_start_date',null);
    }

    /**
     * 获取冬季作息的起始日期
     * @return array
     */
    public function getWinterStart(){
        return $this->get('winter_start_date',null);
    }


    /**
     * 获取schoolId
     * @return mixed
     */
    public function getSchoolId(){
        return $this->get('school_id');
    }


    /**
     * @return mixed
     */
    public function getUserId() {
        return $this->get('user_id');
    }
}
