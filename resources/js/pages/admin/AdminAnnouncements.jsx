import React, { useEffect, useState } from 'react';
import { announcementApi } from '../../api';
import { toast } from 'react-toastify';
import { Plus, Megaphone, Loader2, Trash2 } from 'lucide-react';

export default function AdminAnnouncements() {
    const [announcements, setAnnouncements] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [form, setForm] = useState({ title: '', body: '', target_role: 'all' });
    const [saving, setSaving] = useState(false);

    const load = () => {
        setLoading(true);
        announcementApi.list()
            .then(res => setAnnouncements(res.data.data || []))
            .catch(() => toast.error('Failed to load announcements'))
            .finally(() => setLoading(false));
    };

    useEffect(() => { load(); }, []);

    const handleSave = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            await announcementApi.create({ ...form, published_at: new Date().toISOString() });
            toast.success('Announcement published');
            setShowForm(false);
            load();
        } catch {
            toast.error('Failed to publish');
        } finally { setSaving(false); }
    };

    const handleDelete = async (id) => {
        if (!confirm('Delete announcement?')) return;
        try {
            await announcementApi.delete(id);
            toast.success('Announcement deleted');
            load();
        } catch { toast.error('Delete failed'); }
    };

    return (
        <div className="space-y-6 animate-fade-in">
            <div className="section-header">
                <div>
                    <h1 className="page-title">Announcements</h1>
                    <p className="page-subtitle">Post announcements for interns and instructors</p>
                </div>
                <button onClick={() => setShowForm(true)} className="btn-primary">
                    <Plus size={16} /> New Announcement
                </button>
            </div>

            {loading ? (
                <div className="flex justify-center py-16"><Loader2 className="animate-spin text-indigo-500" size={28} /></div>
            ) : announcements.length === 0 ? (
                <div className="card text-center py-12">
                    <Megaphone size={36} className="mx-auto mb-2 text-gray-600" />
                    <p className="text-gray-400">No announcements published yet.</p>
                </div>
            ) : (
                <div className="space-y-4">
                    {announcements.map(anc => (
                        <div key={anc.id} className="card relative border-gray-800">
                            <div className="flex items-start justify-between">
                                <div>
                                    <div className="flex items-center gap-2">
                                        <h3 className="text-base font-bold text-white">{anc.title}</h3>
                                        <span className="badge badge-blue">{anc.target_role?.value || anc.target_role}</span>
                                    </div>
                                    <p className="text-xs text-gray-500 mt-0.5">
                                        Published {new Date(anc.published_at || anc.created_at).toLocaleDateString()}
                                    </p>
                                </div>
                                <button onClick={() => handleDelete(anc.id)} className="text-gray-500 hover:text-red-400">
                                    <Trash2 size={16} />
                                </button>
                            </div>
                            <p className="text-sm text-gray-300 mt-3 whitespace-pre-wrap">{anc.body}</p>
                        </div>
                    ))}
                </div>
            )}

            {showForm && (
                <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                    <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-lg shadow-2xl">
                        <h2 className="text-lg font-semibold text-white mb-5">Create Announcement</h2>
                        <form onSubmit={handleSave} className="space-y-4">
                            <div><label className="label">Title *</label><input required className="input" value={form.title} onChange={e => setForm({...form, title: e.target.value})} placeholder="Announcement title" /></div>
                            <div><label className="label">Body / Content *</label><textarea required className="input h-32 resize-none" value={form.body} onChange={e => setForm({...form, body: e.target.value})} placeholder="Write announcement details..." /></div>
                            <div>
                                <label className="label">Target Audience</label>
                                <select className="input" value={form.target_role} onChange={e => setForm({...form, target_role: e.target.value})}>
                                    <option value="all">Everyone</option>
                                    <option value="intern">Interns Only</option>
                                    <option value="instructor">Instructors Only</option>
                                </select>
                            </div>
                            <div className="flex gap-3 justify-end pt-2">
                                <button type="button" onClick={() => setShowForm(false)} className="btn-secondary">Cancel</button>
                                <button type="submit" disabled={saving} className="btn-primary">
                                    {saving ? <Loader2 size={15} className="animate-spin" /> : null} Publish
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
