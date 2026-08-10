import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    withCredentials: true,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

// Attach Sanctum token from localStorage
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Handle 401 globally
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

export default api;

// ─── Auth ────────────────────────────────────────────────────────────────────
export const authApi = {
    login: (data) => api.post('/auth/login', data),
    logout: () => api.post('/auth/logout'),
    me: () => api.get('/auth/me'),
};

// ─── Dashboard ───────────────────────────────────────────────────────────────
export const dashboardApi = {
    admin: () => api.get('/dashboard/admin'),
    intern: () => api.get('/dashboard/intern'),
    instructor: () => api.get('/dashboard/instructor'),
};

// ─── Courses ─────────────────────────────────────────────────────────────────
export const courseApi = {
    list: (params) => api.get('/courses', { params }),
    get: (id) => api.get(`/courses/${id}`),
    create: (data) => api.post('/courses', data),
    update: (id, data) => api.put(`/courses/${id}`, data),
    delete: (id) => api.delete(`/courses/${id}`),
    enroll: (id, data) => api.post(`/courses/${id}/enroll`, data),
    myProgress: (id) => api.get(`/courses/${id}/progress`),
};

// ─── Modules ─────────────────────────────────────────────────────────────────
export const moduleApi = {
    list: (params) => api.get('/modules', { params }),
    get: (id) => api.get(`/modules/${id}`),
    create: (data) => api.post('/modules', data),
    update: (id, data) => api.put(`/modules/${id}`, data),
    delete: (id) => api.delete(`/modules/${id}`),
};

// ─── Lessons ─────────────────────────────────────────────────────────────────
export const lessonApi = {
    get: (id) => api.get(`/lessons/${id}`),
    create: (data) => api.post('/lessons', data),
    update: (id, data) => api.put(`/lessons/${id}`, data),
    delete: (id) => api.delete(`/lessons/${id}`),
    markComplete: (id) => api.post(`/lessons/${id}/complete`),
};

// ─── Resources ───────────────────────────────────────────────────────────────
export const resourceApi = {
    list: (params) => api.get('/resources', { params }),
    get: (id) => api.get(`/resources/${id}`),
    create: (data) => api.post('/resources', data),
    update: (id, data) => api.put(`/resources/${id}`, data),
    delete: (id) => api.delete(`/resources/${id}`),
    markComplete: (id) => api.post(`/resources/${id}/complete`),
};

// ─── Exams ───────────────────────────────────────────────────────────────────
export const examApi = {
    list: (params) => api.get('/exams', { params }),
    get: (id) => api.get(`/exams/${id}`),
    create: (data) => api.post('/exams', data),
    update: (id, data) => api.put(`/exams/${id}`, data),
    delete: (id) => api.delete(`/exams/${id}`),
    publish: (id) => api.post(`/exams/${id}/publish`),
    unpublish: (id) => api.post(`/exams/${id}/unpublish`),
    assign: (id, data) => api.post(`/exams/${id}/assign`, data),
};

// ─── Questions ───────────────────────────────────────────────────────────────
export const questionApi = {
    list: (params) => api.get('/questions', { params }),
    create: (data) => api.post('/questions', data),
    bulkUpload: (examId, data) => api.post(`/exams/${examId}/bulk-questions`, data),
    update: (id, data) => api.put(`/questions/${id}`, data),
    delete: (id) => api.delete(`/questions/${id}`),
};

// ─── Exam Attempts ───────────────────────────────────────────────────────────
export const attemptApi = {
    start: (examId) => api.post(`/exams/${examId}/start`),
    timeRemaining: (id) => api.get(`/exam-attempts/${id}/time-remaining`),
    saveAnswer: (id, data) => api.post(`/exam-attempts/${id}/save-answer`, data),
    submit: (id) => api.post(`/exam-attempts/${id}/submit`),
    get: (id) => api.get(`/exam-attempts/${id}`),
};

// ─── Users ───────────────────────────────────────────────────────────────────
export const userApi = {
    list: (params) => api.get('/users', { params }),
    get: (id) => api.get(`/users/${id}`),
    create: (data) => api.post('/users', data),
    update: (id, data) => api.put(`/users/${id}`, data),
    delete: (id) => api.delete(`/users/${id}`),
    resetPassword: (id, data) => api.post(`/users/${id}/reset-password`, data),
};

// ─── Announcements ───────────────────────────────────────────────────────────
export const announcementApi = {
    list: (params) => api.get('/announcements', { params }),
    create: (data) => api.post('/announcements', data),
    update: (id, data) => api.put(`/announcements/${id}`, data),
    delete: (id) => api.delete(`/announcements/${id}`),
};

// ─── Certificates ────────────────────────────────────────────────────────────
export const certificateApi = {
    list: (params) => api.get('/certificates', { params }),
    issue: (data) => api.post('/certificates', data),
    download: (id) => api.get(`/certificates/${id}/download`, { responseType: 'blob' }),
    delete: (id) => api.delete(`/certificates/${id}`),
};

// ─── Reports ─────────────────────────────────────────────────────────────────
export const reportApi = {
    internPerformance: (params) => api.get('/reports/intern-performance', { params }),
    courseCompletion: () => api.get('/reports/course-completion'),
    examPerformance: () => api.get('/reports/exam-performance'),
    export: (type) => api.get('/reports/export', { params: { type }, responseType: 'blob' }),
};
