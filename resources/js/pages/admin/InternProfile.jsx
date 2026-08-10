import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { userApi } from '../../api';
import { toast } from 'react-toastify';
import { Loader2, BookOpen, ClipboardList, Award, CheckCircle, XCircle } from 'lucide-react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

export default function InternProfile() {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        userApi.get(id)
            .then(res => setData(res.data.data))
            .catch(() => toast.error('Failed to load intern profile'))
            .finally(() => setLoading(false));
    }, [id]);

    if (loading) return <div className="flex justify-center py-16"><Loader2 className="animate-spin text-indigo-500" size={28} /></div>;
    if (!data) return <div className="text-gray-500 text-center py-16">Intern not found</div>;

    const { user, progress, exam_stats, recent_attempts } = data;

    const chartData = recent_attempts?.map(a => ({
        exam: a.exam?.title?.substring(0, 15) + '...' || 'Exam',
        score: a.percentage || 0,
    })) || [];

    return (
        <div className="space-y-6 animate-fade-in">
            {/* Header */}
            <div className="card">
                <div className="flex items-start gap-5">
                    <div className="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-500/20">
                        <span className="text-white text-2xl font-bold">{user.name?.[0]?.toUpperCase()}</span>
                    </div>
                    <div className="flex-1">
                        <h1 className="text-xl font-bold text-white">{user.name}</h1>
                        <p className="text-gray-400 text-sm">{user.email}</p>
                        <div className="flex flex-wrap gap-2 mt-3">
                            <span className="badge badge-blue">{user.department || 'No Department'}</span>
                            <span className={`badge ${user.is_active ? 'badge-green' : 'badge-red'}`}>{user.is_active ? 'Active' : 'Inactive'}</span>
                            <span className="badge badge-gray">Intern</span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div className="stat-card">
                    <BookOpen size={20} className="text-indigo-400" />
                    <div className="mt-3">
                        <p className="text-2xl font-bold text-white">{progress?.courses_enrolled || 0}</p>
                        <p className="text-sm text-gray-400">Courses Enrolled</p>
                    </div>
                </div>
                <div className="stat-card">
                    <CheckCircle size={20} className="text-emerald-400" />
                    <div className="mt-3">
                        <p className="text-2xl font-bold text-white">{progress?.courses_completed || 0}</p>
                        <p className="text-sm text-gray-400">Completed</p>
                    </div>
                </div>
                <div className="stat-card">
                    <ClipboardList size={20} className="text-cyan-400" />
                    <div className="mt-3">
                        <p className="text-2xl font-bold text-white">{exam_stats?.total_attempts || 0}</p>
                        <p className="text-sm text-gray-400">Exams Taken</p>
                    </div>
                </div>
                <div className="stat-card">
                    <Award size={20} className="text-amber-400" />
                    <div className="mt-3">
                        <p className="text-2xl font-bold text-white">{exam_stats?.average_score || 0}%</p>
                        <p className="text-sm text-gray-400">Avg Score</p>
                    </div>
                </div>
            </div>

            {/* Chart */}
            {chartData.length > 0 && (
                <div className="card">
                    <h3 className="text-sm font-semibold text-gray-300 mb-4">Recent Exam Scores</h3>
                    <ResponsiveContainer width="100%" height={200}>
                        <BarChart data={chartData}>
                            <CartesianGrid strokeDasharray="3 3" stroke="#1f2937" />
                            <XAxis dataKey="exam" stroke="#6b7280" tick={{ fontSize: 11 }} />
                            <YAxis stroke="#6b7280" tick={{ fontSize: 11 }} domain={[0, 100]} />
                            <Tooltip contentStyle={{ background: '#111827', border: '1px solid #1f2937', color: '#f3f4f6' }} formatter={v => `${v}%`} />
                            <Bar dataKey="score" fill="#6366f1" radius={[4, 4, 0, 0]} />
                        </BarChart>
                    </ResponsiveContainer>
                </div>
            )}

            {/* Recent attempts */}
            <div className="card">
                <h3 className="text-sm font-semibold text-gray-300 mb-4">Recent Exam Attempts</h3>
                {recent_attempts?.length === 0 ? (
                    <p className="text-gray-600 text-sm">No exams taken yet</p>
                ) : (
                    <div className="table-wrapper">
                        <table className="table">
                            <thead><tr><th>Exam</th><th>Score</th><th>Percentage</th><th>Status</th><th>Date</th></tr></thead>
                            <tbody>
                                {recent_attempts?.map(a => (
                                    <tr key={a.id}>
                                        <td className="text-white font-medium">{a.exam?.title}</td>
                                        <td>{a.score}/{a.total_marks}</td>
                                        <td>{a.percentage}%</td>
                                        <td><span className={`badge ${a.passed ? 'badge-green' : 'badge-red'}`}>{a.passed ? 'Passed' : 'Failed'}</span></td>
                                        <td className="text-gray-500 text-xs">{new Date(a.submitted_at).toLocaleDateString()}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </div>
    );
}
