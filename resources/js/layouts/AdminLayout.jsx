import React, { useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import useAuthStore from '../store/authStore';
import { authApi } from '../api';
import {
    LayoutDashboard, BookOpen, Layers, FileText, Link2, Map,
    ClipboardList, HelpCircle, Activity, BarChart2,
    Users, TrendingUp, Award, Megaphone, LineChart, Settings,
    ChevronDown, LogOut, Menu, X, Bell, User
} from 'lucide-react';

const navSections = [
    {
        label: 'Overview',
        items: [
            { label: 'Dashboard', href: '/admin', icon: LayoutDashboard },
        ]
    },
    {
        label: 'Training',
        items: [
            { label: 'Courses', href: '/admin/courses', icon: BookOpen },
            { label: 'Modules', href: '/admin/modules', icon: Layers },
            { label: 'Lessons', href: '/admin/lessons', icon: FileText },
            { label: 'Resources', href: '/admin/resources', icon: Link2 },
            { label: 'Learning Paths', href: '/admin/learning-paths', icon: Map },
        ]
    },
    {
        label: 'Assessment',
        items: [
            { label: 'Exams', href: '/admin/exams', icon: ClipboardList },
            { label: 'Questions', href: '/admin/questions', icon: HelpCircle },
            { label: 'Attempts', href: '/admin/attempts', icon: Activity },
            { label: 'Results', href: '/admin/results', icon: BarChart2 },
        ]
    },
    {
        label: 'Interns',
        items: [
            { label: 'All Interns', href: '/admin/interns', icon: Users },
            { label: 'Performance', href: '/admin/performance', icon: TrendingUp },
            { label: 'Certificates', href: '/admin/certificates', icon: Award },
        ]
    },
    {
        label: 'Communications',
        items: [
            { label: 'Announcements', href: '/admin/announcements', icon: Megaphone },
            { label: 'Reports', href: '/admin/reports', icon: LineChart },
        ]
    },
    {
        label: 'System',
        items: [
            { label: 'Settings', href: '/admin/settings', icon: Settings },
        ]
    },
];

export default function AdminLayout({ children }) {
    const [sidebarOpen, setSidebarOpen] = useState(true);
    const [loggingOut, setLoggingOut] = useState(false);
    const { user, clearAuth } = useAuthStore();
    const location = useLocation();
    const navigate = useNavigate();

    const handleLogout = async () => {
        setLoggingOut(true);
        try {
            await authApi.logout();
        } catch (_) {}
        clearAuth();
        navigate('/login');
        toast.success('Logged out successfully');
    };

    const isActive = (href) => {
        if (href === '/admin') return location.pathname === '/admin';
        return location.pathname.startsWith(href);
    };

    return (
        <div className="flex h-screen bg-gray-950 overflow-hidden">
            {/* Sidebar */}
            <aside className={`${sidebarOpen ? 'w-64' : 'w-0 overflow-hidden'} flex-shrink-0 flex flex-col bg-gray-900 border-r border-gray-800 transition-all duration-300`}>
                {/* Logo */}
                <div className="h-16 flex items-center px-5 border-b border-gray-800 flex-shrink-0">
                    <div className="flex items-center gap-3">
                        <div className="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-500/30">
                            <span className="text-white font-bold text-sm">L</span>
                        </div>
                        <div>
                            <div className="text-white font-bold text-sm leading-tight">Lythub</div>
                            <div className="text-gray-500 text-xs">Technologies</div>
                        </div>
                    </div>
                </div>

                {/* Navigation */}
                <nav className="flex-1 overflow-y-auto py-4 px-3 space-y-6 scrollbar-thin">
                    {navSections.map((section) => (
                        <div key={section.label}>
                            <p className="px-3 mb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                {section.label}
                            </p>
                            <div className="space-y-0.5">
                                {section.items.map((item) => (
                                    <Link
                                        key={item.href}
                                        to={item.href}
                                        className={`sidebar-link ${isActive(item.href) ? 'active' : ''}`}
                                    >
                                        <item.icon size={16} />
                                        {item.label}
                                    </Link>
                                ))}
                            </div>
                        </div>
                    ))}
                </nav>

                {/* User footer */}
                <div className="border-t border-gray-800 p-3">
                    <div className="flex items-center gap-3 px-2 py-2">
                        <div className="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <span className="text-white text-xs font-bold">{user?.name?.[0]?.toUpperCase()}</span>
                        </div>
                        <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-gray-200 truncate">{user?.name}</p>
                            <p className="text-xs text-gray-500">Super Admin</p>
                        </div>
                        <button
                            onClick={handleLogout}
                            disabled={loggingOut}
                            className="p-1.5 text-gray-500 hover:text-red-400 transition-colors"
                            title="Logout"
                        >
                            <LogOut size={15} />
                        </button>
                    </div>
                </div>
            </aside>

            {/* Main */}
            <div className="flex-1 flex flex-col overflow-hidden">
                {/* Topbar */}
                <header className="h-16 bg-gray-900 border-b border-gray-800 flex items-center justify-between px-6 flex-shrink-0">
                    <button
                        onClick={() => setSidebarOpen(!sidebarOpen)}
                        className="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors"
                    >
                        {sidebarOpen ? <X size={18} /> : <Menu size={18} />}
                    </button>
                    <div className="flex items-center gap-2">
                        <button className="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors relative">
                            <Bell size={18} />
                        </button>
                        <Link to="/admin/settings" className="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                            <User size={18} />
                        </Link>
                    </div>
                </header>

                {/* Page content */}
                <main className="flex-1 overflow-y-auto p-6 animate-fade-in">
                    {children}
                </main>
            </div>
        </div>
    );
}
