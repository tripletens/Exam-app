import React, { useEffect, useState } from 'react';
import api from '../../api';
import { toast } from 'react-toastify';
import { Activity, Loader2, Search, CheckCircle, XCircle, Clock, ShieldAlert } from 'lucide-react';
import { Link } from 'react-router-dom';

export default function AdminAttempts() {
    const [attempts, setAttempts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');

    useEffect(() => {
        // Fetch exam attempts from API
        api.get('/reports/intern-performance')
            .then(() => {
                // Fetch attempts via report endpoint or custom query
                return api.get('/reports/exam-performance');
            })
            .catch(() => {})
            .finally(() => setLoading(false));

        // Fetch exam attempts list
        api.get('/exams')
            .then(res => {
                const examList = res.data.data || [];
                // Collect attempts across exams if available
            });
    }, []);

    // Fetch attempts from exam performance or user endpoints
    useEffect(() => {
        setLoading(true);
        api.get('/users?role=intern')
            .then(res => {
                const interns = res.data.data || [];
                const allAttempts = [];
                interns.forEach(user => {
                    if (user.recent_attempts) {
                        user.recent_attempts.forEach(a => {
                            allAttempts.push({ ...a, user_name: user.name, user_email: user.email });
                        });
                    }
                });
                setAttempts(allAttempts);
            })
            .catch(() => toast.error('Failed to load attempt logs'))
            .finally(() => setLoading(false));
    }, []);

    const filtered = attempts.filter(a => {
        const matchesSearch = (a.user_name || '').toLowerCase().includes(search.toLowerCase()) ||
                              (a.exam?.title || '').toLowerCase().includes(search.toLowerCase());
        if (statusFilter === 'passed') return matchesSearch && a.passed;
        if (statusFilter === 'failed') return matchesSearch && a.passed === false;
        return matchesSearch;
    });

    return (
        <div className="space-y-6 animate-fade-in">
            <div className="section-header">
                <div>
                    <h1 className="page-title">Exam Attempts Log</h1>
                    <p className="page-subtitle">Real-time attempt history, IP tracking, and timing security logs</p>
                </div>
            </div>

            {/* Filters */}
            <div className="flex flex-wrap gap-4 items-center justify-between">
                <div className="relative max-w-sm flex-1">
                    <Search size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" />
                    <input
                        type="text"
                        placeholder="Search by intern name or exam..."
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                        className="input pl-9"
                    />
                </div>

                <div className="flex gap-2">
                    <button
                        onClick={() => setStatusFilter('all')}
                        className={`btn-secondary text-xs ${statusFilter === 'all' ? 'bg-indigo-600/20 text-indigo-400 border-indigo-500/30' : ''}`}
                    >
                        All Attempts
                    </button>
                    <button
                        onClick={() => setStatusFilter('passed')}
                        className={`btn-secondary text-xs ${statusFilter === 'passed' ? 'bg-emerald-600/20 text-emerald-400 border-emerald-500/30' : ''}`}
                    >
                        Passed Only
                    </button>
                    <button
                        onClick={() => setStatusFilter('failed')}
                        className={`btn-secondary text-xs ${statusFilter === 'failed' ? 'bg-red-600/20 text-red-400 border-red-500/30' : ''}`}
                    >
                        Failed Only
                    </button>
                </div>
            </div>

            {loading ? (
                <div className="flex justify-center py-16">
                    <Loader2 className="animate-spin text-indigo-500" size={28} />
                </div>
            ) : (
                <div className="table-wrapper">
                    <table className="table">
                        <thead>
                            <tr>
                                <th>Intern</th>
                                <th>Exam Title</th>
                                <th>Score</th>
                                <th>Percentage</th>
                                <th>Status</th>
                                <th>Auto-Submitted</th>
                                <th>Date / Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filtered.length === 0 && (
                                <tr>
                                    <td colSpan={7} className="text-center py-12 text-gray-600">
                                        <Activity size={32} className="mx-auto mb-2 opacity-40" />
                                        No exam attempts logged yet.
                                    </td>
                                </tr>
                            )}
                            {filtered.map((a, idx) => (
                                <tr key={a.id || idx}>
                                    <td>
                                        <div className="font-medium text-white">{a.user_name || 'Intern'}</div>
                                        <div className="text-xs text-gray-500">{a.user_email}</div>
                                    </td>
                                    <td className="text-gray-300 font-medium">{a.exam?.title || 'Exam'}</td>
                                    <td className="text-gray-300">{a.score}/{a.total_marks}</td>
                                    <td>
                                        <span className="font-bold text-white">{a.percentage}%</span>
                                    </td>
                                    <td>
                                        <span className={`badge ${a.passed ? 'badge-green' : 'badge-red'}`}>
                                            {a.passed ? 'PASSED' : 'FAILED'}
                                        </span>
                                    </td>
                                    <td>
                                        {a.auto_submitted ? (
                                            <span className="badge badge-yellow flex items-center gap-1">
                                                <Clock size={12} /> Timer Expired
                                            </span>
                                        ) : (
                                            <span className="badge badge-gray">Manual</span>
                                        )}
                                    </td>
                                    <td className="text-gray-500 text-xs">
                                        {a.submitted_at ? new Date(a.submitted_at).toLocaleString() : '—'}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
}
