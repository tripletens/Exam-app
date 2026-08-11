import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api';
import { toast } from 'react-toastify';
import { BarChart2, Loader2, Award, Clock, ArrowRight } from 'lucide-react';

export default function InternResultsList() {
    const [attempts, setAttempts] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get('/dashboard/intern')
            .then(res => setAttempts(res.data.data?.recent_attempts || []))
            .catch(() => toast.error('Failed to load exam results'))
            .finally(() => setLoading(false));
    }, []);

    return (
        <div className="space-y-6 max-w-4xl mx-auto">
            <div className="section-header">
                <div>
                    <h1 className="page-title">My Exam Results</h1>
                    <p className="page-subtitle">Historical performance and score breakdown across attempts</p>
                </div>
            </div>

            {loading ? (
                <div className="flex justify-center py-16">
                    <Loader2 className="animate-spin text-indigo-500" size={32} />
                </div>
            ) : attempts.length === 0 ? (
                <div className="bg-gray-900 border border-gray-800 rounded-2xl p-12 text-center">
                    <BarChart2 size={40} className="mx-auto mb-3 text-indigo-400 opacity-60" />
                    <h3 className="text-lg font-bold text-white mb-1">No Exam Attempts Completed Yet</h3>
                    <p className="text-gray-400 text-xs max-w-sm mx-auto">
                        Take your assigned module certification exams to view detailed score reports and answer breakdowns.
                    </p>
                </div>
            ) : (
                <div className="table-wrapper">
                    <table className="table">
                        <thead>
                            <tr>
                                <th>Exam Title</th>
                                <th>Score</th>
                                <th>Status</th>
                                <th>Submitted Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {attempts.map(attempt => (
                                <tr key={attempt.id}>
                                    <td>
                                        <div className="font-semibold text-white">{attempt.exam?.title || 'Module Certification Exam'}</div>
                                    </td>
                                    <td>
                                        <span className="font-bold text-indigo-400">{attempt.percentage || 0}%</span>
                                        <span className="text-xs text-gray-500 ml-1.5">({attempt.total_score || 0} pts)</span>
                                    </td>
                                    <td>
                                        <span className={`badge ${attempt.passed ? 'badge-green' : 'badge-red'}`}>
                                            {attempt.passed ? 'PASSED' : 'FAILED'}
                                        </span>
                                    </td>
                                    <td className="text-xs text-gray-400 font-mono">
                                        {attempt.submitted_at ? new Date(attempt.submitted_at).toLocaleDateString() : 'In Progress'}
                                    </td>
                                    <td>
                                        <Link to={`/intern/results/${attempt.id}`} className="btn-secondary text-xs py-1 px-3">
                                            View Report <ArrowRight size={12} />
                                        </Link>
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
