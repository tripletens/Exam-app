import React, { useEffect, useState } from 'react';
import api from '../../api';
import { toast } from 'react-toastify';
import { Megaphone, Calendar, Loader2 } from 'lucide-react';

export default function InternAnnouncements() {
    const [announcements, setAnnouncements] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get('/announcements')
            .then(res => setAnnouncements(res.data.data || []))
            .catch(() => toast.error('Failed to load announcements'))
            .finally(() => setLoading(false));
    }, []);

    return (
        <div className="space-y-6 max-w-4xl mx-auto">
            <div className="section-header">
                <div>
                    <h1 className="page-title">Announcements & Notices</h1>
                    <p className="page-subtitle">Latest news and updates from your program directors</p>
                </div>
            </div>

            {loading ? (
                <div className="flex justify-center py-16">
                    <Loader2 className="animate-spin text-indigo-500" size={32} />
                </div>
            ) : announcements.length === 0 ? (
                <div className="bg-gray-900 border border-gray-800 rounded-2xl p-12 text-center">
                    <Megaphone size={40} className="mx-auto mb-3 text-indigo-400 opacity-60" />
                    <h3 className="text-lg font-bold text-white mb-1">No Announcements Yet</h3>
                    <p className="text-gray-400 text-xs max-w-sm mx-auto">
                        Check back later for news, exam schedules, and curriculum updates.
                    </p>
                </div>
            ) : (
                <div className="space-y-4">
                    {announcements.map(item => (
                        <div key={item.id} className="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-xl space-y-2">
                            <div className="flex items-center justify-between gap-4">
                                <h3 className="text-base font-bold text-white">{item.title}</h3>
                                <span className="text-xs text-gray-500 flex items-center gap-1 shrink-0 font-mono">
                                    <Calendar size={12} />
                                    {new Date(item.created_at).toLocaleDateString()}
                                </span>
                            </div>
                            <p className="text-xs text-gray-300 leading-relaxed whitespace-pre-line">{item.content}</p>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
