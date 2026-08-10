import React, { useEffect, useState } from 'react';
import { reportApi } from '../../api';
import { toast } from 'react-toastify';
import { Download, LineChart, Loader2, Users, ClipboardList, BookOpen } from 'lucide-react';

export default function AdminReports() {
    const [performance, setPerformance] = useState([]);
    const [courses, setCourses] = useState([]);
    const [loading, setLoading] = useState(true);
    const [downloading, setDownloading] = useState(false);

    useEffect(() => {
        Promise.all([
            reportApi.internPerformance(),
            reportApi.courseCompletion()
        ]).then(([perfRes, courseRes]) => {
            setPerformance(perfRes.data.data || []);
            setCourses(courseRes.data.data || []);
        }).catch(() => toast.error('Failed to load report data'))
        .finally(() => setLoading(false));
    }, []);

    const handleExport = async (type) => {
        setDownloading(true);
        try {
            const res = await reportApi.export(type);
            const blob = new Blob([res.data], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `lythub-${type}-report.csv`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            toast.success('CSV exported successfully');
        } catch {
            toast.error('Export failed');
        } finally { setDownloading(false); }
    };

    return (
        <div className="space-y-6 animate-fade-in">
            <div className="section-header">
                <div>
                    <h1 className="page-title">Reports & Analytics</h1>
                    <p className="page-subtitle">Export performance summaries and course statistics</p>
                </div>
                <div className="flex gap-2">
                    <button onClick={() => handleExport('interns')} disabled={downloading} className="btn-secondary text-xs">
                        <Download size={14} /> Export Interns CSV
                    </button>
                    <button onClick={() => handleExport('exams')} disabled={downloading} className="btn-secondary text-xs">
                        <Download size={14} /> Export Exams CSV
                    </button>
                </div>
            </div>

            {loading ? (
                <div className="flex justify-center py-16"><Loader2 className="animate-spin text-indigo-500" size={28} /></div>
            ) : (
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Intern performance summary */}
                    <div className="card">
                        <h3 className="text-base font-semibold text-white mb-4 flex items-center gap-2">
                            <Users size={18} className="text-indigo-400" />
                            Intern Ranking & Scores
                        </h3>
                        <div className="table-wrapper">
                            <table className="table">
                                <thead>
                                    <tr><th>Intern</th><th>Exams</th><th>Passed</th><th>Avg Score</th></tr>
                                </thead>
                                <tbody>
                                    {performance.map(row => (
                                        <tr key={row.id}>
                                            <td className="font-medium text-white">{row.name}</td>
                                            <td>{row.total_exams || 0}</td>
                                            <td><span className="badge badge-green">{row.passed_exams || 0}</span></td>
                                            <td className="font-semibold text-emerald-400">{row.avg_score ? `${Math.round(row.avg_score)}%` : '—'}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Course Completion Rates */}
                    <div className="card">
                        <h3 className="text-base font-semibold text-white mb-4 flex items-center gap-2">
                            <BookOpen size={18} className="text-cyan-400" />
                            Course Completion Rates
                        </h3>
                        <div className="table-wrapper">
                            <table className="table">
                                <thead>
                                    <tr><th>Course</th><th>Enrolled</th><th>Completed</th><th>Completion Rate</th></tr>
                                </thead>
                                <tbody>
                                    {courses.map((row, idx) => (
                                        <tr key={idx}>
                                            <td className="font-medium text-white">{row.course}</td>
                                            <td>{row.enrolled}</td>
                                            <td>{row.completed}</td>
                                            <td><span className="badge badge-blue">{row.rate}%</span></td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
