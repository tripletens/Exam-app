import React, { useEffect, useState } from 'react';
import { lessonApi, moduleApi } from '../../api';
import { toast } from 'react-toastify';
import { Plus, FileText, Loader2, Edit, Trash2 } from 'lucide-react';

export default function AdminLessons() {
    const [modules, setModules] = useState([]);
    const [selectedModule, setSelectedModule] = useState('');
    const [loading, setLoading] = useState(false);
    const [showForm, setShowForm] = useState(false);
    const [form, setForm] = useState({ module_id: '', title: '', content: '', duration_minutes: 30, order: 1 });
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        moduleApi.list()
            .then(res => setModules(res.data.data || []))
            .catch(() => toast.error('Failed to load modules'));
    }, []);

    const openCreate = () => {
        setForm({ module_id: selectedModule || modules[0]?.id || '', title: '', content: '', duration_minutes: 30, order: 1 });
        setShowForm(true);
    };

    const handleSave = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            await lessonApi.create(form);
            toast.success('Lesson created');
            setShowForm(false);
        } catch (err) {
            toast.error(err.response?.data?.message || 'Failed to save lesson');
        } finally {
            setSaving(false);
        }
    };

    return (
        <div className="space-y-6 animate-fade-in">
            <div className="section-header">
                <div>
                    <h1 className="page-title">Lessons</h1>
                    <p className="page-subtitle">Manage lesson content inside modules</p>
                </div>
                <button onClick={openCreate} className="btn-primary">
                    <Plus size={16} /> New Lesson
                </button>
            </div>

            <div className="max-w-xs">
                <label className="label">Filter by Module</label>
                <select
                    className="input"
                    value={selectedModule}
                    onChange={e => setSelectedModule(e.target.value)}
                >
                    <option value="">Select Module</option>
                    {modules.map(m => (
                        <option key={m.id} value={m.id}>{m.title}</option>
                    ))}
                </select>
            </div>

            <div className="card text-center py-12">
                <FileText size={36} className="mx-auto mb-3 text-indigo-400 opacity-80" />
                <h3 className="text-lg font-semibold text-gray-200">Lesson Management</h3>
                <p className="text-sm text-gray-400 max-w-md mx-auto mt-1">
                    Select a module to view its lessons, or create a new lesson for interns.
                </p>
            </div>

            {showForm && (
                <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                    <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-lg shadow-2xl">
                        <h2 className="text-lg font-semibold text-white mb-5">Create Lesson</h2>
                        <form onSubmit={handleSave} className="space-y-4">
                            <div>
                                <label className="label">Module *</label>
                                <select required className="input" value={form.module_id} onChange={e => setForm({...form, module_id: e.target.value})}>
                                    <option value="">Select Module</option>
                                    {modules.map(m => (
                                        <option key={m.id} value={m.id}>{m.title}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="label">Lesson Title *</label>
                                <input required className="input" value={form.title} onChange={e => setForm({...form, title: e.target.value})} placeholder="Lesson title" />
                            </div>
                            <div>
                                <label className="label">Content / Body</label>
                                <textarea className="input h-28 resize-none" value={form.content} onChange={e => setForm({...form, content: e.target.value})} placeholder="Markdown or plain text content" />
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="label">Duration (min)</label>
                                    <input type="number" className="input" value={form.duration_minutes} onChange={e => setForm({...form, duration_minutes: +e.target.value})} min={1} />
                                </div>
                                <div>
                                    <label className="label">Order</label>
                                    <input type="number" className="input" value={form.order} onChange={e => setForm({...form, order: +e.target.value})} min={1} />
                                </div>
                            </div>
                            <div className="flex gap-3 justify-end pt-2">
                                <button type="button" onClick={() => setShowForm(false)} className="btn-secondary">Cancel</button>
                                <button type="submit" disabled={saving} className="btn-primary">
                                    {saving ? <Loader2 size={15} className="animate-spin" /> : null} Create Lesson
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
