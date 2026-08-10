import React, { useEffect, useState } from 'react';
import { dashboardApi } from '../../api';
import { toast } from 'react-toastify';
import {
    Users, BookOpen, ClipboardList, TrendingUp, Target,
    XCircle, CheckCircle, Loader2, Medal
} from 'lucide-react';
import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip,
    ResponsiveContainer, LineChart, Line, PieChart, Pie, Cell, Legend
} from 'recharts';

const StatCard = ({ icon: Icon, label, value, color = 'indigo', subtitle }) => {
    const colors = {
        indigo: 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
        emerald: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        amber: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        red: 'bg-red-500/10 text-red-400 border-red-500/20',
        cyan: 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
    };
    return (
        <div className="stat-card">
            <div className="flex items-start justify-between">
                <div className={`p-2.5 rounded-xl border ${colors[color]}`}>
                    <Icon size={20} />
                </div>
            </div>
            <div className="mt-3">
                <p className="text-3xl font-bold text-white">{value ?? '—'}</p>
                <p className="text-sm text-gray-400 mt-0.5">{label}</p>
                {subtitle && <p className="text-xs text-gray-600 mt-1">{subtitle}</p>}
            </div>
        </div>
    );
};

const COLORS = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#06b6d4'];

export default function AdminDashboard() {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        dashboardApi.admin()
            .then(res => setData(res.data.data))
            .catch(() => toast.error('Failed to load dashboard data'))
            .finally(() => setLoading(false));
    }, []);

    if (loading) return (
        <div className="flex items-center justify-center h-64">
            <Loader2 className="animate-spin text-indigo-500" size={32} />
        </div>
    );

    const { stats, score_trend = [], top_interns = [] } = data || {};

    const passFailData = [
        { name: 'Passed', value: stats?.pass_rate ?? 0 },
        { name: 'Failed', value: 100 - (stats?.pass_rate ?? 0) },
    ];

    return (
        <div className="space-y-6 animate-fade-in">
            {/* Header */}
            <div>
                <h1 className="page-title">Dashboard</h1>
                <p className="page-subtitle">Lythub Technologies — Training Platform Overview</p>
            </div>

            {/* Stat cards */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard icon={Users} label="Total Interns" value={stats?.total_interns} color="indigo" />
                <StatCard icon={BookOpen} label="Total Courses" value={stats?.total_courses} color="cyan" />
                <StatCard icon={ClipboardList} label="Exams Completed" value={stats?.exams_completed} color="emerald" />
                <StatCard icon={TrendingUp} label="Average Score" value={`${stats?.average_score ?? 0}%`} color="amber" />
                <StatCard icon={CheckCircle} label="Pass Rate" value={`${stats?.pass_rate ?? 0}%`} color="emerald" />
                <StatCard icon={XCircle} label="Failed Exams" value={stats?.failed_exams} color="red" />
                <StatCard icon={Users} label="Active Interns" value={stats?.active_interns} color="indigo" />
                <StatCard icon={Target} label="Courses" value={stats?.total_courses} color="cyan" />
            </div>

            {/* Charts row */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Score trend */}
                <div className="card lg:col-span-2">
                    <h3 className="text-sm font-semibold text-gray-300 mb-4">Average Score Over Time</h3>
                    {score_trend.length > 0 ? (
                        <ResponsiveContainer width="100%" height={220}>
                            <LineChart data={score_trend}>
                                <CartesianGrid strokeDasharray="3 3" stroke="#1f2937" />
                                <XAxis dataKey="month" stroke="#6b7280" tick={{ fontSize: 12 }} />
                                <YAxis stroke="#6b7280" tick={{ fontSize: 12 }} domain={[0, 100]} />
                                <Tooltip contentStyle={{ background: '#111827', border: '1px solid #1f2937', color: '#f3f4f6' }} />
                                <Line type="monotone" dataKey="avg_score" stroke="#6366f1" strokeWidth={2} dot={{ fill: '#6366f1', r: 4 }} />
                            </LineChart>
                        </ResponsiveContainer>
                    ) : (
                        <div className="flex items-center justify-center h-48 text-gray-600 text-sm">No exam data yet</div>
                    )}
                </div>

                {/* Pass/Fail pie */}
                <div className="card">
                    <h3 className="text-sm font-semibold text-gray-300 mb-4">Pass / Fail Rate</h3>
                    <ResponsiveContainer width="100%" height={220}>
                        <PieChart>
                            <Pie data={passFailData} cx="50%" cy="50%" innerRadius={55} outerRadius={80} paddingAngle={3} dataKey="value">
                                <Cell fill="#10b981" />
                                <Cell fill="#ef4444" />
                            </Pie>
                            <Legend formatter={(v) => <span className="text-gray-400 text-xs">{v}</span>} />
                            <Tooltip contentStyle={{ background: '#111827', border: '1px solid #1f2937', color: '#f3f4f6' }} formatter={(v) => `${v}%`} />
                        </PieChart>
                    </ResponsiveContainer>
                </div>
            </div>

            {/* Top interns */}
            {top_interns.length > 0 && (
                <div className="card">
                    <h3 className="text-sm font-semibold text-gray-300 mb-4 flex items-center gap-2">
                        <Medal size={16} className="text-amber-400" />
                        Top Performing Interns
                    </h3>
                    <div className="table-wrapper">
                        <table className="table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Avg Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                {top_interns.map((intern, i) => (
                                    <tr key={intern.id}>
                                        <td>
                                            <span className={`badge ${i === 0 ? 'badge-yellow' : i === 1 ? 'badge-blue' : 'badge-gray'}`}>
                                                #{i + 1}
                                            </span>
                                        </td>
                                        <td className="font-medium text-white">{intern.name}</td>
                                        <td className="text-gray-500">{intern.email}</td>
                                        <td>
                                            <span className="text-emerald-400 font-semibold">
                                                {intern.avg_score ? `${Math.round(intern.avg_score)}%` : '—'}
                                            </span>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
        </div>
    );
}
