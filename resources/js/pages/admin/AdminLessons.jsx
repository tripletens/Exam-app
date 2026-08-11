import React, { useEffect, useState } from 'react';
import { lessonApi, moduleApi } from '../../api';
import { toast } from 'react-toastify';
import { Plus, FileText, Loader2, Edit, Trash2, Clock, Layers } from 'lucide-react';

export default function AdminLessons() {
    const [modules, setModules] = useState([]);
    const [selectedModule, setSelectedModule] = useState('');
    const [lessons, setLessons] = useState([]);
    const [loadingModules, setLoadingModules] = useState(true);
    const [loadingLessons, setLoadingLessons] = useState(false);

    const [showForm, setShowForm] = useState(false);
    const [editingLesson, setEditingLesson] = useState(null);
    const [form, setForm] = useState({ module_id: '', title: '', content: '', duration_minutes: 30, order: 1 });
    const [saving, setSaving] = useState(false);

    // Load all modules
    useEffect(() => {
        moduleApi.list()
            .then(res => {
                const list = res.data.data || [];
                setModules(list);
                if (list.length > 0) {
                    setSelectedModule(list[0].id.toString());
                }
            })
            .catch(() => toast.error('Failed to load modules'))
            .finally(() => setLoadingModules(false));
    }, []);

    // Load lessons whenever selectedModule changes
    useEffect(() => {
        if (!selectedModule) {
            setLessons([]);
            return;
        }
        setLoadingLessons(true);
        lessonApi.list({ module_id: selectedModule })
            .then(res => setLessons(res.data.data || []))
            .catch(() => toast.error('Failed to load lessons'))
            .finally(() => setLoadingLessons(false));
    }, [selectedModule]);

    const openCreate = () => {
        setEditingLesson(null);
        setForm({
            module_id: selectedModule || (modules[0]?.id ? modules[0].id.toString() : ''),
            title: '',
            content: '',
            duration_minutes: 30,
            order: lessons.length + 1
        });
        setShowForm(true);
    };

    const openEdit = (l) => {
        setEditingLesson(l);
        setForm({
            module_id: l.module_id.toString(),
            title: l.title,
            content: l.content || '',
            duration_minutes: l.duration_minutes || 30,
            order: l.order || 1
        });
        setShowForm(true);
    };

    const handleSave = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (editingLesson) {
                await lessonApi.update(editingLesson.id, form);
                toast.success('Lesson updated');
            } else {
                await lessonApi.create(form);
                toast.success('Lesson created');
            }
            setShowForm(false);
            // Refresh lessons
            if (selectedModule === form.module_id) {
                const res = await lessonApi.list({ module_id: selectedModule });
                setLessons(res.data.data || []);
            } else {
                setSelectedModule(form.module_id);
            }
        } catch (err) {
            toast.error(err.response?.data?.message || 'Failed to save lesson');
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Delete this lesson? This action cannot be undone.')) return;
        try {
            await lessonApi.delete(id);
            toast.success('Lesson deleted');
            setLessons(prev => prev.filter(l => l.id !== id));
        } catch {
            toast.error('Failed to delete lesson');
        }
    };

    if (loadingModules) {
        return (
            <div className="flex justify-center py-24">
                <Loader2 className="animate-spin text-indigo-500" size={32} />
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="section-header">
                <div>
                    <h1 className="page-title">Lessons Bank</h1>
                    <p className="page-subtitle">Create, view, and organize lesson topics inside modules</p>
                </div>
                <button onClick={openCreate} className="btn-primary">
                    <Plus size={16} /> New Lesson
                </button>
            </div>

            {/* Filter by Module */}
            <div className="bg-gray-900 border border-gray-800 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div className="flex items-center gap-3">
                    <Layers className="text-indigo-400" size={20} />
                    <div>
                        <div className="text-xs font-semibold text-gray-300">Select Module</div>
                        <div className="text-[11px] text-gray-500">View lessons belonging to a specific module</div>
                    </div>
                </div>
                <select
                    className="input max-w-xs text-sm"
                    value={selectedModule}
                    onChange={e => setSelectedModule(e.target.value)}
                >
                    {modules.length === 0 && <option value="">No modules found</option>}
                    {modules.map(m => (
                        <option key={m.id} value={m.id}>
                            {m.course?.title ? `${m.course.title} → ` : ''}{m.title}
                        </option>
                    ))}
                </select>
            </div>

            {/* Lessons Table */}
            {loadingLessons ? (
                <div className="flex justify-center py-16">
                    <Loader2 className="animate-spin text-indigo-500" size={28} />
                </div>
            ) : (
                <div className="table-wrapper">
                    <table className="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Lesson Title</th>
                                <th>Est. Duration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {lessons.length === 0 ? (
                                <tr>
                                    <td colSpan={4} className="text-center py-12 text-gray-500">
                                        <FileText size={32} className="mx-auto mb-2 opacity-40 text-indigo-400" />
                                        No lessons found for this module yet.
                                    </td>
                                </tr>
                            ) : (
                                lessons.map(lesson => (
                                    <tr key={lesson.id}>
                                        <td className="w-12 font-mono text-xs text-gray-500">{lesson.order}</td>
                                        <td>
                                            <div className="font-semibold text-white">{lesson.title}</div>
                                            <div className="text-xs text-gray-500 truncate max-w-md mt-0.5">
                                                {lesson.content ? lesson.content.substring(0, 90) + '...' : 'No markdown content body.'}
                                            </div>
                                        </td>
                                        <td>
                                            <span className="badge badge-indigo flex items-center gap-1 w-fit">
                                                <Clock size={12} /> {lesson.duration_minutes || 30} mins
                                            </span>
                                        </td>
                                        <td>
                                            <div className="flex items-center gap-2">
                                                <button onClick={() => openEdit(lesson)} className="p-1.5 text-gray-400 hover:text-indigo-400 transition-colors" title="Edit Lesson">
                                                    <Edit size={15} />
                                                </button>
                                                <button onClick={() => handleDelete(lesson.id)} className="p-1.5 text-gray-400 hover:text-red-400 transition-colors" title="Delete Lesson">
                                                    <Trash2 size={15} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            )}

            {/* Create/Edit Modal */}
            {showForm && (
                <div className="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                    <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-lg shadow-2xl space-y-4">
                        <h2 className="text-lg font-bold text-white border-b border-gray-800 pb-3">
                            {editingLesson ? 'Edit Lesson' : 'Create New Lesson'}
                        </h2>
                        <form onSubmit={handleSave} className="space-y-4">
                            <div>
                                <label className="label">Module *</label>
                                <select
                                    required
                                    className="input text-xs"
                                    value={form.module_id}
                                    onChange={e => setForm({ ...form, module_id: e.target.value })}
                                >
                                    <option value="">Select Module</option>
                                    {modules.map(m => (
                                        <option key={m.id} value={m.id}>{m.title}</option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="label">Lesson Title *</label>
                                <input
                                    required
                                    className="input"
                                    value={form.title}
                                    onChange={e => setForm({ ...form, title: e.target.value })}
                                    placeholder="Lesson title"
                                />
                            </div>

                            <div>
                                <label className="label">Content / Study Body (Markdown supported)</label>
                                <textarea
                                    className="input h-32 font-mono text-xs leading-relaxed resize-none"
                                    value={form.content}
                                    onChange={e => setForm({ ...form, content: e.target.value })}
                                    placeholder="# Topic Title&#10;&#10;Write lesson study notes here..."
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="label">Duration (min)</label>
                                    <input
                                        type="number"
                                        className="input"
                                        value={form.duration_minutes}
                                        onChange={e => setForm({ ...form, duration_minutes: +e.target.value })}
                                        min={1}
                                    />
                                </div>
                                <div>
                                    <label className="label">Display Order</label>
                                    <input
                                        type="number"
                                        className="input"
                                        value={form.order}
                                        onChange={e => setForm({ ...form, order: +e.target.value })}
                                        min={1}
                                    />
                                </div>
                            </div>

                            <div className="flex gap-3 justify-end pt-3 border-t border-gray-800">
                                <button type="button" onClick={() => setShowForm(false)} className="btn-secondary">Cancel</button>
                                <button type="submit" disabled={saving} className="btn-primary">
                                    {saving ? <Loader2 size={15} className="animate-spin" /> : null}
                                    {editingLesson ? 'Save Changes' : 'Create Lesson'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
