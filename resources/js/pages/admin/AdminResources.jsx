import React, { useEffect, useState } from 'react';
import { resourceApi, courseApi } from '../../api';
import { toast } from 'react-toastify';
import { Plus, Link2, Loader2, Trash2, ExternalLink } from 'lucide-react';

export default function AdminResources() {
    const [resources, setResources] = useState([]);
    const [loading, setLoading] = useState(true);
    const [courses, setCourses] = useState([]);
    const [showForm, setShowForm] = useState(false);
    const [form, setForm] = useState({ title: '', url: '', type: 'youtube', course_id: '', description: '', is_required: false });
    const [saving, setSaving] = useState(false);

    const load = () => {
        setLoading(true);
        resourceApi.list()
            .then(res => setResources(res.data.data || []))
            .catch(() => toast.error('Failed to load resources'))
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        load();
        courseApi.list().then(res => setCourses(res.data.data || []));
    }, []);

    const handleSave = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            await resourceApi.create(form);
            toast.success('Resource created');
            setShowForm(false);
            load();
        } catch {
            toast.error('Failed to create resource');
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Delete resource?')) return;
        try {
            await resourceApi.delete(id);
            toast.success('Resource deleted');
            load();
        } catch {
            toast.error('Delete failed');
        }
    };

    return (
        <div className="space-y-6 animate-fade-in">
            <div className="section-header">
                <div>
                    <h1 className="page-title">Learning Resources</h1>
                    <p className="page-subtitle">Manage books, YouTube videos, articles, and labs</p>
                </div>
                <button onClick={() => setShowForm(true)} className="btn-primary">
                    <Plus size={16} /> New Resource
                </button>
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
                                <th>Title</th>
                                <th>Type</th>
                                <th>URL / Link</th>
                                <th>Required</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {resources.length === 0 && (
                                <tr>
                                    <td colSpan={5} className="text-center py-12 text-gray-600">
                                        <Link2 size={32} className="mx-auto mb-2 opacity-40" /> No learning resources added.
                                    </td>
                                </tr>
                            )}
                            {resources.map(res => (
                                <tr key={res.id}>
                                    <td className="font-medium text-white">{res.title}</td>
                                    <td><span className="badge badge-blue">{res.type?.value || res.type}</span></td>
                                    <td>
                                        {res.url ? (
                                            <a href={res.url} target="_blank" rel="noreferrer" className="text-indigo-400 hover:underline flex items-center gap-1 text-xs">
                                                Link <ExternalLink size={12} />
                                            </a>
                                        ) : '—'}
                                    </td>
                                    <td><span className={`badge ${res.is_required ? 'badge-green' : 'badge-gray'}`}>{res.is_required ? 'Yes' : 'No'}</span></td>
                                    <td>
                                        <button onClick={() => handleDelete(res.id)} className="p-1.5 text-gray-400 hover:text-red-400">
                                            <Trash2 size={15} />
                                        </button>
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
                        <h2 className="text-lg font-semibold text-white mb-5">Add Resource</h2>
                        <form onSubmit={handleSave} className="space-y-4">
                            <div><label className="label">Title *</label><input required className="input" value={form.title} onChange={e => setForm({...form, title: e.target.value})} placeholder="e.g. MySQL Official Docs" /></div>
                            <div><label className="label">URL</label><input type="url" className="input" value={form.url} onChange={e => setForm({...form, url: e.target.value})} placeholder="https://..." /></div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="label">Resource Type</label>
                                    <select className="input" value={form.type} onChange={e => setForm({...form, type: e.target.value})}>
                                        <option value="youtube">YouTube Video</option>
                                        <option value="book">Book</option>
                                        <option value="article">Article</option>
                                        <option value="documentation">Documentation</option>
                                        <option value="pdf">PDF</option>
                                        <option value="practical_lab">Practical Lab</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="label">Course</label>
                                    <select className="input" value={form.course_id} onChange={e => setForm({...form, course_id: e.target.value})}>
                                        <option value="">None / Global</option>
                                        {courses.map(c => <option key={c.id} value={c.id}>{c.title}</option>)}
                                    </select>
                                </div>
                            </div>
                            <label className="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
                                <input type="checkbox" className="rounded" checked={form.is_required} onChange={e => setForm({...form, is_required: e.target.checked})} />
                                Required Resource
                            </label>
                            <div className="flex gap-3 justify-end pt-2">
                                <button type="button" onClick={() => setShowForm(false)} className="btn-secondary">Cancel</button>
                                <button type="submit" disabled={saving} className="btn-primary">
                                    {saving ? <Loader2 size={15} className="animate-spin" /> : null} Add Resource
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
