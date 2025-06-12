import { apiRequest } from "../utils/api_request.js";

export const logincheck = async () => {
    try {
        const isLogin = await apiRequest('url');
        return isLogin;
    } catch (error) {
        console.error("로그인 체크 중 에러 발생:", error);
        return false;
    }
}
