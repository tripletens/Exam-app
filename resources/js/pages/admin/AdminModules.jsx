import React, { useEffect, useState } from 'react';
import { moduleApi, courseApi } from '../../api';
import { toast } from 'react-toastify';
import { Plus, Layers, Loader2, Edit, Trash2, Search } from 'lucide-react';

export default function AdminModules() {
    const [modules, setModules] = useState([]);
    const [courses, setCourses] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedCourse, setSelectedCourse] = useState('');
    const [showForm, setShowForm] = useState(false);
    const [editingModule, setEditingModule] = useState(null);
    const [form, setForm] = useState({ course_id: '', title: '', description: '', order: 1 });
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        courseApi.list()
            .then(res => setCourses(res.data.data || []))
            .catch(() => toast.error('Failed to load courses'));
    }, []);

    const loadModules = () => {
        setLoading(true);
        moduleApi.list({ course_id: selectedCourse })
            .then(res => setModules(res.data.data || []))
            .catch(() => toast.error('Failed to load modules'))
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        loadModules();
    }, [selectedCourse]);

    const openCreate = () => {
        setEditingModule(null);
        setForm({ course_id: selectedCourse || courses[0]?.id || '', title: '', description: '', order: modules.length + 1 });
        setShowForm(true);
    };

    const openEdit = (mod) => {
        setEditingModule(mod);
        setForm({ course_id: mod.course_id, title: mod.title, description: mod.description || '', order: mod.order || 1 });
        setShowForm(true);
    };

    const handleSave = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (editingModule) {
                await moduleApi.update(editingModule.id, form);
                toast.success('Module updated');
            } else {
                await moduleApi.create(form);
                toast.success('Module created');
            }
            setShowForm(false);
            loadModules();
        } catch (err) {
            toast.error(err.response?.data?.message || 'Save failed');
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Delete this module?')) return;
        try {
            await moduleApi.delete(id);
            toast.success('Module deleted');
            loadModules();
        } catch {
            toast.error('Delete failed');
        }
    };

    return (
        <div className="space-y-6 animate-fade-in">
            <div className="section-header">
                <div>
                    <h1 className="page-title">Course Modules</h1>
                    <p className="page-subtitle">Organize learning content into structured modules</p>
                </div>
                <button onClick={openCreate} className="btn-primary">
                    <Plus size={16} /> New Module
                </button>
            </div>

            {/* Filter */}
            <div className="max-w-xs">
                <label className="label">Filter by Course</label>
                <select
                    className="input"
                    value={selectedCourse}
                    onChange={(e) => setSelectedCourse(e.target.value)}
                >
                    <option value="">All Courses</option>
                    {courses.map(c => (
                        <option key={c.id} value={c.id}>{c.title}</option>
                    ))}
                </select>
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
                                <th>Order</th>
                                <th>Module Title</th>
                                <th>Lessons</th>
                                <th>Resources</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {modules.length === 0 && (
                                <tr>
                                    <td colSpan={5} className="text-center py-12 text-gray-600">
                                        <Layers size={32} className="mx-auto mb-2 opacity-40" />
                                        No modules found. Select a course or create a module.
                                    </td>
                                </tr>
                            )}
                            {modules.map(mod => (
                                <tr key={mod.id}>
                                    <td><span className="badge badge-gray">#{mod.order}</span></td>
                                    <td>
                                        <div className="font-medium text-white">{mod.title}</div>
                                        <div className="text-xs text-gray-500">{mod.description}</div>
                                    </td>
                                    <td><span className="badge badge-blue">{mod.lessons?.length || 0} Lessons</span></td>
                                    <td><span className="badge badge-green">{mod.resources?.length || 0} Resources</span></td>
                                    <td>
                                        <div className="flex items-center gap-2">
                                            <button onClick={() => openEdit(mod)} className="p-1.5 text-gray-400 hover:text-indigo-400 transition-colors">
                                                <Edit size={15} />
                                            </button>
                                            <button onClick={() => handleDelete(mod.id)} className="p-1.5 text-gray-400 hover:text-red-400 transition-colors">
                                                <Trash2 size={15} />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            {/* Modal */}
            {showForm && (
                <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                    <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-lg shadow-2xl">
                        <h2 className="text-lg font-semibold text-white mb-5">
                            {editingModule ? 'Edit Module' : 'Create Module'}
                        </h2>
                        <form onSubmit={handleSave} className="space-y-4">
                            <div>
                                <label className="label">Course *</label>
                                <select
                                    required
                                    className="input"
                                    value={form.course_id}
                                    onChange={e => setForm({ ...form, course_id: e.target.value })}
                                >
                                    <option value="">Select Course</option>
                                    {courses.map(c => (
                                        <option key={c.id} value={c.id}>{c.title}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="label">Module Title *</label>
                                <input
                                    required
                                    className="input"
                                    value={form.title}
                                    onChange={e => setForm({ ...form, title: e.target.value })}
                                    placeholder="e.g. Introduction to SQL"
                                />
                            </div>
                            <div>
                                <label className="label">Description</label>
                                <textarea
                                    className="input h-20 resize-none"
                                    value={form.description}
                                    onChange={e => setForm({ ...form, description: e.target.value })}
                                    placeholder="Module overview"
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
                            <div className="flex gap-3 justify-end pt-2">
                                <button type="button" onClick={() => setShowForm(false)} className="btn-secondary">Cancel</button>
                                <button type="submit" disabled={saving} className="btn-primary">
                                    {saving ? <Loader2 size={15} className="animate-spin" /> : null}
                                    {editingModule ? 'Save Changes' : 'Create Module'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
