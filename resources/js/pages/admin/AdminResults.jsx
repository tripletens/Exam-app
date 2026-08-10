import React, { useEffect, useState } from 'react';
import api from '../../api';
import { toast } from 'react-toastify';
import { BarChart2, Loader2, Search, CheckCircle, XCircle, Award, FileText } from 'lucide-react';

export default function AdminResults() {
    const [examStats, setExamStats] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');

    useEffect(() => {
        setLoading(true);
        api.get('/reports/exam-performance')
            .then(res => setExamStats(res.data.data || []))
            .catch(() => toast.error('Failed to load exam results overview'))
            .finally(() => setLoading(false));
    }, []);

    const filtered = examStats.filter(e =>
        (e.exam || '').toLowerCase().includes(search.toLowerCase())
    );

    return (
        <div className="space-y-6 animate-fade-in">
            <div className="section-header">
                <div>
                    <h1 className="page-title">Assessment Results & Analytics</h1>
                    <p className="page-subtitle">Aggregate pass rates, average scores, and question difficulty breakdown</p>
                </div>
            </div>

            {/* Search */}
            <div className="relative max-w-sm">
                <Search size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" />
                <input
                    type="text"
                    placeholder="Filter by exam title..."
                    value={search}
                    onChange={e => setSearch(e.target.value)}
                    className="input pl-9"
                />
            </div>

            {loading ? (
                <div className="flex justify-center py-16">
                    <Loader2 className="animate-spin text-indigo-500" size={28} />
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {filtered.length === 0 && (
                        <div className="card col-span-2 text-center py-12 text-gray-600">
                            <BarChart2 size={32} className="mx-auto mb-2 opacity-40" />
                            No assessment results found.
                        </div>
                    )}
                    {filtered.map((stat, idx) => (
                        <div key={idx} className="card space-y-4 hover:border-indigo-500/30 transition-all">
                            <div className="flex items-start justify-between">
                                <div>
                                    <h3 className="text-base font-bold text-white">{stat.exam}</h3>
                                    <p className="text-xs text-gray-500 mt-0.5">{stat.attempts} Total Attempts Submitted</p>
                                </div>
                                <span className="badge badge-blue">{stat.avg_score}% Avg</span>
                            </div>

                            <div className="grid grid-cols-2 gap-4 p-3 bg-gray-800/50 rounded-xl border border-gray-800 text-xs">
                                <div>
                                    <span className="text-gray-500 block">Average Score</span>
                                    <span className="text-lg font-extrabold text-white">{stat.avg_score}%</span>
                                </div>
                                <div>
                                    <span className="text-gray-500 block">Pass Rate</span>
                                    <span className="text-lg font-extrabold text-emerald-400">{stat.pass_rate}%</span>
                                </div>
                            </div>

                            <div className="w-full bg-gray-800 rounded-full h-2 overflow-hidden">
                                <div
                                    className="bg-emerald-500 h-2 rounded-full transition-all duration-500"
                                    style={{ width: `${stat.pass_rate}%` }}
                                />
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
