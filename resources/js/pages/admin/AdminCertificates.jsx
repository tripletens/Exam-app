import React, { useEffect, useState } from 'react';
import { certificateApi, userApi, courseApi } from '../../api';
import { toast } from 'react-toastify';
import { Plus, Award, Loader2, Download, Trash2 } from 'lucide-react';

export default function AdminCertificates() {
    const [certificates, setCertificates] = useState([]);
    const [loading, setLoading] = useState(true);
    const [users, setUsers] = useState([]);
    const [courses, setCourses] = useState([]);
    const [showForm, setShowForm] = useState(false);
    const [form, setForm] = useState({ user_id: '', course_id: '' });
    const [saving, setSaving] = useState(false);

    const load = () => {
        setLoading(true);
        certificateApi.list()
            .then(res => setCertificates(res.data.data || []))
            .catch(() => toast.error('Failed to load certificates'))
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        load();
        userApi.list({ role: 'intern' }).then(res => setUsers(res.data.data || []));
        courseApi.list().then(res => setCourses(res.data.data || []));
    }, []);

    const handleIssue = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            await certificateApi.issue(form);
            toast.success('Certificate issued');
            setShowForm(false);
            load();
        } catch {
            toast.error('Failed to issue certificate');
        } finally { setSaving(false); }
    };

    const handleRevoke = async (id) => {
        if (!confirm('Revoke this certificate?')) return;
        try {
            await certificateApi.delete(id);
            toast.success('Certificate revoked');
            load();
        } catch { toast.error('Revoke failed'); }
    };

    return (
        <div className="space-y-6 animate-fade-in">
            <div className="section-header">
                <div>
                    <h1 className="page-title">Certificates</h1>
                    <p className="page-subtitle">Issue and manage official completion certificates</p>
                </div>
                <button onClick={() => setShowForm(true)} className="btn-primary">
                    <Plus size={16} /> Issue Certificate
                </button>
            </div>

            {loading ? (
                <div className="flex justify-center py-16"><Loader2 className="animate-spin text-indigo-500" size={28} /></div>
            ) : (
                <div className="table-wrapper">
                    <table className="table">
                        <thead>
                            <tr>
                                <th>Certificate No</th>
                                <th>Intern</th>
                                <th>Course</th>
                                <th>Issued Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {certificates.length === 0 && (
                                <tr>
                                    <td colSpan={5} className="text-center py-12 text-gray-600">
                                        <Award size={32} className="mx-auto mb-2 opacity-40" /> No certificates issued yet.
                                    </td>
                                </tr>
                            )}
                            {certificates.map(cert => (
                                <tr key={cert.id}>
                                    <td className="font-mono text-xs text-indigo-400">{cert.certificate_number}</td>
                                    <td className="font-medium text-white">{cert.user?.name}</td>
                                    <td className="text-gray-300">{cert.course?.title || 'Training Program'}</td>
                                    <td className="text-gray-500 text-xs">{new Date(cert.issued_at).toLocaleDateString()}</td>
                                    <td>
                                        <button onClick={() => handleRevoke(cert.id)} className="p-1.5 text-gray-400 hover:text-red-400">
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
                        <h2 className="text-lg font-semibold text-white mb-5">Issue Certificate</h2>
                        <form onSubmit={handleIssue} className="space-y-4">
                            <div>
                                <label className="label">Select Intern *</label>
                                <select required className="input" value={form.user_id} onChange={e => setForm({...form, user_id: e.target.value})}>
                                    <option value="">Select Intern</option>
                                    {users.map(u => <option key={u.id} value={u.id}>{u.name} ({u.email})</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="label">Select Course / Program *</label>
                                <select required className="input" value={form.course_id} onChange={e => setForm({...form, course_id: e.target.value})}>
                                    <option value="">Select Course</option>
                                    {courses.map(c => <option key={c.id} value={c.id}>{c.title}</option>)}
                                </select>
                            </div>
                            <div className="flex gap-3 justify-end pt-2">
                                <button type="button" onClick={() => setShowForm(false)} className="btn-secondary">Cancel</button>
                                <button type="submit" disabled={saving} className="btn-primary">
                                    {saving ? <Loader2 size={15} className="animate-spin" /> : null} Issue Certificate
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
