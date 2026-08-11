import React from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import useAuthStore from '../store/authStore';

export default function ProtectedRoute({ children, allowedRoles }) {
    const { user, isAuthenticated } = useAuthStore();
    const location = useLocation();

    if (!isAuthenticated || !user) {
        return <Navigate to="/login" state={{ from: location }} replace />;
    }

    // Robust role extraction handling strings, objects, and arrays
    let userRole = 'intern';
    if (typeof user.role === 'string') {
        userRole = user.role;
    } else if (Array.isArray(user.role) && user.role.length > 0) {
        userRole = typeof user.role[0] === 'string' ? user.role[0] : (user.role[0].value || user.role[0].name || 'intern');
    } else if (typeof user.role === 'object' && user.role !== null) {
        userRole = user.role.value || user.role.name || 'intern';
    } else if (Array.isArray(user.roles) && user.roles.length > 0) {
        userRole = typeof user.roles[0] === 'string' ? user.roles[0] : (user.roles[0].name || user.roles[0].value || 'intern');
    }

    if (allowedRoles && !allowedRoles.includes(userRole)) {
        // Redirect to correct dashboard based on role
        if (userRole === 'super_admin') return <Navigate to="/admin" replace />;
        if (userRole === 'instructor') return <Navigate to="/instructor" replace />;
        return <Navigate to="/intern" replace />;
    }

    return children;
}
