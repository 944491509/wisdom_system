import {Util} from "./utils";
import {Constants} from "./constants";

/**
 * 根据用户来加载所有可能的组织机构
 * @param schoolId
 * @param userUuid
 * @param roles
 * @param affix
 * @returns Promise
 */
export function loadOrganizationsByRoles(schoolId, userUuid, roles, affix) {
    const url = Util.buildUrl(Constants.API.ORGANIZATION.LOAD_BY_ROLES);
    if(Util.isDevEnv()){
        return axios.get(url, affix);
    }
    return axios.post(
        url,
        {school: schoolId, user: userUuid, roles: roles, version:Constants.VERSION}
    );
}

export function loadOrganizationsByParent(parentId, keyword,affix) {
    const url = Util.buildUrl(Constants.API.ORGANIZATION.LOAD_BY_PARENT);
    const dom = document.getElementById('app-init-data-holder');
    const schoolId = dom.dataset.school;
    console.log(schoolId);
    if(Util.isDevEnv()){
        return axios.get(url, affix);
    }
    return axios.post(
        url,
        {parent_id: parentId, keyword: keyword, type: 1, version:Constants.VERSION,school_id:schoolId}
    );
}