import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { attemptApi } from '../../api';
import { toast } from 'react-toastify';
import { Loader2, CheckCircle2, XCircle, Clock, Award, ArrowLeft, HelpCircle } from 'lucide-react';

export default function ExamResults() {
    const { attemptId } = useParams();
    const [result, setResult] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        attemptApi.get(attemptId)
            .then(res => setResult(res.data.data))
            .catch(err => {
                toast.error(err.response?.data?.message || 'Failed to load exam results');
            })
            .finally(() => setLoading(false));
    }, [attemptId]);

    if (loading) {
        return (
            <div className="flex items-center justify-center h-64">
                <Loader2 className="animate-spin text-indigo-500" size={32} />
            </div>
        );
    }

    if (!result) {
        return (
            <div className="text-center py-16">
                <p className="text-gray-400">Results not found or not yet available.</p>
                <Link to="/intern/exams" className="btn-primary mt-4">
                    <ArrowLeft size={16} /> Back to Exams
                </Link>
            </div>
        );
    }

    const { exam_title, score, total_marks, percentage, passed, time_taken_minutes, submitted_at, answers } = result;

    return (
        <div className="max-w-4xl mx-auto space-y-6 animate-fade-in">
            <div className="flex items-center justify-between">
                <Link to="/intern/exams" className="btn-secondary text-xs">
                    <ArrowLeft size={14} /> Back to Exams
                </Link>
                <span className="text-xs text-gray-500">
                    Submitted: {submitted_at ? new Date(submitted_at).toLocaleString() : 'Just now'}
                </span>
            </div>

            {/* Banner card */}
            <div className={`card text-center py-8 relative overflow-hidden border ${passed ? 'border-emerald-500/30 bg-emerald-950/20' : 'border-red-500/30 bg-red-950/20'}`}>
                <div className="inline-flex items-center justify-center w-16 h-16 rounded-full mb-3 shadow-xl">
                    {passed ? (
                        <div className="w-16 h-16 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center">
                            <CheckCircle2 size={40} />
                        </div>
                    ) : (
                        <div className="w-16 h-16 bg-red-500/20 text-red-400 rounded-full flex items-center justify-center">
                            <XCircle size={40} />
                        </div>
                    )}
                </div>

                <h1 className="text-2xl font-bold text-white mb-1">{exam_title}</h1>
                <p className="text-sm text-gray-400 mb-6">Exam Performance Summary</p>

                <div className="inline-flex items-center gap-3 px-6 py-2 rounded-full bg-gray-900/80 border border-gray-800 mb-6">
                    <span className="text-3xl font-extrabold text-white">{percentage}%</span>
                    <span className={`badge text-xs px-3 py-1 ${passed ? 'badge-green' : 'badge-red'}`}>
                        {passed ? 'PASSED' : 'FAILED'}
                    </span>
                </div>

                <div className="grid grid-cols-3 max-w-md mx-auto gap-4 pt-4 border-t border-gray-800/80">
                    <div>
                        <p className="text-xs text-gray-500">Score</p>
                        <p className="text-lg font-bold text-gray-200">{score} / {total_marks}</p>
                    </div>
                    <div>
                        <p className="text-xs text-gray-500">Percentage</p>
                        <p className="text-lg font-bold text-gray-200">{percentage}%</p>
                    </div>
                    <div>
                        <p className="text-xs text-gray-500">Time Taken</p>
                        <p className="text-lg font-bold text-gray-200 flex items-center justify-center gap-1">
                            <Clock size={14} className="text-gray-400" />
                            {time_taken_minutes}m
                        </p>
                    </div>
                </div>
            </div>

            {/* Answer breakdown */}
            {answers && answers.length > 0 && (
                <div className="card space-y-4">
                    <h2 className="text-base font-semibold text-white flex items-center gap-2">
                        <HelpCircle size={18} className="text-indigo-400" />
                        Detailed Question Review
                    </h2>

                    <div className="space-y-4">
                        {answers.map((ans, idx) => (
                            <div key={idx} className={`p-4 rounded-xl border ${ans.is_correct ? 'bg-emerald-950/10 border-emerald-500/20' : 'bg-red-950/10 border-red-500/20'}`}>
                                <div className="flex items-start justify-between gap-3 mb-2">
                                    <h3 className="text-sm font-medium text-gray-200">
                                        <span className="text-gray-500 mr-2">Q{idx + 1}.</span>
                                        {ans.question}
                                    </h3>
                                    <span className={`badge flex-shrink-0 ${ans.is_correct ? 'badge-green' : 'badge-red'}`}>
                                        {ans.is_correct ? `+${ans.marks_awarded} pts` : `0 / ${ans.marks_available} pts`}
                                    </span>
                                </div>

                                {ans.selected_option && (
                                    <p className="text-xs text-gray-400 mt-1">
                                        <span className="text-gray-500 font-medium">Your answer: </span>
                                        <span className={ans.is_correct ? 'text-emerald-400 font-medium' : 'text-red-400 font-medium'}>
                                            {ans.selected_option}
                                        </span>
                                    </p>
                                )}

                                {!ans.is_correct && ans.correct_options && ans.correct_options.length > 0 && (
                                    <p className="text-xs text-gray-400 mt-1">
                                        <span className="text-gray-500 font-medium">Correct answer: </span>
                                        <span className="text-emerald-400 font-medium">
                                            {ans.correct_options.join(', ')}
                                        </span>
                                    </p>
                                )}

                                {ans.explanation && (
                                    <div className="mt-2 text-xs bg-gray-900/60 p-2.5 rounded-lg border border-gray-800 text-gray-300">
                                        <span className="font-semibold text-indigo-400">Explanation: </span>
                                        {ans.explanation}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
