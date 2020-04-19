import {Constants} from "./constants";

export function getTimeSlots(schoolId, noTime, year) {
    if(noTime){
        noTime = true;
    }else{
        noTime = false;
    }
    return axios.post(
        Constants.API.LOAD_STUDY_TIME_SLOTS_BY_SCHOOL,{school: schoolId, no_time: noTime, year}
    );
}

export function saveTimeSlot(schoolUuid, timeSlot) {
    return axios.post(
        Constants.API.SAVE_TIME_SLOT,{school: schoolUuid, timeSlot: timeSlot}
    );
}

export function getCourses(schoolId, page) {
    return axios.post(
        Constants.API.LOAD_COURSES_BY_SCHOOL,{school: schoolId, page: page}
    );
}

export function getMajors(schoolId, pageNumber) {
    return axios.post(
        Constants.API.LOAD_MAJORS_BY_SCHOOL,{id: schoolId, pageNumber: pageNumber}
    );
}