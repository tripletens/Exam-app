import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { userApi, courseApi } from '../../api';
import { toast } from 'react-toastify';
import { Plus, Users, Loader2, Edit, Trash2, Search, Eye, UserCheck } from 'lucide-react';

const roleBadge = { super_admin: 'badge-red', instructor: 'badge-blue', intern: 'badge-green' };

export default function AdminInterns() {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');
    const [showForm, setShowForm] = useState(false);
    const [form, setForm] = useState({ name: '', email: '', password: '', role: 'intern', department: '', is_active: true });
    const [saving, setSaving] = useState(false);
    const [totalPages, setTotalPages] = useState(1);
    const [page, setPage] = useState(1);

    const load = (params = {}) => {
        setLoading(true);
        userApi.list({ search, page, ...params })
            .then(res => { setUsers(res.data.data || []); setTotalPages(res.data.meta?.last_page || 1); })
            .catch(() => toast.error('Failed to load users'))
            .finally(() => setLoading(false));
    };

    useEffect(() => { load(); }, [search, page]);

    const handleCreate = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            await userApi.create(form);
            toast.success('User created successfully');
            setShowForm(false);
            load();
        } catch (err) {
            toast.error(err.response?.data?.message || 'Failed to create user');
        } finally { setSaving(false); }
    };

    const handleDelete = async (id) => {
        if (!confirm('Delete this user?')) return;
        try { await userApi.delete(id); toast.success('User deleted'); load(); }
        catch { toast.error('Delete failed'); }
    };

    const toggleActive = async (user) => {
        try {
            await userApi.update(user.id, { is_active: !user.is_active });
            toast.success(`User ${user.is_active ? 'deactivated' : 'activated'}`);
            load();
        } catch { toast.error('Update failed'); }
    };

    return (
        <div className="space-y-6">
            <div className="section-header">
                <div><h1 className="page-title">Users & Interns</h1><p className="page-subtitle">Manage all platform users</p></div>
                <button onClick={() => setShowForm(true)} className="btn-primary"><Plus size={16} /> New User</button>
            </div>

            <div className="relative max-w-sm">
                <Search size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" />
                <input type="text" placeholder="Search by name or email..." value={search} onChange={e => setSearch(e.target.value)} className="input pl-9" />
            </div>

            {loading ? (
                <div className="flex justify-center py-16"><Loader2 className="animate-spin text-indigo-500" size={28} /></div>
            ) : (
                <div className="table-wrapper">
                    <table className="table">
                        <thead><tr><th>User</th><th>Role</th><th>Department</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            {users.length === 0 && (
                                <tr><td colSpan={5} className="text-center py-12 text-gray-600">
                                    <Users size={32} className="mx-auto mb-2 opacity-40" />No users found.
                                </td></tr>
                            )}
                            {users.map(user => (
                                <tr key={user.id}>
                                    <td>
                                        <div className="flex items-center gap-3">
                                            <div className="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center flex-shrink-0">
                                                <span className="text-white text-xs font-bold">{user.name?.[0]?.toUpperCase()}</span>
                                            </div>
                                            <div>
                                                <div className="font-medium text-white">{user.name}</div>
                                                <div className="text-xs text-gray-500">{user.email}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span className={`badge ${roleBadge[user.role?.value || user.role] || 'badge-gray'}`}>{user.role?.value || user.role}</span></td>
                                    <td className="text-gray-400">{user.department || '—'}</td>
                                    <td>
                                        <span className={`badge ${user.is_active ? 'badge-green' : 'badge-red'}`}>
                                            {user.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </td>
                                    <td>
                                        <div className="flex items-center gap-2">
                                            <Link to={`/admin/interns/${user.id}`} className="p-1.5 text-gray-400 hover:text-cyan-400 transition-colors"><Eye size={15} /></Link>
                                            <button onClick={() => toggleActive(user)} className="p-1.5 text-gray-400 hover:text-emerald-400 transition-colors"><UserCheck size={15} /></button>
                                            <button onClick={() => handleDelete(user.id)} className="p-1.5 text-gray-400 hover:text-red-400 transition-colors"><Trash2 size={15} /></button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            {/* Create User Modal */}
            {showForm && (
                <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                    <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-lg shadow-2xl">
                        <h2 className="text-lg font-semibold text-white mb-5">Create New User</h2>
                        <form onSubmit={handleCreate} className="space-y-4">
                            <div><label className="label">Full Name *</label><input required className="input" value={form.name} onChange={e => setForm({...form, name: e.target.value})} placeholder="John Doe" /></div>
                            <div><label className="label">Email *</label><input required type="email" className="input" value={form.email} onChange={e => setForm({...form, email: e.target.value})} placeholder="john@lythub.com" /></div>
                            <div><label className="label">Password *</label><input required type="password" className="input" value={form.password} onChange={e => setForm({...form, password: e.target.value})} placeholder="Min 8 characters" /></div>
                            <div className="grid grid-cols-2 gap-4">
                                <div><label className="label">Role</label>
                                    <select className="input" value={form.role} onChange={e => setForm({...form, role: e.target.value})}>
                                        <option value="intern">Intern</option>
                                        <option value="instructor">Instructor</option>
                                        <option value="super_admin">Super Admin</option>
                                    </select>
                                </div>
                                <div><label className="label">Department</label><input className="input" value={form.department} onChange={e => setForm({...form, department: e.target.value})} placeholder="Cybersecurity" /></div>
                            </div>
                            <div className="flex gap-3 justify-end pt-2">
                                <button type="button" onClick={() => setShowForm(false)} className="btn-secondary">Cancel</button>
                                <button type="submit" disabled={saving} className="btn-primary">
                                    {saving ? <Loader2 size={15} className="animate-spin" /> : <Plus size={15} />}
                                    Create User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
