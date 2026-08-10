import React, { useEffect, useState } from 'react';
import { examApi } from '../../api';
import { toast } from 'react-toastify';
import { Plus, ClipboardList, Loader2, Edit, Trash2, Eye, Send, Ban } from 'lucide-react';
import { Link } from 'react-router-dom';

export default function AdminExams() {
    const [exams, setExams] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [form, setForm] = useState({ title: '', description: '', duration_minutes: 120, pass_percentage: 70, max_attempts: 5, randomize_questions: false, randomize_answers: false, show_results_immediately: true, status: 'draft' });
    const [saving, setSaving] = useState(false);

    const load = () => {
        setLoading(true);
        examApi.list().then(res => setExams(res.data.data || [])).catch(() => toast.error('Failed to load exams')).finally(() => setLoading(false));
    };

    useEffect(() => { load(); }, []);

    const handleSave = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            await examApi.create(form);
            toast.success('Exam created');
            setShowForm(false);
            load();
        } catch (err) { toast.error(err.response?.data?.message || 'Failed'); } finally { setSaving(false); }
    };

    const togglePublish = async (exam) => {
        try {
            if (exam.status?.value === 'published' || exam.status === 'published') {
                await examApi.unpublish(exam.id);
                toast.success('Exam unpublished');
            } else {
                await examApi.publish(exam.id);
                toast.success('Exam published');
            }
            load();
        } catch { toast.error('Failed'); }
    };

    const handleDelete = async (id) => {
        if (!confirm('Delete this exam?')) return;
        try { await examApi.delete(id); toast.success('Exam deleted'); load(); } catch { toast.error('Delete failed'); }
    };

    const isPublished = (e) => e.status?.value === 'published' || e.status === 'published';

    return (
        <div className="space-y-6">
            <div className="section-header">
                <div><h1 className="page-title">Exams</h1><p className="page-subtitle">Create and manage assessments</p></div>
                <button onClick={() => setShowForm(true)} className="btn-primary"><Plus size={16} /> New Exam</button>
            </div>

            {loading ? (
                <div className="flex justify-center py-16"><Loader2 className="animate-spin text-indigo-500" size={28} /></div>
            ) : (
                <div className="table-wrapper">
                    <table className="table">
                        <thead><tr><th>Exam</th><th>Duration</th><th>Pass Mark</th><th>Questions</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            {exams.length === 0 && <tr><td colSpan={6} className="text-center py-12 text-gray-600"><ClipboardList size={32} className="mx-auto mb-2 opacity-40" />No exams yet.</td></tr>}
                            {exams.map(exam => (
                                <tr key={exam.id}>
                                    <td>
                                        <div className="font-medium text-white">{exam.title}</div>
                                        <div className="text-xs text-gray-500">{exam.course?.title || '—'}</div>
                                    </td>
                                    <td className="text-gray-300">{exam.duration_minutes} min</td>
                                    <td className="text-gray-300">{exam.pass_percentage}%</td>
                                    <td><span className="badge badge-blue">{exam.questions_count || 0} Qs</span></td>
                                    <td><span className={`badge ${isPublished(exam) ? 'badge-green' : 'badge-gray'}`}>{exam.status?.value || exam.status}</span></td>
                                    <td>
                                        <div className="flex items-center gap-2">
                                            <Link to={`/admin/exams/${exam.id}/questions`} className="p-1.5 text-gray-400 hover:text-cyan-400 transition-colors" title="Questions"><Eye size={15} /></Link>
                                            <button onClick={() => togglePublish(exam)} className={`p-1.5 transition-colors ${isPublished(exam) ? 'text-gray-400 hover:text-amber-400' : 'text-gray-400 hover:text-emerald-400'}`} title={isPublished(exam) ? 'Unpublish' : 'Publish'}>
                                                {isPublished(exam) ? <Ban size={15} /> : <Send size={15} />}
                                            </button>
                                            <button onClick={() => handleDelete(exam.id)} className="p-1.5 text-gray-400 hover:text-red-400 transition-colors"><Trash2 size={15} /></button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            {showForm && (
                <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                    <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-lg shadow-2xl">
                        <h2 className="text-lg font-semibold text-white mb-5">Create Exam</h2>
                        <form onSubmit={handleSave} className="space-y-4">
                            <div><label className="label">Title *</label><input required className="input" value={form.title} onChange={e => setForm({...form, title: e.target.value})} placeholder="Exam title" /></div>
                            <div><label className="label">Description</label><textarea className="input h-16 resize-none" value={form.description} onChange={e => setForm({...form, description: e.target.value})} /></div>
                            <div className="grid grid-cols-3 gap-3">
                                <div><label className="label">Duration (min)</label><input type="number" className="input" value={form.duration_minutes} onChange={e => setForm({...form, duration_minutes: +e.target.value})} min={1} /></div>
                                <div><label className="label">Pass % </label><input type="number" className="input" value={form.pass_percentage} onChange={e => setForm({...form, pass_percentage: +e.target.value})} min={0} max={100} /></div>
                                <div><label className="label">Max Attempts</label><input type="number" className="input" value={form.max_attempts} onChange={e => setForm({...form, max_attempts: +e.target.value})} min={1} /></div>
                            </div>
                            <div className="flex gap-4">
                                <label className="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
                                    <input type="checkbox" className="rounded" checked={form.randomize_questions} onChange={e => setForm({...form, randomize_questions: e.target.checked})} />
                                    Randomize Questions
                                </label>
                                <label className="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
                                    <input type="checkbox" className="rounded" checked={form.show_results_immediately} onChange={e => setForm({...form, show_results_immediately: e.target.checked})} />
                                    Show Results Immediately
                                </label>
                            </div>
                            <div className="flex gap-3 justify-end pt-2">
                                <button type="button" onClick={() => setShowForm(false)} className="btn-secondary">Cancel</button>
                                <button type="submit" disabled={saving} className="btn-primary">{saving ? <Loader2 size={15} className="animate-spin" /> : null} Create Exam</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
