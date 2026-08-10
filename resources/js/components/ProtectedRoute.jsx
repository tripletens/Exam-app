import React from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import useAuthStore from '../store/authStore';

export default function ProtectedRoute({ children, allowedRoles }) {
    const { user, isAuthenticated } = useAuthStore();
    const location = useLocation();

    if (!isAuthenticated || !user) {
        return <Navigate to="/login" state={{ from: location }} replace />;
    }

    const userRole = typeof user.role === 'object' ? user.role.value : user.role;

    if (allowedRoles && !allowedRoles.includes(userRole)) {
        // Redirect to correct dashboard
        if (userRole === 'super_admin') return <Navigate to="/admin" replace />;
        if (userRole === 'instructor') return <Navigate to="/instructor" replace />;
        return <Navigate to="/intern" replace />;
    }

    return children;
}
