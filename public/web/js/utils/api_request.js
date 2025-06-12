import axios from 'axios';

const apiClient = axios.create({
    // baseURL: 'https://api.example.com',
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
    },
});

apiClient.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        console.error('요청 인터셉터 에러:', error);
        return Promise.reject(error);
    }
);

apiClient.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response) {
            const { status, statusText, data } = error.response;
            console.error(`HTTP ${status} ${statusText}: ${data.message || 'Error occurred'}`);
        } else if (error.request) {
            console.error('응답 없음:', error.request);
        } else {
            console.error('설정 에러:', error.message);
        }
        return Promise.reject(error);
    }
);

export const apiRequest = async (url, options = {}) => {
    const defaultOptions = {
        method: 'GET',
        url,
    };

    const config = { ...defaultOptions, ...options };

    try {
        const response = await apiClient(config);
        return response.data;
    } catch (error) {
        throw error;
    }
};
