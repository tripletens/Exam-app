import React, { useState } from 'react';
import useAuthStore from '../../store/authStore';
import { authApi } from '../../api';
import { toast } from 'react-toastify';
import { User, Mail, Phone, Building, ShieldCheck, Key, Loader2, Save } from 'lucide-react';

export default function InternProfile() {
    const { user, setUser } = useAuthStore();
    const [profileForm, setProfileForm] = useState({
        name: user?.name || '',
        phone: user?.phone || '',
        department: user?.department || '',
    });
    const [passwordForm, setPasswordForm] = useState({
        current_password: '',
        password: '',
        password_confirmation: '',
    });
    const [savingProfile, setSavingProfile] = useState(false);
    const [savingPassword, setSavingPassword] = useState(false);

    const handleProfileSubmit = async (e) => {
        e.preventDefault();
        setSavingProfile(true);
        try {
            const res = await authApi.updateProfile(profileForm);
            const updatedUser = res.data?.data || res.data;
            setUser({ ...user, ...updatedUser });
            toast.success('Profile details updated successfully');
        } catch (err) {
            toast.error(err.response?.data?.message || 'Failed to update profile');
        } finally {
            setSavingProfile(false);
        }
    };

    const handlePasswordSubmit = async (e) => {
        e.preventDefault();
        if (passwordForm.password !== passwordForm.password_confirmation) {
            return toast.error('New passwords do not match');
        }
        setSavingPassword(true);
        try {
            await authApi.changePassword(passwordForm);
            toast.success('Password changed successfully');
            setPasswordForm({ current_password: '', password: '', password_confirmation: '' });
        } catch (err) {
            toast.error(err.response?.data?.message || 'Failed to change password');
        } finally {
            setSavingPassword(false);
        }
    };

    return (
        <div className="space-y-6 max-w-4xl mx-auto">
            {/* Header Card */}
            <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
                <div className="flex items-center gap-5">
                    <div className="w-16 h-16 bg-gradient-to-tr from-indigo-600 to-indigo-500 rounded-2xl flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-indigo-500/30">
                        {user?.name?.[0]?.toUpperCase()}
                    </div>
                    <div>
                        <h1 className="text-xl font-bold text-white flex items-center gap-2">
                            {user?.name}
                            <span className="badge badge-green text-xs font-normal">
                                <ShieldCheck size={12} className="inline mr-1" /> Active Intern
                            </span>
                        </h1>
                        <p className="text-xs text-gray-400 mt-1 flex items-center gap-4">
                            <span className="flex items-center gap-1"><Mail size={13} /> {user?.email}</span>
                            {user?.department && <span className="flex items-center gap-1"><Building size={13} /> {user.department}</span>}
                        </p>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {/* Profile Details Form */}
                <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-xl space-y-4">
                    <h2 className="text-base font-bold text-white flex items-center gap-2 border-b border-gray-800 pb-3">
                        <User className="text-indigo-400" size={18} /> Personal Details
                    </h2>

                    <form onSubmit={handleProfileSubmit} className="space-y-4">
                        <div>
                            <label className="label">Full Name</label>
                            <input
                                type="text"
                                required
                                className="input"
                                value={profileForm.name}
                                onChange={e => setProfileForm({ ...profileForm, name: e.target.value })}
                            />
                        </div>

                        <div>
                            <label className="label">Email Address (Read-only)</label>
                            <input
                                type="email"
                                disabled
                                className="input bg-gray-950/80 border-gray-800/80 text-gray-500 cursor-not-allowed"
                                value={user?.email || ''}
                            />
                        </div>

                        <div>
                            <label className="label">Phone Number</label>
                            <input
                                type="text"
                                className="input"
                                placeholder="+1 (555) 000-0000"
                                value={profileForm.phone}
                                onChange={e => setProfileForm({ ...profileForm, phone: e.target.value })}
                            />
                        </div>

                        <div>
                            <label className="label">Department / Specialization</label>
                            <input
                                type="text"
                                className="input"
                                placeholder="e.g. Cybersecurity"
                                value={profileForm.department}
                                onChange={e => setProfileForm({ ...profileForm, department: e.target.value })}
                            />
                        </div>

                        <div className="pt-2">
                            <button
                                type="submit"
                                disabled={savingProfile}
                                className="btn-primary w-full justify-center text-xs py-2.5"
                            >
                                {savingProfile ? <Loader2 size={15} className="animate-spin" /> : <Save size={15} />}
                                Save Profile Changes
                            </button>
                        </div>
                    </form>
                </div>

                {/* Password Change Form */}
                <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-xl space-y-4">
                    <h2 className="text-base font-bold text-white flex items-center gap-2 border-b border-gray-800 pb-3">
                        <Key className="text-amber-400" size={18} /> Security & Password
                    </h2>

                    <form onSubmit={handlePasswordSubmit} className="space-y-4">
                        <div>
                            <label className="label">Current Password</label>
                            <input
                                type="password"
                                required
                                className="input"
                                placeholder="••••••••"
                                value={passwordForm.current_password}
                                onChange={e => setPasswordForm({ ...passwordForm, current_password: e.target.value })}
                            />
                        </div>

                        <div>
                            <label className="label">New Password</label>
                            <input
                                type="password"
                                required
                                minLength={8}
                                className="input"
                                placeholder="Min. 8 characters"
                                value={passwordForm.password}
                                onChange={e => setPasswordForm({ ...passwordForm, password: e.target.value })}
                            />
                        </div>

                        <div>
                            <label className="label">Confirm New Password</label>
                            <input
                                type="password"
                                required
                                className="input"
                                placeholder="••••••••"
                                value={passwordForm.password_confirmation}
                                onChange={e => setPasswordForm({ ...passwordForm, password_confirmation: e.target.value })}
                            />
                        </div>

                        <div className="pt-2">
                            <button
                                type="submit"
                                disabled={savingPassword}
                                className="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 text-gray-950 font-bold text-xs rounded-xl shadow-lg shadow-amber-600/20 w-full justify-center flex items-center gap-1.5 transition-all disabled:opacity-40"
                            >
                                {savingPassword ? <Loader2 size={15} className="animate-spin" /> : <Key size={15} />}
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
