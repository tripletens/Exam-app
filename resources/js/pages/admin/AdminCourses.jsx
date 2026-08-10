import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { courseApi } from '../../api';
import { toast } from 'react-toastify';
import { Plus, BookOpen, Loader2, Edit, Trash2, Search, Eye } from 'lucide-react';

const difficultyBadge = { beginner: 'badge-green', intermediate: 'badge-yellow', advanced: 'badge-red' };
const statusBadge = { published: 'badge-green', draft: 'badge-gray', archived: 'badge-red' };

export default function AdminCourses() {
    const [courses, setCourses] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');
    const [showForm, setShowForm] = useState(false);
    const [editingCourse, setEditingCourse] = useState(null);
    const [form, setForm] = useState({ title: '', description: '', category: '', difficulty: 'beginner', status: 'draft', estimated_duration: '' });
    const [saving, setSaving] = useState(false);

    const load = (params = {}) => {
        setLoading(true);
        courseApi.list({ search, ...params })
            .then(res => setCourses(res.data.data || []))
            .catch(() => toast.error('Failed to load courses'))
            .finally(() => setLoading(false));
    };

    useEffect(() => { load(); }, []);

    const openCreate = () => { setEditingCourse(null); setForm({ title: '', description: '', category: '', difficulty: 'beginner', status: 'draft', estimated_duration: '' }); setShowForm(true); };
    const openEdit = (c) => { setEditingCourse(c); setForm({ title: c.title, description: c.description || '', category: c.category || '', difficulty: c.difficulty?.value || c.difficulty || 'beginner', status: c.status?.value || c.status || 'draft', estimated_duration: c.estimated_duration || '' }); setShowForm(true); };

    const handleSave = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (editingCourse) {
                await courseApi.update(editingCourse.id, form);
                toast.success('Course updated');
            } else {
                await courseApi.create(form);
                toast.success('Course created');
            }
            setShowForm(false);
            load();
        } catch (err) {
            toast.error(err.response?.data?.message || 'Save failed');
        } finally { setSaving(false); }
    };

    const handleDelete = async (id) => {
        if (!confirm('Delete this course? This cannot be undone.')) return;
        try {
            await courseApi.delete(id);
            toast.success('Course deleted');
            load();
        } catch { toast.error('Delete failed'); }
    };

    return (
        <div className="space-y-6">
            <div className="section-header">
                <div>
                    <h1 className="page-title">Courses</h1>
                    <p className="page-subtitle">Manage your training courses</p>
                </div>
                <button onClick={openCreate} className="btn-primary">
                    <Plus size={16} /> New Course
                </button>
            </div>

            {/* Search */}
            <div className="relative max-w-sm">
                <Search size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" />
                <input
                    type="text"
                    placeholder="Search courses..."
                    value={search}
                    onChange={(e) => { setSearch(e.target.value); load({ search: e.target.value }); }}
                    className="input pl-9"
                />
            </div>

            {/* Table */}
            {loading ? (
                <div className="flex justify-center py-16"><Loader2 className="animate-spin text-indigo-500" size={28} /></div>
            ) : (
                <div className="table-wrapper">
                    <table className="table">
                        <thead><tr><th>Course</th><th>Category</th><th>Difficulty</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            {courses.length === 0 && (
                                <tr><td colSpan={5} className="text-center py-12 text-gray-600">
                                    <BookOpen size={32} className="mx-auto mb-2 opacity-40" />
                                    No courses yet. Create your first course.
                                </td></tr>
                            )}
                            {courses.map(course => (
                                <tr key={course.id}>
                                    <td>
                                        <div className="font-medium text-white">{course.title}</div>
                                        <div className="text-xs text-gray-500 mt-0.5 truncate max-w-xs">{course.description}</div>
                                    </td>
                                    <td><span className="badge badge-blue">{course.category || '—'}</span></td>
                                    <td><span className={`badge ${difficultyBadge[course.difficulty?.value || course.difficulty] || 'badge-gray'}`}>{course.difficulty?.value || course.difficulty}</span></td>
                                    <td><span className={`badge ${statusBadge[course.status?.value || course.status] || 'badge-gray'}`}>{course.status?.value || course.status}</span></td>
                                    <td>
                                        <div className="flex items-center gap-2">
                                            <Link to={`/admin/courses/${course.id}/modules`} className="p-1.5 text-gray-400 hover:text-cyan-400 transition-colors" title="View Modules">
                                                <Eye size={15} />
                                            </Link>
                                            <button onClick={() => openEdit(course)} className="p-1.5 text-gray-400 hover:text-indigo-400 transition-colors">
                                                <Edit size={15} />
                                            </button>
                                            <button onClick={() => handleDelete(course.id)} className="p-1.5 text-gray-400 hover:text-red-400 transition-colors">
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
                        <h2 className="text-lg font-semibold text-white mb-5">{editingCourse ? 'Edit Course' : 'Create Course'}</h2>
                        <form onSubmit={handleSave} className="space-y-4">
                            <div><label className="label">Title *</label><input required className="input" value={form.title} onChange={e => setForm({...form, title: e.target.value})} placeholder="Course title" /></div>
                            <div><label className="label">Description</label><textarea className="input h-20 resize-none" value={form.description} onChange={e => setForm({...form, description: e.target.value})} placeholder="Course description" /></div>
                            <div className="grid grid-cols-2 gap-4">
                                <div><label className="label">Category</label><input className="input" value={form.category} onChange={e => setForm({...form, category: e.target.value})} placeholder="e.g. Cybersecurity" /></div>
                                <div><label className="label">Duration (min)</label><input type="number" className="input" value={form.estimated_duration} onChange={e => setForm({...form, estimated_duration: e.target.value})} /></div>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div><label className="label">Difficulty</label>
                                    <select className="input" value={form.difficulty} onChange={e => setForm({...form, difficulty: e.target.value})}>
                                        <option value="beginner">Beginner</option>
                                        <option value="intermediate">Intermediate</option>
                                        <option value="advanced">Advanced</option>
                                    </select>
                                </div>
                                <div><label className="label">Status</label>
                                    <select className="input" value={form.status} onChange={e => setForm({...form, status: e.target.value})}>
                                        <option value="draft">Draft</option>
                                        <option value="published">Published</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                </div>
                            </div>
                            <div className="flex gap-3 justify-end pt-2">
                                <button type="button" onClick={() => setShowForm(false)} className="btn-secondary">Cancel</button>
                                <button type="submit" disabled={saving} className="btn-primary">
                                    {saving ? <Loader2 size={15} className="animate-spin" /> : null}
                                    {editingCourse ? 'Save Changes' : 'Create Course'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
