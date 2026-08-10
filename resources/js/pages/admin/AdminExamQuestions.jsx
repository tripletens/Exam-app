import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { examApi, questionApi } from '../../api';
import { toast } from 'react-toastify';
import { ArrowLeft, Plus, Upload, Trash2, CheckCircle2, Copy, HelpCircle, Loader2, FileCode, Check } from 'lucide-react';

export default function AdminExamQuestions({ examIdOverride }) {
    const params = useParams();
    const examId = examIdOverride || params.id;

    const [exam, setExam] = useState(null);
    const [questions, setQuestions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState('list'); // 'list' | 'create' | 'bulk'

    // Form state for single question
    const [singleForm, setSingleForm] = useState({
        question_text: '',
        type: 'mcq',
        marks: 2,
        difficulty: 'medium',
        explanation: '',
        options: [
            { option_text: '', is_correct: true },
            { option_text: '', is_correct: false },
            { option_text: '', is_correct: false },
            { option_text: '', is_correct: false },
        ]
    });
    const [savingSingle, setSavingSingle] = useState(false);

    // Bulk upload state
    const [bulkJson, setBulkJson] = useState('');
    const [uploadingBulk, setUploadingBulk] = useState(false);
    const [copiedSample, setCopiedSample] = useState(false);

    const loadData = async () => {
        if (!examId) return;
        setLoading(true);
        try {
            const [eRes, qRes] = await Promise.all([
                examApi.get(examId),
                questionApi.list({ exam_id: examId })
            ]);
            setExam(eRes.data.data);
            setQuestions(qRes.data.data || []);
        } catch {
            toast.error('Failed to load exam questions');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => { loadData(); }, [examId]);

    const handleCreateSingle = async (e) => {
        e.preventDefault();
        if (!singleForm.question_text.trim()) return toast.error('Question text is required');
        const validOptions = singleForm.options.filter(o => o.option_text.trim());
        if (validOptions.length < 2) return toast.error('At least 2 options are required');
        if (!validOptions.some(o => o.is_correct)) return toast.error('Select a correct option');

        setSavingSingle(true);
        try {
            await questionApi.create({
                exam_id: examId,
                ...singleForm,
                options: validOptions,
            });
            toast.success('Question added to exam pool');
            setSingleForm({
                question_text: '',
                type: 'mcq',
                marks: 2,
                difficulty: 'medium',
                explanation: '',
                options: [
                    { option_text: '', is_correct: true },
                    { option_text: '', is_correct: false },
                    { option_text: '', is_correct: false },
                    { option_text: '', is_correct: false },
                ]
            });
            setActiveTab('list');
            loadData();
        } catch (err) {
            toast.error(err.response?.data?.message || 'Failed to create question');
        } finally {
            setSavingSingle(false);
        }
    };

    const handleBulkUpload = async (e) => {
        e.preventDefault();
        if (!bulkJson.trim()) return toast.error('Paste JSON array of questions');

        let parsedQuestions;
        try {
            parsedQuestions = JSON.parse(bulkJson);
            if (!Array.isArray(parsedQuestions)) {
                throw new Error('Must be a JSON array of question objects');
            }
        } catch (err) {
            return toast.error(`Invalid JSON: ${err.message}`);
        }

        setUploadingBulk(true);
        try {
            const res = await questionApi.bulkUpload(examId, { questions: parsedQuestions });
            toast.success(res.data?.message || 'Questions uploaded successfully');
            setBulkJson('');
            setActiveTab('list');
            loadData();
        } catch (err) {
            toast.error(err.response?.data?.message || 'Bulk upload failed');
        } finally {
            setUploadingBulk(false);
        }
    };

    const handleDelete = async (questionId) => {
        if (!confirm('Delete this question from exam pool?')) return;
        try {
            await questionApi.delete(questionId);
            toast.success('Question deleted');
            loadData();
        } catch {
            toast.error('Failed to delete question');
        }
    };

    const sampleJsonTemplate = `[
  {
    "question_text": "What is the dot product of vectors A = [2, 3] and B = [4, 1]?",
    "type": "mcq",
    "marks": 2,
    "difficulty": "medium",
    "explanation": "(2 * 4) + (3 * 1) = 8 + 3 = 11.",
    "options": [
      { "option_text": "5", "is_correct": false },
      { "option_text": "11", "is_correct": true },
      { "option_text": "14", "is_correct": false },
      { "option_text": "24", "is_correct": false }
    ]
  }
]`;

    const copySampleJson = () => {
        navigator.clipboard.writeText(sampleJsonTemplate);
        setCopiedSample(true);
        toast.info('Sample JSON copied to clipboard!');
        setTimeout(() => setCopiedSample(false), 3000);
    };

    if (loading) {
        return (
            <div className="flex justify-center py-24">
                <Loader2 className="animate-spin text-indigo-500" size={32} />
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div className="flex items-center gap-3">
                    {!examIdOverride && (
                        <Link to="/admin/exams" className="p-2 bg-gray-900 border border-gray-800 rounded-xl text-gray-400 hover:text-white hover:bg-gray-800 transition-colors">
                            <ArrowLeft size={18} />
                        </Link>
                    )}
                    <div>
                        <h1 className="text-xl font-bold text-white">{exam?.title}</h1>
                        <p className="text-sm text-gray-400">Question Pool Manager & Upload ({questions.length} total questions)</p>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <button
                        onClick={() => setActiveTab('list')}
                        className={`px-4 py-2 text-sm font-medium rounded-xl transition-all ${activeTab === 'list' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-gray-900 border border-gray-800 text-gray-400 hover:text-white'}`}
                    >
                        Questions ({questions.length})
                    </button>
                    <button
                        onClick={() => setActiveTab('create')}
                        className={`px-4 py-2 text-sm font-medium rounded-xl transition-all flex items-center gap-1.5 ${activeTab === 'create' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-gray-900 border border-gray-800 text-gray-400 hover:text-white'}`}
                    >
                        <Plus size={16} /> Add Single Question
                    </button>
                    <button
                        onClick={() => setActiveTab('bulk')}
                        className={`px-4 py-2 text-sm font-medium rounded-xl transition-all flex items-center gap-1.5 ${activeTab === 'bulk' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'bg-gray-900 border border-gray-800 text-gray-400 hover:text-white'}`}
                    >
                        <Upload size={16} /> Bulk Upload (JSON)
                    </button>
                </div>
            </div>

            {/* TAB 1: QUESTIONS LIST */}
            {activeTab === 'list' && (
                <div className="space-y-4">
                    {questions.length === 0 ? (
                        <div className="bg-gray-900 border border-gray-800 rounded-2xl p-12 text-center">
                            <HelpCircle className="mx-auto text-gray-600 mb-3" size={40} />
                            <h3 className="text-lg font-semibold text-white mb-1">No Questions in Pool</h3>
                            <p className="text-gray-400 text-sm max-w-md mx-auto mb-6">
                                Upload a batch of questions or add them individually to build the exam's random sampling bank.
                            </p>
                            <div className="flex justify-center gap-3">
                                <button onClick={() => setActiveTab('create')} className="btn-primary">
                                    <Plus size={16} /> Add First Question
                                </button>
                                <button onClick={() => setActiveTab('bulk')} className="btn-secondary">
                                    <Upload size={16} /> Bulk Upload
                                </button>
                            </div>
                        </div>
                    ) : (
                        <div className="grid gap-4">
                            {questions.map((q, idx) => (
                                <div key={q.id} className="bg-gray-900 border border-gray-800 rounded-2xl p-5 hover:border-gray-700 transition-colors">
                                    <div className="flex items-start justify-between gap-4 mb-3">
                                        <div className="flex items-center gap-3">
                                            <span className="w-7 h-7 rounded-lg bg-indigo-950 border border-indigo-800/50 text-indigo-400 text-xs font-bold flex items-center justify-center">
                                                {idx + 1}
                                            </span>
                                            <div>
                                                <div className="text-sm font-semibold text-white">{q.question_text}</div>
                                                <div className="flex items-center gap-2 mt-1">
                                                    <span className="text-xs px-2 py-0.5 rounded bg-gray-800 text-gray-300 font-mono">
                                                        {q.marks} Marks
                                                    </span>
                                                    <span className={`text-xs px-2 py-0.5 rounded capitalize ${q.difficulty === 'hard' ? 'bg-red-950 text-red-400' : q.difficulty === 'medium' ? 'bg-amber-950 text-amber-400' : 'bg-emerald-950 text-emerald-400'}`}>
                                                        {q.difficulty}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <button onClick={() => handleDelete(q.id)} className="p-1.5 text-gray-500 hover:text-red-400 transition-colors">
                                            <Trash2 size={16} />
                                        </button>
                                    </div>

                                    {/* Options Grid */}
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-2 mt-3 pt-3 border-t border-gray-800/60">
                                        {q.options?.map((opt, oIdx) => (
                                            <div
                                                key={opt.id || oIdx}
                                                className={`p-2.5 rounded-xl border text-xs flex items-center gap-2 ${opt.is_correct ? 'bg-emerald-950/40 border-emerald-800/60 text-emerald-300 font-medium' : 'bg-gray-950/40 border-gray-800/50 text-gray-400'}`}
                                            >
                                                {opt.is_correct ? (
                                                    <CheckCircle2 size={14} className="text-emerald-400 flex-shrink-0" />
                                                ) : (
                                                    <span className="w-3.5 h-3.5 rounded-full border border-gray-700 flex-shrink-0" />
                                                )}
                                                <span>{opt.option_text}</span>
                                            </div>
                                        ))}
                                    </div>

                                    {q.explanation && (
                                        <div className="mt-3 text-xs text-gray-400 bg-gray-950/60 p-2.5 rounded-xl border border-gray-800/40">
                                            <span className="font-semibold text-indigo-400">Explanation:</span> {q.explanation}
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            )}

            {/* TAB 2: MANUAL SINGLE QUESTION FORM */}
            {activeTab === 'create' && (
                <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6 max-w-2xl mx-auto shadow-2xl">
                    <h2 className="text-lg font-bold text-white mb-5">Add Single Question</h2>
                    <form onSubmit={handleCreateSingle} className="space-y-4">
                        <div>
                            <label className="label">Question Text *</label>
                            <textarea
                                required
                                rows={3}
                                className="input resize-none"
                                placeholder="Enter technical question text..."
                                value={singleForm.question_text}
                                onChange={e => setSingleForm({ ...singleForm, question_text: e.target.value })}
                            />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="label">Marks per Question</label>
                                <input
                                    type="number"
                                    className="input"
                                    value={singleForm.marks}
                                    onChange={e => setSingleForm({ ...singleForm, marks: +e.target.value })}
                                    min={1}
                                />
                            </div>
                            <div>
                                <label className="label">Difficulty Level</label>
                                <select
                                    className="input"
                                    value={singleForm.difficulty}
                                    onChange={e => setSingleForm({ ...singleForm, difficulty: e.target.value })}
                                >
                                    <option value="easy">Easy</option>
                                    <option value="medium">Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </div>
                        </div>

                        {/* Options Section */}
                        <div className="space-y-3 pt-2">
                            <label className="label">Answer Options (Select Radio for Correct Option) *</label>
                            {singleForm.options.map((opt, i) => (
                                <div key={i} className="flex items-center gap-3 bg-gray-950 p-2.5 rounded-xl border border-gray-800">
                                    <input
                                        type="radio"
                                        name="correct_option"
                                        checked={opt.is_correct}
                                        onChange={() => {
                                            const newOpts = singleForm.options.map((o, idx) => ({
                                                ...o,
                                                is_correct: idx === i,
                                            }));
                                            setSingleForm({ ...singleForm, options: newOpts });
                                        }}
                                        className="text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                                    />
                                    <input
                                        required
                                        type="text"
                                        className="bg-transparent text-sm text-white focus:outline-none flex-1"
                                        placeholder={`Option ${String.fromCharCode(65 + i)}`}
                                        value={opt.option_text}
                                        onChange={e => {
                                            const newOpts = [...singleForm.options];
                                            newOpts[i].option_text = e.target.value;
                                            setSingleForm({ ...singleForm, options: newOpts });
                                        }}
                                    />
                                </div>
                            ))}
                        </div>

                        <div>
                            <label className="label">Explanation (Shown after submission)</label>
                            <input
                                type="text"
                                className="input"
                                placeholder="Explain why the correct answer is right..."
                                value={singleForm.explanation}
                                onChange={e => setSingleForm({ ...singleForm, explanation: e.target.value })}
                            />
                        </div>

                        <div className="flex gap-3 justify-end pt-4">
                            <button type="button" onClick={() => setActiveTab('list')} className="btn-secondary">
                                Cancel
                            </button>
                            <button type="submit" disabled={savingSingle} className="btn-primary">
                                {savingSingle ? <Loader2 size={16} className="animate-spin" /> : <Plus size={16} />} Save Question
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {/* TAB 3: BULK JSON QUESTION UPLOADER */}
            {activeTab === 'bulk' && (
                <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6 max-w-3xl mx-auto shadow-2xl space-y-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <h2 className="text-lg font-bold text-white flex items-center gap-2">
                                <FileCode className="text-emerald-400" size={22} /> Bulk Upload Question Pool
                            </h2>
                            <p className="text-xs text-gray-400 mt-1">
                                Upload 50 to 200 questions simultaneously by pasting a structured JSON array.
                            </p>
                        </div>
                        <button
                            onClick={copySampleJson}
                            className="btn-secondary text-xs flex items-center gap-1.5"
                        >
                            {copiedSample ? <Check size={14} className="text-emerald-400" /> : <Copy size={14} />} Copy Sample JSON
                        </button>
                    </div>

                    <form onSubmit={handleBulkUpload} className="space-y-4">
                        <div>
                            <label className="label">Paste JSON Array of Questions *</label>
                            <textarea
                                required
                                rows={12}
                                className="input font-mono text-xs leading-relaxed resize-y h-64"
                                placeholder={`[\n  {\n    "question_text": "Sample Question...",\n    "options": [\n      { "option_text": "Option A", "is_correct": true },\n      { "option_text": "Option B", "is_correct": false }\n    ]\n  }\n]`}
                                value={bulkJson}
                                onChange={e => setBulkJson(e.target.value)}
                            />
                        </div>

                        <div className="flex gap-3 justify-end pt-2">
                            <button type="button" onClick={() => setActiveTab('list')} className="btn-secondary">
                                Cancel
                            </button>
                            <button type="submit" disabled={uploadingBulk} className="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium rounded-xl transition-all shadow-lg shadow-emerald-600/30 flex items-center gap-2 text-sm">
                                {uploadingBulk ? <Loader2 size={16} className="animate-spin" /> : <Upload size={16} />} Upload Questions
                            </button>
                        </div>
                    </form>
                </div>
            )}
        </div>
    );
}
