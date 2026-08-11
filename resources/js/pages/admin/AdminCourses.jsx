import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { courseApi, userApi } from '../../api';
import { toast } from 'react-toastify';
import { Plus, BookOpen, Loader2, Edit, Trash2, Search, Eye, UserPlus, Check } from 'lucide-react';

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

    // Enrollment Modal State
    const [enrollCourse, setEnrollCourse] = useState(null);
    const [interns, setInterns] = useState([]);
    const [selectedInternIds, setSelectedInternIds] = useState([]);
    const [internSearch, setInternSearch] = useState('');
    const [loadingInterns, setLoadingInterns] = useState(false);
    const [enrolling, setEnrolling] = useState(false);

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

    const openEnroll = async (c) => {
        setEnrollCourse(c);
        setSelectedInternIds([]);
        setInternSearch('');
        setLoadingInterns(true);
        try {
            const res = await userApi.list({ role: 'intern' });
            setInterns(res.data.data || []);
        } catch {
            toast.error('Failed to load interns list');
        } finally {
            setLoadingInterns(false);
        }
    };

    const handleEnrollSave = async (e) => {
        e.preventDefault();
        if (selectedInternIds.length === 0) return toast.error('Select at least one intern');
        setEnrolling(true);
        try {
            await courseApi.enroll(enrollCourse.id, { user_ids: selectedInternIds });
            toast.success(`Successfully enrolled ${selectedInternIds.length} intern(s)`);
            setEnrollCourse(null);
        } catch (err) {
            toast.error(err.response?.data?.message || 'Enrollment failed');
        } finally {
            setEnrolling(false);
        }
    };

    const toggleSelectAllInterns = (filtered) => {
        if (selectedInternIds.length === filtered.length) {
            setSelectedInternIds([]);
        } else {
            setSelectedInternIds(filtered.map(i => i.id));
        }
    };

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

    const filteredInterns = interns.filter(i =>
        i.name?.toLowerCase().includes(internSearch.toLowerCase()) ||
        i.email?.toLowerCase().includes(internSearch.toLowerCase())
    );

    return (
        <div className="space-y-6">
            <div className="section-header">
                <div>
                    <h1 className="page-title">Courses</h1>
                    <p className="page-subtitle">Manage your training courses and intern enrollments</p>
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
                                            <button onClick={() => openEnroll(course)} className="p-1.5 text-gray-400 hover:text-emerald-400 transition-colors" title="Enroll Interns">
                                                <UserPlus size={15} />
                                            </button>
                                            <Link to={`/admin/courses/${course.id}/modules`} className="p-1.5 text-gray-400 hover:text-cyan-400 transition-colors" title="View Modules">
                                                <Eye size={15} />
                                            </Link>
                                            <button onClick={() => openEdit(course)} className="p-1.5 text-gray-400 hover:text-indigo-400 transition-colors" title="Edit Course">
                                                <Edit size={15} />
                                            </button>
                                            <button onClick={() => handleDelete(course.id)} className="p-1.5 text-gray-400 hover:text-red-400 transition-colors" title="Delete Course">
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

            {/* Create/Edit Course Modal */}
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

            {/* ENROLL INTERNS MODAL */}
            {enrollCourse && (
                <div className="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                    <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-lg shadow-2xl space-y-4">
                        <div className="flex items-center justify-between border-b border-gray-800 pb-3">
                            <div>
                                <h2 className="text-base font-bold text-white flex items-center gap-2">
                                    <UserPlus className="text-emerald-400" size={18} /> Enroll Interns in Course
                                </h2>
                                <p className="text-xs text-gray-400 mt-0.5">{enrollCourse.title}</p>
                            </div>
                            <span className="badge badge-indigo text-xs">{selectedInternIds.length} Selected</span>
                        </div>

                        {/* Search & Select All */}
                        <div className="flex items-center gap-2">
                            <div className="relative flex-1">
                                <Search size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" />
                                <input
                                    type="text"
                                    placeholder="Filter interns by name or email..."
                                    value={internSearch}
                                    onChange={e => setInternSearch(e.target.value)}
                                    className="input pl-8 py-1.5 text-xs"
                                />
                            </div>
                            <button
                                type="button"
                                onClick={() => toggleSelectAllInterns(filteredInterns)}
                                className="btn-secondary text-xs py-1.5"
                            >
                                {selectedInternIds.length === filteredInterns.length ? 'Deselect All' : 'Select All'}
                            </button>
                        </div>

                        {/* Interns List */}
                        {loadingInterns ? (
                            <div className="flex justify-center py-8"><Loader2 className="animate-spin text-indigo-500" size={24} /></div>
                        ) : (
                            <div className="max-h-64 overflow-y-auto space-y-1.5 pr-1">
                                {filteredInterns.length === 0 ? (
                                    <div className="text-center py-6 text-gray-500 text-xs">No interns found.</div>
                                ) : (
                                    filteredInterns.map(intern => {
                                        const isSelected = selectedInternIds.includes(intern.id);
                                        return (
                                            <div
                                                key={intern.id}
                                                onClick={() => {
                                                    setSelectedInternIds(prev =>
                                                        isSelected ? prev.filter(id => id !== intern.id) : [...prev, intern.id]
                                                    );
                                                }}
                                                className={`p-3 rounded-xl border flex items-center justify-between cursor-pointer transition-all ${isSelected ? 'bg-indigo-950/40 border-indigo-500 text-white' : 'bg-gray-950/40 border-gray-800/60 text-gray-400 hover:border-gray-700'}`}
                                            >
                                                <div>
                                                    <div className="text-xs font-semibold text-white">{intern.name}</div>
                                                    <div className="text-[11px] text-gray-500">{intern.email}</div>
                                                </div>
                                                <div className={`w-5 h-5 rounded-md border flex items-center justify-center ${isSelected ? 'bg-indigo-600 border-indigo-500 text-white' : 'border-gray-700 bg-gray-900'}`}>
                                                    {isSelected && <Check size={12} />}
                                                </div>
                                            </div>
                                        );
                                    })
                                )}
                            </div>
                        )}

                        <form onSubmit={handleEnrollSave} className="flex gap-3 justify-end pt-2 border-t border-gray-800">
                            <button type="button" onClick={() => setEnrollCourse(null)} className="btn-secondary">Cancel</button>
                            <button type="submit" disabled={enrolling || selectedInternIds.length === 0} className="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 text-white text-xs font-medium rounded-xl transition-all shadow-lg shadow-emerald-600/30 flex items-center gap-1.5">
                                {enrolling ? <Loader2 size={14} className="animate-spin" /> : <UserPlus size={14} />} Enroll ({selectedInternIds.length})
                            </button>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
