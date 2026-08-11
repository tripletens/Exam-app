import React, { Suspense, lazy } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import ProtectedRoute from './components/ProtectedRoute';
import AdminLayout from './layouts/AdminLayout';
import InternLayout from './layouts/InternLayout';
import useAuthStore from './store/authStore';
import { Loader2 } from 'lucide-react';

// Pages
const Login = lazy(() => import('./pages/Login'));

// Admin Pages
const AdminDashboard = lazy(() => import('./pages/admin/AdminDashboard'));
const AdminCourses = lazy(() => import('./pages/admin/AdminCourses'));
const AdminModules = lazy(() => import('./pages/admin/AdminModules'));
const AdminLessons = lazy(() => import('./pages/admin/AdminLessons'));
const AdminResources = lazy(() => import('./pages/admin/AdminResources'));
const AdminInterns = lazy(() => import('./pages/admin/AdminInterns'));
const InternProfile = lazy(() => import('./pages/admin/InternProfile'));
const AdminExams = lazy(() => import('./pages/admin/AdminExams'));
const AdminQuestions = lazy(() => import('./pages/admin/AdminQuestions'));
const AdminExamQuestions = lazy(() => import('./pages/admin/AdminExamQuestions'));
const AdminAttempts = lazy(() => import('./pages/admin/AdminAttempts'));
const AdminResults = lazy(() => import('./pages/admin/AdminResults'));
const AdminAnnouncements = lazy(() => import('./pages/admin/AdminAnnouncements'));
const AdminCertificates = lazy(() => import('./pages/admin/AdminCertificates'));
const AdminReports = lazy(() => import('./pages/admin/AdminReports'));
const AdminSettings = lazy(() => import('./pages/admin/AdminSettings'));

// Intern Pages
const InternDashboard = lazy(() => import('./pages/intern/InternDashboard'));
const MyCourses = lazy(() => import('./pages/intern/MyCourses'));
const CourseDetail = lazy(() => import('./pages/intern/CourseDetail'));
const MyExams = lazy(() => import('./pages/intern/MyExams'));
const ExamInstructions = lazy(() => import('./pages/intern/ExamInstructions'));
const TakeExam = lazy(() => import('./pages/intern/TakeExam'));
const ExamResults = lazy(() => import('./pages/intern/ExamResults'));
const MyCertificates = lazy(() => import('./pages/intern/MyCertificates'));

const PageLoader = () => (
    <div className="flex items-center justify-center h-screen bg-gray-950">
        <Loader2 className="animate-spin text-indigo-500" size={32} />
    </div>
);

// Fallback redirect component for unknown routes
const CatchAllRedirect = () => {
    const { user, isAuthenticated } = useAuthStore();
    if (isAuthenticated && user) {
        let userRole = 'intern';
        if (typeof user.role === 'string') userRole = user.role;
        else if (Array.isArray(user.role) && user.role.length > 0) userRole = typeof user.role[0] === 'string' ? user.role[0] : (user.role[0].value || user.role[0].name || 'intern');
        else if (typeof user.role === 'object' && user.role !== null) userRole = user.role.value || user.role.name || 'intern';

        if (userRole === 'super_admin') return <Navigate to="/admin" replace />;
        if (userRole === 'instructor') return <Navigate to="/instructor" replace />;
        return <Navigate to="/intern" replace />;
    }
    return <Navigate to="/login" replace />;
};

function App() {
    return (
        <BrowserRouter>
            <Suspense fallback={<PageLoader />}>
                <Routes>
                    {/* Public */}
                    <Route path="/login" element={<Login />} />
                    <Route path="/" element={<CatchAllRedirect />} />

                    {/* Admin routes */}
                    <Route path="/admin" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminDashboard /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/courses" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminCourses /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/courses/:id/modules" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminModules /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/modules" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminModules /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/modules/:id/lessons" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminLessons /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/lessons" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminLessons /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/resources" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminResources /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/learning-paths" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminCourses /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/exams" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminExams /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/questions" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminQuestions /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/exams/:id/questions" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminExamQuestions /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/interns" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminInterns /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/interns/:id" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><InternProfile /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/performance" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminReports /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/attempts" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminAttempts /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/results" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminResults /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/announcements" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminAnnouncements /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/certificates" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminCertificates /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/reports" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminReports /></AdminLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/admin/settings" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminSettings /></AdminLayout>
                        </ProtectedRoute>
                    } />

                    {/* Intern routes */}
                    <Route path="/intern" element={
                        <ProtectedRoute allowedRoles={['intern']}>
                            <InternLayout><InternDashboard /></InternLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/intern/courses" element={
                        <ProtectedRoute allowedRoles={['intern']}>
                            <InternLayout><MyCourses /></InternLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/intern/courses/:id" element={
                        <ProtectedRoute allowedRoles={['intern']}>
                            <InternLayout><CourseDetail /></InternLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/intern/exams" element={
                        <ProtectedRoute allowedRoles={['intern']}>
                            <InternLayout><MyExams /></InternLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/intern/exams/:id/instructions" element={
                        <ProtectedRoute allowedRoles={['intern']}>
                            <InternLayout><ExamInstructions /></InternLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/intern/exams/take/:attemptId" element={
                        <ProtectedRoute allowedRoles={['intern']}>
                            <TakeExam />
                        </ProtectedRoute>
                    } />
                    <Route path="/intern/results/:attemptId" element={
                        <ProtectedRoute allowedRoles={['intern']}>
                            <InternLayout><ExamResults /></InternLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/intern/certificates" element={
                        <ProtectedRoute allowedRoles={['intern']}>
                            <InternLayout><MyCertificates /></InternLayout>
                        </ProtectedRoute>
                    } />

                    {/* Catch-all route for unknown URLs */}
                    <Route path="*" element={<CatchAllRedirect />} />
                </Routes>
            </Suspense>
            <ToastContainer position="top-right" autoClose={3000} theme="dark" />
        </BrowserRouter>
    );
}

export default App;
