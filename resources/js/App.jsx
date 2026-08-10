import React, { Suspense, lazy } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import ProtectedRoute from './components/ProtectedRoute';
import AdminLayout from './layouts/AdminLayout';
import InternLayout from './layouts/InternLayout';
import { Loader2 } from 'lucide-react';

// Pages — lazy loaded for performance
const Login = lazy(() => import('./pages/Login'));

// Admin
const AdminDashboard = lazy(() => import('./pages/admin/AdminDashboard'));
const AdminCourses = lazy(() => import('./pages/admin/AdminCourses'));
const AdminInterns = lazy(() => import('./pages/admin/AdminInterns'));
const InternProfile = lazy(() => import('./pages/admin/InternProfile'));
const AdminExams = lazy(() => import('./pages/admin/AdminExams'));

// Intern
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

function App() {
    return (
        <BrowserRouter>
            <Suspense fallback={<PageLoader />}>
                <Routes>
                    {/* Public */}
                    <Route path="/login" element={<Login />} />
                    <Route path="/" element={<Navigate to="/login" replace />} />

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
                    <Route path="/admin/exams" element={
                        <ProtectedRoute allowedRoles={['super_admin']}>
                            <AdminLayout><AdminExams /></AdminLayout>
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

                    {/* Catch-all */}
                    <Route path="*" element={<Navigate to="/login" replace />} />
                </Routes>
            </Suspense>

            <ToastContainer
                position="top-right"
                autoClose={3500}
                hideProgressBar={false}
                theme="dark"
                toastStyle={{ background: '#1f2937', border: '1px solid #374151', color: '#f3f4f6' }}
            />
        </BrowserRouter>
    );
}

export default App;
