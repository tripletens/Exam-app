import React, { useEffect, useState } from 'react';
import { examApi } from '../../api';
import AdminExamQuestions from './AdminExamQuestions';
import { Loader2, ClipboardList } from 'lucide-react';
import { toast } from 'react-toastify';

export default function AdminQuestions() {
    const [exams, setExams] = useState([]);
    const [selectedExamId, setSelectedExamId] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        examApi.list()
            .then(res => {
                const list = res.data.data || [];
                setExams(list);
                if (list.length > 0) {
                    setSelectedExamId(list[0].id.toString());
                }
            })
            .catch(() => toast.error('Failed to load exams'))
            .finally(() => setLoading(false));
    }, []);

    if (loading) {
        return (
            <div className="flex justify-center py-24">
                <Loader2 className="animate-spin text-indigo-500" size={32} />
            </div>
        );
    }

    if (exams.length === 0) {
        return (
            <div className="bg-gray-900 border border-gray-800 rounded-2xl p-12 text-center">
                <ClipboardList className="mx-auto text-gray-600 mb-3" size={40} />
                <h3 className="text-lg font-semibold text-white mb-1">No Exams Found</h3>
                <p className="text-gray-400 text-sm max-w-md mx-auto">
                    Create an exam first before managing questions.
                </p>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="bg-gray-900 border border-gray-800 rounded-2xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xl">
                <div>
                    <h1 className="text-xl font-bold text-white">Questions Bank Manager</h1>
                    <p className="text-xs text-gray-400">Select an exam from the dropdown below to view, add, or bulk-upload questions</p>
                </div>
                <div className="flex items-center gap-3">
                    <label className="text-xs font-semibold text-gray-300 uppercase tracking-wider flex-shrink-0">Select Exam:</label>
                    <select
                        className="input max-w-xs text-sm"
                        value={selectedExamId}
                        onChange={(e) => setSelectedExamId(e.target.value)}
                    >
                        {exams.map(e => (
                            <option key={e.id} value={e.id}>
                                {e.title} ({e.questions_count || 0} Qs)
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            {selectedExamId && (
                <AdminExamQuestions key={selectedExamId} examIdOverride={selectedExamId} />
            )}
        </div>
    );
}
