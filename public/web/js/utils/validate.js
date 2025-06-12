export const validateUserName = (userName) => {
    if (userName === "") return "";
    const nameRegex = /^[가-힣a-zA-Z]{2,}$/;
    if (!nameRegex.test(userName)) {
        return "이름은 2자 이상이며, 숫자나 특수문자가 포함될 수 없습니다.";
    }
    return "";
}

export const validateEmail = (email) => {
    if (email === "") return "";
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        return "올바른 이메일 주소를 입력하세요.";
    }
    return "";
}

export const validatePhone = (phone) => {
    if (phone === "") return "";
    const phoneRegex = /^(01[016789])[-]?\d{3,4}[-]?\d{4}$/;
    if (!phoneRegex.test(phone)) {
        return "올바른 전화번호를 입력하세요.";
    }
    return "";
}

export const  validateUserId = (userId) => {
    if (userId === "") return "";
    const userIdRegex = /^[a-zA-Z0-9_]{4,20}$/;
    if (!userIdRegex.test(userId)) {
        return "아이디는 4자 이상 20자 이하의 영문, 숫자, 밑줄만 사용할 수 있습니다.";
    }
    return "";
}

// 비밀번호 검증 함수 (최소 8자, 영문과 숫자 포함)
export const validatePassword = (password) => {
    if (password === "") return "";
    const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d!@#$%^&*()]{8,}$/;
    if (!passwordRegex.test(password)) {
        return "비밀번호는 최소 8자 이상이며, 영문과 숫자를 포함해야 합니다.";
    }
    return "";
}

export const validateNickname = (nickname) => {
    if (nickname === "") return "";
    const nicknameRegex = /^[a-zA-Z0-9가-힣_]{2,10}$/;
    if (!nicknameRegex.test(nickname)) {
        return "닉네임은 2자 이상 10자 이하의 한글, 영문, 숫자, 밑줄만 사용할 수 있습니다.";
    }
    return "";
};

export const validateBirthday = (birthday) => {
    if (birthday === "") return "";
    // 기본 형식 체크: YYYYMMDD 형식
    const birthdayRegex = /^\d{4}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])$/;
    if (!birthdayRegex.test(birthday)) {
        return "생년월일은 YYYYMMDD 형식이어야 합니다.";
    }
    // 입력값 (예: "19880510")을 "1988-05-10" 형식으로 변환 후 날짜 객체 생성
    const formatted = `${birthday.slice(0, 4)}-${birthday.slice(4, 6)}-${birthday.slice(6, 8)}`;
    const date = new Date(formatted);
    if (isNaN(date.getTime())) {
        return "유효한 생년월일을 입력하세요.";
    }
    return "";
};
