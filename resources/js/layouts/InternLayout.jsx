import React, { useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import useAuthStore from '../store/authStore';
import { authApi } from '../api';
import {
    LayoutDashboard, BookOpen, ClipboardList, BarChart2,
    Award, Megaphone, User, LogOut, Menu, X, Bell
} from 'lucide-react';

const navItems = [
    { label: 'Dashboard', href: '/intern', icon: LayoutDashboard },
    { label: 'My Courses', href: '/intern/courses', icon: BookOpen },
    { label: 'My Exams', href: '/intern/exams', icon: ClipboardList },
    { label: 'My Results', href: '/intern/results', icon: BarChart2 },
    { label: 'Certificates', href: '/intern/certificates', icon: Award },
    { label: 'Announcements', href: '/intern/announcements', icon: Megaphone },
    { label: 'Profile', href: '/intern/profile', icon: User },
];

export default function InternLayout({ children }) {
    const [sidebarOpen, setSidebarOpen] = useState(true);
    const { user, clearAuth } = useAuthStore();
    const location = useLocation();
    const navigate = useNavigate();

    const handleLogout = async () => {
        try { await authApi.logout(); } catch (_) {}
        clearAuth();
        navigate('/login');
        toast.success('Logged out successfully');
    };

    const isActive = (href) => {
        if (href === '/intern') return location.pathname === '/intern';
        return location.pathname.startsWith(href);
    };

    return (
        <div className="flex h-screen bg-gray-950 overflow-hidden">
            <aside className={`${sidebarOpen ? 'w-64' : 'w-0 overflow-hidden'} flex-shrink-0 flex flex-col bg-gray-900 border-r border-gray-800 transition-all duration-300`}>
                <div className="h-16 flex items-center px-5 border-b border-gray-800 flex-shrink-0">
                    <div className="flex items-center gap-3">
                        <div className="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center shadow-lg shadow-emerald-500/30">
                            <span className="text-white font-bold text-sm">L</span>
                        </div>
                        <div>
                            <div className="text-white font-bold text-sm">Lythub</div>
                            <div className="text-gray-500 text-xs">Intern Portal</div>
                        </div>
                    </div>
                </div>

                <nav className="flex-1 overflow-y-auto py-4 px-3 space-y-0.5 scrollbar-thin">
                    {navItems.map((item) => (
                        <Link
                            key={item.href}
                            to={item.href}
                            className={`sidebar-link ${isActive(item.href) ? 'active' : ''}`}
                        >
                            <item.icon size={16} />
                            {item.label}
                        </Link>
                    ))}
                </nav>

                <div className="border-t border-gray-800 p-3">
                    <div className="flex items-center gap-3 px-2 py-2">
                        <div className="w-8 h-8 bg-emerald-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <span className="text-white text-xs font-bold">{user?.name?.[0]?.toUpperCase()}</span>
                        </div>
                        <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-gray-200 truncate">{user?.name}</p>
                            <p className="text-xs text-gray-500">Intern</p>
                        </div>
                        <button onClick={handleLogout} className="p-1.5 text-gray-500 hover:text-red-400 transition-colors" title="Logout">
                            <LogOut size={15} />
                        </button>
                    </div>
                </div>
            </aside>

            <div className="flex-1 flex flex-col overflow-hidden">
                <header className="h-16 bg-gray-900 border-b border-gray-800 flex items-center justify-between px-6 flex-shrink-0">
                    <button onClick={() => setSidebarOpen(!sidebarOpen)} className="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                        {sidebarOpen ? <X size={18} /> : <Menu size={18} />}
                    </button>
                    <button className="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                        <Bell size={18} />
                    </button>
                </header>
                <main className="flex-1 overflow-y-auto p-6 animate-fade-in">
                    {children}
                </main>
            </div>
        </div>
    );
}
