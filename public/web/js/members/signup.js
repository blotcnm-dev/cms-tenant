import {
    validateUserName,
    validateEmail,
    validatePhone,
    validateUserId,
    validatePassword,
    validateNickname,
    validateBirthday
} from '../utils/validate.js';

const handleImageUpload = () => {
    const imageInput = document.getElementById('user_profile');
    const imagePreview = document.querySelector('.profile_image_container img');
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    imagePreview.src = e.target.result;
                    document.getElementById('profile_image_change').value = '1';
                };
                reader.readAsDataURL(file);
            }
        });
    }
};

const setupSelectBoxToggling = () => {
    const selectBoxContainers = document.querySelectorAll('.input_item_container fieldset .select_box_container');
    selectBoxContainers.forEach(container => {
        container.addEventListener('click', () => {
            container.classList.toggle('visible');
        });
    });
    return selectBoxContainers;
};

const handlePhoneSelect = (selectBoxContainers) => {
    const phoneSelectContainer = selectBoxContainers[0]; // 첫 번째가 전화번호용
    if (phoneSelectContainer) {
        const phoneOptions = phoneSelectContainer.querySelectorAll('.select_option_list button');
        const selectOption = document.querySelector('.select_option'); // 선택된 옵션 텍스트 표시 요소
        const phoneProviderHidden = document.getElementById('user_phone_provider');
        phoneOptions.forEach(button => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                const provider = button.textContent.trim();
                if (selectOption) {
                    selectOption.textContent = provider;
                }
                if (phoneProviderHidden) {
                    phoneProviderHidden.value = provider;
                }
                // 옵션 선택 후 dropdown 닫기
                phoneSelectContainer.classList.toggle('visible');
            });
        });
    }
};

const updateEmailCombined = () => {
    const emailLocalInput = document.getElementById('user_email_local');
    const emailDomainInput = document.getElementById('user_email_domain');
    const fullEmailField = document.getElementById('user_email');
    const emailError = document.getElementById('user_emailError');
    if (emailLocalInput && emailDomainInput) {
        const fullEmail = emailLocalInput.value + '@' + emailDomainInput.value;
        if (fullEmailField) {
            fullEmailField.value = fullEmail;
        }
        if (emailError) {
            emailError.textContent = validateEmail(fullEmail);
        }
    }
};

const setupEmailListeners = () => {
    const emailLocalInput = document.getElementById('user_email_local');
    const emailDomainInput = document.getElementById('user_email_domain');
    if (emailLocalInput) {
        emailLocalInput.addEventListener('input', updateEmailCombined);
    }
    if (emailDomainInput) {
        emailDomainInput.addEventListener('input', updateEmailCombined);
    }
};

const handleEmailSelect = (selectBoxContainers, updateEmailFn) => {
    const emailSelectContainer = selectBoxContainers[1]; // 두 번째가 이메일용
    if (emailSelectContainer) {
        const emailOptions = emailSelectContainer.querySelectorAll('.select_option_list button');
        const emailDomainInput = document.getElementById('user_email_domain');
        const emailProviderHidden = document.getElementById('user_email_provider');
        emailOptions.forEach(button => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                const domain = button.textContent.trim();
                if (emailDomainInput) {
                    emailDomainInput.value = domain;
                }
                if (emailProviderHidden) {
                    emailProviderHidden.value = domain;
                }
                updateEmailFn();
            });
        });
    }
};

const setupValidations = () => {
    const addValidation = (inputId, validateFunction) => {
        const inputEl = document.getElementById(inputId);
        if (inputEl) {
            const container = inputEl.closest('.input_item_container');
            if (container) {
                const errorEl = container.querySelector('.error_notice');
                if (errorEl) {
                    inputEl.addEventListener('input', (event) => {
                        errorEl.textContent = validateFunction(event.target.value);
                    });
                }
            }
        }
    };

    addValidation('user_id', validateUserId);
    addValidation('user_password', validatePassword);
    addValidation('user_name', validateUserName);
    addValidation('user_nickname', validateNickname);
    addValidation('user_birth', validateBirthday);
    addValidation('user_phone', validatePhone);
};

const setupPasswordToggle = () => {
    const passwordInput = document.getElementById('user_password');
    const toggleButton = document.querySelector('.password_button_container button[aria-label="password_visible"]');
    if (passwordInput && toggleButton) {
        toggleButton.addEventListener('click', () => {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.classList.remove('off');
                toggleButton.classList.add('on');
            } else {
                passwordInput.type = 'password';
                toggleButton.classList.remove('on');
                toggleButton.classList.add('off');
            }
        });
    }
};


const signupController = () => {
    handleImageUpload();
    // const selectBoxContainers = setupSelectBoxToggling();
    // handlePhoneSelect(selectBoxContainers);
    // setupEmailListeners();
    // handleEmailSelect(selectBoxContainers, updateEmailCombined);
    // setupValidations();
    // setupPasswordToggle();
};

document.addEventListener('DOMContentLoaded', signupController);
