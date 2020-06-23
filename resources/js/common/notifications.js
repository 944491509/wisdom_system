/**
 * 根据学校的 ID 获取所有的校区与建筑的集合
 * @param schoolId
 * @param affix
 * @returns Promise
 */
export function loadMessages(schoolId, affix) {
  return axios.get(
    '/api/notification/unread-news',
  );
}
