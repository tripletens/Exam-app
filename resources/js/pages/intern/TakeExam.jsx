import React, {
  useState,
  useEffect,
  useRef,
  useCallback,
  useMemo,
} from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import {
  Clock,
  ChevronLeft,
  ChevronRight,
  Flag,
  AlertTriangle,
  Send,
  CheckCircle2,
  Shield,
} from 'lucide-react';
import api from '../../api';

export default function TakeExam() {
  const { attemptId } = useParams();
  const navigate = useNavigate();

  // ── State ────────────────────────────────────────────────────────────────
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [examTitle, setExamTitle] = useState('Exam');
  const [questions, setQuestions] = useState([]);
  const [currentIndex, setCurrentIndex] = useState(0);

  // Map of questionId -> selectedOptionId
  const [answers, setAnswers] = useState({});
  // Set of flagged questionIds
  const [flagged, setFlagged] = useState(new Set());

  // Seconds remaining (initially loaded from server)
  const [timeLeft, setTimeLeft] = useState(null);

  // Auto-save feedback per question: 'saving' | 'saved' | 'error'
  const [saveState, setSaveState] = useState({});

  // Confirm submit modal
  const [showSubmitModal, setShowSubmitModal] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Active question shortcut
  const currentQuestion = questions[currentIndex] || null;

  // Track component mount status
  const isMountedRef = useRef(true);

  // ── Submit exam to backend ────────────────────────────────────────────────
  const submitExam = useCallback(
    async (isAuto = false) => {
      if (isSubmitting) return;
      setIsSubmitting(true);

      try {
        await api.post(`/exam-attempts/${attemptId}/submit`);
        if (isAuto) {
          toast.info('Time is up! Your exam has been submitted automatically.');
        } else {
          toast.success('Exam submitted successfully!');
        }
        navigate(`/intern/results/${attemptId}`, { replace: true });
      } catch (err) {
        toast.error(
          err?.response?.data?.message ||
            'Failed to submit exam. Please try again.',
        );
        setIsSubmitting(false);
      }
    },
    [attemptId, isSubmitting, navigate],
  );

  // ── Server time sync ─────────────────────────────────────────────────────
  const syncServerTime = useCallback(async () => {
    try {
      const res = await api.get(`/exam-attempts/${attemptId}/time-remaining`);
      const attemptData = res.data?.data || res.data;
      const remaining = Number(
        attemptData?.seconds_remaining ?? attemptData?.time_remaining_seconds ?? attemptData?.time_remaining ?? 0,
      );

      if (!isMountedRef.current) return;

      if (remaining <= 0) {
        setTimeLeft(0);
        submitExam(true);
        return;
      }

      setTimeLeft(remaining);
    } catch {
      // Non-fatal — local countdown continues
    }
  }, [attemptId, submitExam]);

  // ── Fetch initial attempt data ────────────────────────────────────────────
  useEffect(() => {
    isMountedRef.current = true;

    const fetchAttempt = async () => {
      try {
        const res = await api.get(`/exam-attempts/${attemptId}`);
        if (!isMountedRef.current) return;

        const attemptData = res.data?.data || res.data;

        // If attempt is already submitted, redirect to results immediately
        if (attemptData?.is_submitted) {
          navigate(`/intern/results/${attemptId}`, { replace: true });
          return;
        }

        const serverSeconds = Number(
          attemptData?.time_remaining_seconds ?? attemptData?.time_remaining ?? 7200,
        );

        // Normalise questions — ensure options have _index for labels
        const rawQs = attemptData?.questions || [];
        const qs = rawQs.map((q) => ({
          ...q,
          options: (q.options || []).map((opt, i) => ({ ...opt, _index: i })),
        }));

        // Hydrate saved answers from server
        let savedMap = {};
        if (Array.isArray(attemptData?.saved_answers)) {
          attemptData.saved_answers.forEach((sa) => {
            if (sa.question_id && sa.selected_option_id) {
              savedMap[sa.question_id] = sa.selected_option_id;
            }
          });
        } else if (attemptData?.saved_answers && typeof attemptData.saved_answers === 'object') {
          savedMap = attemptData.saved_answers;
        }

        setExamTitle(attemptData?.exam_title || attemptData?.exam?.title || 'Exam');
        setQuestions(qs);
        setAnswers(savedMap);
        setTimeLeft(serverSeconds);
        setLoading(false);

        // Only auto-submit on mount if questions were loaded AND time is strictly 0
        if (serverSeconds <= 0 && qs.length > 0) {
          submitExam(true);
        }
      } catch (err) {
        if (!isMountedRef.current) return;
        const msg =
          err?.response?.data?.message || 'Failed to load exam. Please refresh.';
        setError(msg);
        setLoading(false);
      }
    };

    fetchAttempt();

    return () => {
      isMountedRef.current = false;
    };
  }, [attemptId, navigate, submitExam]);

  // ── Local countdown timer (1s interval) ──────────────────────────────────
  useEffect(() => {
    if (timeLeft === null || timeLeft <= 0 || loading) return;

    const timer = setInterval(() => {
      setTimeLeft((prev) => {
        if (prev <= 1) {
          clearInterval(timer);
          submitExam(true);
          return 0;
        }
        return prev - 1;
      });
    }, 1000);

    return () => clearInterval(timer);
  }, [timeLeft, loading, submitExam]);

  // ── Periodic server time sync (every 30s) ────────────────────────────────
  useEffect(() => {
    if (loading || timeLeft === null) return;
    const syncTimer = setInterval(syncServerTime, 30000);
    return () => clearInterval(syncTimer);
  }, [loading, timeLeft, syncServerTime]);

  // ── Save answer on selection ──────────────────────────────────────────────
  const handleSelectOption = async (questionId, optionId) => {
    setAnswers((prev) => ({ ...prev, [questionId]: optionId }));
    setSaveState((prev) => ({ ...prev, [questionId]: 'saving' }));

    try {
      await api.post(`/exam-attempts/${attemptId}/save-answer`, {
        question_id: questionId,
        selected_option_id: optionId,
      });
      if (isMountedRef.current) {
        setSaveState((prev) => ({ ...prev, [questionId]: 'saved' }));
      }
    } catch {
      if (isMountedRef.current) {
        setSaveState((prev) => ({ ...prev, [questionId]: 'error' }));
      }
    }
  };

  // ── Flag question ────────────────────────────────────────────────────────
  const toggleFlag = (questionId) => {
    setFlagged((prev) => {
      const next = new Set(prev);
      if (next.has(questionId)) {
        next.delete(questionId);
      } else {
        next.add(questionId);
      }
      return next;
    });
  };

  // ── Format seconds -> HH:MM:SS or MM:SS ──────────────────────────────────
  const formatTime = (totalSec) => {
    if (totalSec === null || totalSec === undefined) return '--:--';
    const hrs = Math.floor(totalSec / 3600);
    const mins = Math.floor((totalSec % 3600) / 60);
    const secs = totalSec % 60;

    const pad = (n) => String(n).padStart(2, '0');
    if (hrs > 0) {
      return `${pad(hrs)}:${pad(mins)}:${pad(secs)}`;
    }
    return `${pad(mins)}:${pad(secs)}`;
  };

  const isTimeLow = timeLeft !== null && timeLeft < 300; // < 5 mins

  // ── Question counts ──────────────────────────────────────────────────────
  const answeredCount = useMemo(
    () => Object.keys(answers).length,
    [answers],
  );
  const unansweredCount = questions.length - answeredCount;

  // ── Loading state ────────────────────────────────────────────────────────
  if (loading) {
    return (
      <div className="min-h-screen bg-gray-950 text-white flex items-center justify-center">
        <div className="flex flex-col items-center gap-4">
          <div className="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin" />
          <p className="text-gray-400 text-sm animate-pulse">
            Securing exam environment & loading questions…
          </p>
        </div>
      </div>
    );
  }

  // ── Error state ──────────────────────────────────────────────────────────
  if (error) {
    return (
      <div className="min-h-screen bg-gray-950 text-white flex items-center justify-center p-4">
        <div className="bg-gray-900 border border-red-500/30 rounded-2xl p-8 max-w-md w-full text-center space-y-4 shadow-2xl">
          <div className="w-14 h-14 bg-red-500/10 text-red-400 rounded-full flex items-center justify-center mx-auto">
            <AlertTriangle size={28} />
          </div>
          <h2 className="text-xl font-bold">Exam Load Error</h2>
          <p className="text-sm text-gray-400">{error}</p>
          <button
            onClick={() => navigate('/intern/exams')}
            className="w-full py-2.5 px-4 bg-gray-800 hover:bg-gray-700 text-white font-medium rounded-xl transition text-sm"
          >
            Return to My Exams
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-950 text-gray-100 flex flex-col font-sans select-none">
      {/* Top Header */}
      <header className="h-16 bg-gray-900 border-b border-gray-800 px-4 lg:px-8 flex items-center justify-between shrink-0 sticky top-0 z-30 shadow-md">
        <div className="flex items-center gap-3">
          <div className="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center font-bold text-white text-sm shadow-md">
            L
          </div>
          <div>
            <h1 className="text-sm lg:text-base font-bold text-white line-clamp-1">
              {examTitle}
            </h1>
            <p className="text-[11px] text-gray-400 hidden sm:block">
              Lythub Technologies Assessment Environment
            </p>
          </div>
        </div>

        {/* Center: Server-synced Timer */}
        <div
          className={`flex items-center gap-2 px-4 py-1.5 rounded-full font-mono text-sm font-bold border transition-all duration-300 ${
            isTimeLow
              ? 'bg-red-500/20 text-red-400 border-red-500/40 animate-pulse'
              : 'bg-gray-800 text-indigo-300 border-indigo-500/30'
          }`}
        >
          <Clock size={16} className={isTimeLow ? 'text-red-400' : 'text-indigo-400'} />
          <span>{formatTime(timeLeft)}</span>
        </div>

        {/* Right: Submit Button */}
        <button
          onClick={() => setShowSubmitModal(true)}
          disabled={isSubmitting}
          className="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-lg shadow-indigo-600/20 transition disabled:opacity-50"
        >
          <Send size={14} />
          <span>Submit Exam</span>
        </button>
      </header>

      {/* Main Content Body */}
      <div className="flex-1 flex overflow-hidden">
        {/* Left Sidebar: Question Navigation Grid */}
        <aside className="w-64 bg-gray-900 border-r border-gray-800 hidden md:flex flex-col shrink-0">
          <div className="p-4 border-b border-gray-800">
            <h3 className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
              Question Navigator
            </h3>
            <div className="grid grid-cols-2 gap-2 text-[11px] text-gray-400">
              <div className="flex items-center gap-1.5">
                <span className="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block" />
                <span>Answered ({answeredCount})</span>
              </div>
              <div className="flex items-center gap-1.5">
                <span className="w-2.5 h-2.5 rounded-full bg-gray-700 inline-block" />
                <span>Unanswered ({unansweredCount})</span>
              </div>
              <div className="flex items-center gap-1.5 col-span-2">
                <span className="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block" />
                <span>Flagged ({flagged.size})</span>
              </div>
            </div>
          </div>

          <div className="flex-1 p-4 overflow-y-auto">
            <div className="grid grid-cols-4 gap-2">
              {questions.map((q, idx) => {
                const isCurrent = idx === currentIndex;
                const isAnswered = Boolean(answers[q.id]);
                const isFlagged = flagged.has(q.id);

                let btnStyle = 'bg-gray-800 text-gray-400 border-gray-700 hover:bg-gray-700';

                if (isAnswered) {
                  btnStyle = 'bg-emerald-950/80 text-emerald-400 border-emerald-500/40 font-semibold';
                }

                if (isFlagged) {
                  btnStyle = 'bg-amber-950/80 text-amber-400 border-amber-500/40 font-semibold';
                }

                if (isCurrent) {
                  btnStyle += ' ring-2 ring-indigo-400 ring-offset-2 ring-offset-gray-950';
                }

                return (
                  <button
                    key={q.id}
                    onClick={() => setCurrentIndex(idx)}
                    className={`h-10 rounded-xl text-xs font-mono font-medium border flex items-center justify-center relative transition-all ${btnStyle}`}
                  >
                    {idx + 1}
                    {isFlagged && (
                      <span className="absolute top-1 right-1 w-1.5 h-1.5 rounded-full bg-amber-400" />
                    )}
                  </button>
                );
              })}
            </div>
          </div>
        </aside>

        {/* Main Workspace: Active Question */}
        <main className="flex-1 flex flex-col overflow-y-auto p-4 lg:p-8 max-w-4xl mx-auto w-full">
          {currentQuestion ? (
            <div className="flex-1 flex flex-col justify-between space-y-6">
              <div className="space-y-6">
                {/* Question Info Bar */}
                <div className="flex items-center justify-between">
                  <span className="text-xs font-semibold text-indigo-400 bg-indigo-500/10 px-3 py-1 rounded-full border border-indigo-500/20">
                    Question {currentIndex + 1} of {questions.length}
                  </span>
                  <div className="flex items-center gap-3">
                    {saveState[currentQuestion.id] === 'saving' && (
                      <span className="text-[11px] text-gray-500 animate-pulse">
                        Saving answer…
                      </span>
                    )}
                    {saveState[currentQuestion.id] === 'saved' && (
                      <span className="text-[11px] text-emerald-400 flex items-center gap-1">
                        <CheckCircle2 size={12} /> Saved
                      </span>
                    )}
                    <span className="text-xs text-gray-500">
                      {currentQuestion.marks} {currentQuestion.marks === 1 ? 'mark' : 'marks'}
                    </span>
                  </div>
                </div>

                {/* Question Text */}
                <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-xl">
                  <h2 className="text-base lg:text-lg font-medium text-white leading-relaxed">
                    {currentQuestion.question_text}
                  </h2>
                </div>

                {/* Answer Options */}
                <div className="space-y-3">
                  {currentQuestion.options.map((opt) => {
                    const isSelected = answers[currentQuestion.id] === opt.id;
                    const letter = String.fromCharCode(65 + opt._index);

                    return (
                      <button
                        key={opt.id}
                        onClick={() => handleSelectOption(currentQuestion.id, opt.id)}
                        className={`w-full p-4 rounded-xl border text-left transition-all flex items-center gap-4 group ${
                          isSelected
                            ? 'bg-indigo-600/20 border-indigo-500 text-white shadow-md shadow-indigo-600/10'
                            : 'bg-gray-900/60 border-gray-800 text-gray-300 hover:bg-gray-900 hover:border-gray-700'
                        }`}
                      >
                        <div
                          className={`w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold font-mono shrink-0 transition-colors ${
                            isSelected
                              ? 'bg-indigo-600 text-white'
                              : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white'
                          }`}
                        >
                          {letter}
                        </div>
                        <span className="text-sm font-medium leading-relaxed">
                          {opt.option_text}
                        </span>
                      </button>
                    );
                  })}
                </div>
              </div>

              {/* Bottom Controls */}
              <div className="flex items-center justify-between pt-6 border-t border-gray-800 mt-8">
                <button
                  onClick={() => toggleFlag(currentQuestion.id)}
                  className={`flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-medium border transition ${
                    flagged.has(currentQuestion.id)
                      ? 'bg-amber-500/20 text-amber-300 border-amber-500/40'
                      : 'bg-gray-900 text-gray-400 border-gray-800 hover:text-white hover:border-gray-700'
                  }`}
                >
                  <Flag size={14} />
                  <span>
                    {flagged.has(currentQuestion.id) ? 'Flagged for Review' : 'Flag for Review'}
                  </span>
                </button>

                <div className="flex items-center gap-3">
                  <button
                    onClick={() => setCurrentIndex((prev) => Math.max(0, prev - 1))}
                    disabled={currentIndex === 0}
                    className="flex items-center gap-1.5 px-4 py-2 bg-gray-900 hover:bg-gray-800 border border-gray-800 text-gray-300 text-xs font-medium rounded-xl transition disabled:opacity-40"
                  >
                    <ChevronLeft size={16} /> Previous
                  </button>
                  <button
                    onClick={() =>
                      setCurrentIndex((prev) => Math.min(questions.length - 1, prev + 1))
                    }
                    disabled={currentIndex === questions.length - 1}
                    className="flex items-center gap-1.5 px-4 py-2 bg-gray-900 hover:bg-gray-800 border border-gray-800 text-gray-300 text-xs font-medium rounded-xl transition disabled:opacity-40"
                  >
                    Next <ChevronRight size={16} />
                  </button>
                </div>
              </div>
            </div>
          ) : null}
        </main>
      </div>

      {/* Confirmation Modal */}
      {showSubmitModal && (
        <div className="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 animate-fade-in">
          <div className="bg-gray-900 border border-gray-800 rounded-2xl max-w-md w-full p-6 space-y-5 shadow-2xl">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center border border-indigo-500/20">
                <Shield size={22} />
              </div>
              <div>
                <h3 className="text-base font-bold text-white">Submit Exam Confirmation</h3>
                <p className="text-xs text-gray-400">Are you sure you want to finish?</p>
              </div>
            </div>

            <div className="bg-gray-950 p-4 rounded-xl border border-gray-800 space-y-2 text-xs">
              <div className="flex justify-between">
                <span className="text-gray-400">Total Questions:</span>
                <span className="font-semibold text-white">{questions.length}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-400">Answered:</span>
                <span className="font-semibold text-emerald-400">{answeredCount}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-400">Unanswered:</span>
                <span className="font-semibold text-amber-400">{unansweredCount}</span>
              </div>
              {flagged.size > 0 && (
                <div className="flex justify-between">
                  <span className="text-gray-400">Flagged Questions:</span>
                  <span className="font-semibold text-amber-400">{flagged.size}</span>
                </div>
              )}
            </div>

            {unansweredCount > 0 && (
              <p className="text-xs text-amber-400 flex items-center gap-1.5">
                <AlertTriangle size={14} className="shrink-0" />
                You have {unansweredCount} unanswered question(s). Unanswered questions will score 0 marks.
              </p>
            )}

            <div className="flex items-center justify-end gap-3 pt-2">
              <button
                onClick={() => setShowSubmitModal(false)}
                disabled={isSubmitting}
                className="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs font-medium rounded-xl transition"
              >
                Continue Exam
              </button>
              <button
                onClick={() => {
                  setShowSubmitModal(false);
                  submitExam(false);
                }}
                disabled={isSubmitting}
                className="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-indigo-600/20 transition flex items-center gap-1.5"
              >
                {isSubmitting ? 'Submitting…' : 'Yes, Submit Exam'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
