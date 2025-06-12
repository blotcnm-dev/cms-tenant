import { apiRequest } from "../utils/api_request";

export const logout = async (url) => {
    try {
        const isLogout = await apiRequest(url);
        return isLogout;
    } catch (error) {
        console.error("로그아웃 처리 중 에러 발생:", error);
        return false;
    }
}
