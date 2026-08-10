import React, { useState } from 'react';
import useAuthStore from '../../store/authStore';
import { toast } from 'react-toastify';
import { Settings, User, Key, Shield, Check } from 'lucide-react';

export default function AdminSettings() {
    const { user } = useAuthStore();
    const [saved, setSaved] = useState(false);

    const handleSave = (e) => {
        e.preventDefault();
        setSaved(true);
        toast.success('Settings updated');
        setTimeout(() => setSaved(false), 2000);
    };

    return (
        <div className="space-y-6 max-w-3xl animate-fade-in">
            <div>
                <h1 className="page-title">Settings</h1>
                <p className="page-subtitle">Platform and profile configurations</p>
            </div>

            <div className="card">
                <h2 className="text-base font-semibold text-white mb-4 flex items-center gap-2">
                    <User size={18} className="text-indigo-400" /> Account Profile
                </h2>
                <form onSubmit={handleSave} className="space-y-4">
                    <div>
                        <label className="label">Name</label>
                        <input className="input" defaultValue={user?.name} readOnly />
                    </div>
                    <div>
                        <label className="label">Email Address</label>
                        <input className="input" defaultValue={user?.email} readOnly />
                    </div>
                    <div>
                        <label className="label">Role</label>
                        <input className="input" defaultValue={typeof user?.role === 'object' ? user?.role.value : user?.role} readOnly />
                    </div>

                    <div className="pt-2">
                        <button type="submit" className="btn-primary">
                            {saved ? <Check size={16} /> : null} Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <div className="card">
                <h2 className="text-base font-semibold text-white mb-2 flex items-center gap-2">
                    <Shield size={18} className="text-emerald-400" /> Platform Info
                </h2>
                <p className="text-xs text-gray-400 mb-4">Lythub Technologies Internship Learning & Assessment System v1.0.0</p>

                <div className="p-3 bg-gray-800/60 rounded-xl border border-gray-700 text-xs font-mono space-y-1 text-gray-400">
                    <p>Framework: Laravel 12 / PHP 8.3+</p>
                    <p>Frontend: React 18 + Vite</p>
                    <p>Database: MySQL 8 (lythub_platform)</p>
                    <p>Auth: Sanctum SPA Cookie Auth</p>
                </div>
            </div>
        </div>
    );
}
