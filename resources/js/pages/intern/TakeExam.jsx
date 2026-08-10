import { useEffect, useRef, useState, useCallback } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import {
  AlertTriangle,
  ChevronLeft,
  ChevronRight,
  Clock,
  Flag,
  Send,
  X,
} from 'lucide-react';
import { toast } from 'react-toastify';
import api from '../../api';

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Format raw seconds → "HH:MM:SS"
 */
function formatTime(totalSeconds) {
  const s = Math.max(0, Math.floor(totalSeconds));
  const h = Math.floor(s / 3600);
  const m = Math.floor((s % 3600) / 60);
  const sec = s % 60;
  return [h, m, sec].map((v) => String(v).padStart(2, '0')).join(':');
}

// ---------------------------------------------------------------------------
// Sub-components
// ---------------------------------------------------------------------------

/** Single answer option card */
function OptionCard({ option, isSelected, onSelect, disabled }) {
  return (
    <button
      onClick={() => !disabled && onSelect(option.id)}
      disabled={disabled}
      className={[
        'w-full text-left px-5 py-4 rounded-xl border transition-all duration-150',
        'focus:outline-none focus:ring-2 focus:ring-indigo-500',
        isSelected
          ? 'border-indigo-500 bg-indigo-500/10 text-indigo-200 shadow-lg shadow-indigo-900/30'
          : 'border-gray-700 bg-gray-900 text-gray-200 hover:border-gray-500 hover:bg-gray-800',
        disabled && 'cursor-not-allowed opacity-60',
      ].join(' ')}
    >
      <div className="flex items-start gap-3">
        {/* Lettered badge */}
        <span
          className={[
            'mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold',
            isSelected
              ? 'bg-indigo-500 text-white'
              : 'bg-gray-700 text-gray-300',
          ].join(' ')}
        >
          {option.label ?? String.fromCharCode(65 + (option._index ?? 0))}
        </span>
        <span className="leading-snug">{option.text}</span>
      </div>
    </button>
  );
}

/** Question navigation sidebar button */
function NavButton({ number, state, isCurrent, onClick }) {
  const base =
    'h-9 w-9 rounded-lg text-xs font-semibold transition-all duration-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 border';

  const stateClass = {
    answered: 'bg-indigo-600 border-indigo-500 text-white',
    flagged: 'bg-yellow-500 border-yellow-400 text-gray-950',
    unanswered: 'bg-gray-800 border-gray-700 text-gray-400 hover:border-gray-500 hover:bg-gray-700',
  }[state] ?? 'bg-gray-800 border-gray-700 text-gray-400';

  const currentRing = isCurrent ? 'ring-2 ring-offset-1 ring-offset-gray-950 ring-white' : '';

  return (
    <button onClick={onClick} className={`${base} ${stateClass} ${currentRing}`}>
      {number}
    </button>
  );
}

/** Confirmation / submit modal */
function SubmitModal({ unansweredCount, onConfirm, onCancel, isSubmitting }) {
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
      <div className="w-full max-w-md rounded-2xl border border-gray-700 bg-gray-900 p-8 shadow-2xl">
        <div className="mb-4 flex items-center gap-3">
          <div className="flex h-10 w-10 items-center justify-center rounded-full bg-yellow-500/10">
            <AlertTriangle className="h-5 w-5 text-yellow-400" />
          </div>
          <h2 className="text-lg font-semibold text-white">Submit Exam?</h2>
          <button
            onClick={onCancel}
            disabled={isSubmitting}
            className="ml-auto text-gray-500 hover:text-gray-300"
          >
            <X className="h-5 w-5" />
          </button>
        </div>

        {unansweredCount > 0 ? (
          <p className="mb-6 text-sm text-gray-300">
            You have{' '}
            <span className="font-bold text-yellow-400">{unansweredCount}</span>{' '}
            unanswered{' '}
            {unansweredCount === 1 ? 'question' : 'questions'}. Once submitted,
            you cannot return to this exam.
          </p>
        ) : (
          <p className="mb-6 text-sm text-gray-300">
            All questions answered. Once submitted, you cannot return to this
            exam.
          </p>
        )}

        <div className="flex gap-3">
          <button
            onClick={onCancel}
            disabled={isSubmitting}
            className="flex-1 rounded-xl border border-gray-600 bg-gray-800 py-2.5 text-sm font-medium text-gray-200 transition hover:bg-gray-700 disabled:opacity-50"
          >
            Go Back
          </button>
          <button
            onClick={onConfirm}
            disabled={isSubmitting}
            className="flex-1 flex items-center justify-center gap-2 rounded-xl bg-indigo-600 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-500 disabled:opacity-60"
          >
            {isSubmitting ? (
              <>
                <span className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                Submitting…
              </>
            ) : (
              <>
                <Send className="h-4 w-4" />
                Submit Exam
              </>
            )}
          </button>
        </div>
      </div>
    </div>
  );
}

// ---------------------------------------------------------------------------
// Main Component
// ---------------------------------------------------------------------------

export default function TakeExam() {
  const { attemptId } = useParams();
  const navigate = useNavigate();

  // ── Data state ────────────────────────────────────────────────────────────
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [examTitle, setExamTitle] = useState('');
  const [questions, setQuestions] = useState([]); // [{ id, text, options: [{id, text}] }]
  const [currentIndex, setCurrentIndex] = useState(0);

  // ── Answer / flag state ───────────────────────────────────────────────────
  // Map of question_id → selected_option_id
  const [answers, setAnswers] = useState({});
  // Set of question_ids that are flagged
  const [flagged, setFlagged] = useState(new Set());

  // ── Timer state ───────────────────────────────────────────────────────────
  const [timeLeft, setTimeLeft] = useState(null); // seconds
  const timerRef = useRef(null);
  const syncCountRef = useRef(0); // ticks since last server sync
  const isMountedRef = useRef(true);

  // ── Saving state ──────────────────────────────────────────────────────────
  // Set of question_ids currently being saved (prevents duplicate saves)
  const savingRef = useRef(new Set());

  // ── Submit state ──────────────────────────────────────────────────────────
  const [showModal, setShowModal] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const hasAutoSubmittedRef = useRef(false);

  // ── Derived ───────────────────────────────────────────────────────────────
  const currentQuestion = questions[currentIndex] ?? null;
  const totalQuestions = questions.length;
  const answeredCount = Object.keys(answers).length;
  const unansweredCount = totalQuestions - answeredCount;
  const isLowTime = timeLeft !== null && timeLeft < 300; // < 5 minutes

  // ── Nav helpers ───────────────────────────────────────────────────────────
  const goTo = useCallback((idx) => {
    const clamped = Math.max(0, Math.min(idx, totalQuestions - 1));
    setCurrentIndex(clamped);
  }, [totalQuestions]);

  const getQuestionState = useCallback(
    (qId) => {
      if (flagged.has(qId)) return 'flagged';
      if (answers[qId] !== undefined) return 'answered';
      return 'unanswered';
    },
    [answers, flagged],
  );

  // ── Submit (callable from timer expiry or user action) ───────────────────
  const submitExam = useCallback(
    async (isAutoSubmit = false) => {
      if (isSubmitting || hasAutoSubmittedRef.current) return;
      hasAutoSubmittedRef.current = true;
      setIsSubmitting(true);
      setShowModal(false);

      // Stop the countdown
      if (timerRef.current) {
        clearInterval(timerRef.current);
        timerRef.current = null;
      }

      try {
        await api.post(`/exam-attempts/${attemptId}/submit`);
        if (isAutoSubmit) {
          toast.info('Time is up! Your exam has been submitted automatically.');
        } else {
          toast.success('Exam submitted successfully!');
        }
        navigate(`/intern/results/${attemptId}`);
      } catch (err) {
        hasAutoSubmittedRef.current = false; // allow retry
        setIsSubmitting(false);
        const msg =
          err?.response?.data?.message ?? 'Failed to submit exam. Please try again.';
        toast.error(msg);
      }
    },
    [attemptId, isSubmitting, navigate],
  );

  // ── Server time sync ─────────────────────────────────────────────────────
  const syncServerTime = useCallback(async () => {
    try {
      const { data } = await api.get(`/exam-attempts/${attemptId}/time-remaining`);
      const remaining = Number(data?.time_remaining_seconds ?? data?.time_remaining ?? 0);

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
        const { data } = await api.get(`/exam-attempts/${attemptId}`);

        if (!isMountedRef.current) return;

        const serverSeconds = Number(
          data?.time_remaining_seconds ?? data?.time_remaining ?? 0,
        );

        // If already expired on mount, auto-submit immediately
        if (serverSeconds <= 0) {
          setLoading(false);
          submitExam(true);
          return;
        }

        // Normalise questions — ensure options have _index for labels
        const qs = (data?.questions ?? []).map((q) => ({
          ...q,
          options: (q.options ?? []).map((opt, i) => ({ ...opt, _index: i })),
        }));

        // Hydrate saved answers from server: [{question_id, selected_option_id}]
        const savedMap = {};
        (data?.saved_answers ?? []).forEach((sa) => {
          if (sa.question_id && sa.selected_option_id) {
            savedMap[sa.question_id] = sa.selected_option_id;
          }
        });

        setExamTitle(data?.exam?.title ?? data?.title ?? 'Exam');
        setQuestions(qs);
        setAnswers(savedMap);
        setTimeLeft(serverSeconds);
        setLoading(false);
      } catch (err) {
        if (!isMountedRef.current) return;
        const msg =
          err?.response?.data?.message ?? 'Failed to load exam. Please refresh.';
        setError(msg);
        setLoading(false);
      }
    };

    fetchAttempt();

    return () => {
      isMountedRef.current = false;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [attemptId]);

  // ── Countdown interval ────────────────────────────────────────────────────
  useEffect(() => {
    if (timeLeft === null || loading) return; // wait until data loaded
    if (timeLeft <= 0) return; // already expired

    timerRef.current = setInterval(() => {
      setTimeLeft((prev) => {
        const next = prev - 1;

        // Every 30 ticks → server sync
        syncCountRef.current += 1;
        if (syncCountRef.current >= 30) {
          syncCountRef.current = 0;
          syncServerTime();
        }

        if (next <= 0) {
          clearInterval(timerRef.current);
          timerRef.current = null;
          return 0;
        }
        return next;
      });
    }, 1000);

    return () => {
      if (timerRef.current) clearInterval(timerRef.current);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [loading, timeLeft === null]); // Only re-run when loading ends or timeLeft transitions from null

  // ── Auto-submit when timer hits 0 ─────────────────────────────────────────
  useEffect(() => {
    if (timeLeft === 0 && !hasAutoSubmittedRef.current && !loading) {
      submitExam(true);
    }
  }, [timeLeft, loading, submitExam]);

  // ── Auto-save answer ──────────────────────────────────────────────────────
  const handleSelectOption = useCallback(
    async (questionId, optionId) => {
      // Optimistic update
      setAnswers((prev) => ({ ...prev, [questionId]: optionId }));

      // Debounce duplicate in-flight saves for the same question
      if (savingRef.current.has(questionId)) return;
      savingRef.current.add(questionId);

      try {
        await api.post(`/exam-attempts/${attemptId}/save-answer`, {
          question_id: questionId,
          selected_option_id: optionId,
        });
      } catch (err) {
        const msg =
          err?.response?.data?.message ?? 'Answer could not be saved. Check your connection.';
        toast.error(msg);
      } finally {
        savingRef.current.delete(questionId);
      }
    },
    [attemptId],
  );

  // ── Flag toggle ───────────────────────────────────────────────────────────
  const handleToggleFlag = useCallback(() => {
    if (!currentQuestion) return;
    const qId = currentQuestion.id;
    setFlagged((prev) => {
      const next = new Set(prev);
      if (next.has(qId)) {
        next.delete(qId);
        toast.info('Flag removed.', { autoClose: 1500 });
      } else {
        next.add(qId);
        toast.info('Marked for review.', { autoClose: 1500 });
      }
      return next;
    });
  }, [currentQuestion]);

  // ── Render: loading ───────────────────────────────────────────────────────
  if (loading) {
    return (
      <div className="flex h-screen w-full items-center justify-center bg-gray-950">
        <div className="flex flex-col items-center gap-4">
          <span className="h-10 w-10 animate-spin rounded-full border-4 border-indigo-500 border-t-transparent" />
          <p className="text-sm text-gray-400">Loading your exam…</p>
        </div>
      </div>
    );
  }

  // ── Render: error ─────────────────────────────────────────────────────────
  if (error) {
    return (
      <div className="flex h-screen w-full items-center justify-center bg-gray-950 px-6">
        <div className="max-w-sm rounded-2xl border border-red-700/40 bg-red-950/20 p-8 text-center">
          <AlertTriangle className="mx-auto mb-4 h-10 w-10 text-red-400" />
          <h2 className="mb-2 text-lg font-semibold text-white">Failed to Load Exam</h2>
          <p className="mb-6 text-sm text-gray-400">{error}</p>
          <button
            onClick={() => window.location.reload()}
            className="rounded-xl bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-500"
          >
            Retry
          </button>
        </div>
      </div>
    );
  }

  // ── Render: main ──────────────────────────────────────────────────────────
  const isFlagged = currentQuestion ? flagged.has(currentQuestion.id) : false;
  const selectedOptionId = currentQuestion ? (answers[currentQuestion.id] ?? null) : null;

  return (
    <div className="flex h-screen flex-col overflow-hidden bg-gray-950 text-gray-100">
      {/* ── TOP BAR ─────────────────────────────────────────────────────── */}
      <header className="flex h-14 shrink-0 items-center justify-between border-b border-gray-800 bg-gray-900/80 px-5 backdrop-blur">
        {/* Exam title */}
        <div className="flex w-1/3 items-center gap-2 overflow-hidden">
          <span className="truncate text-sm font-semibold text-gray-100">
            {examTitle}
          </span>
        </div>

        {/* Timer — centred */}
        <div className="flex w-1/3 items-center justify-center">
          <div
            className={[
              'flex items-center gap-2 rounded-xl px-4 py-1.5 font-mono text-base font-bold tabular-nums transition-colors duration-300',
              isLowTime
                ? 'bg-red-500/15 text-red-400 ring-1 ring-red-500/40'
                : 'bg-gray-800 text-gray-100',
            ].join(' ')}
          >
            <Clock className={`h-4 w-4 ${isLowTime ? 'animate-pulse text-red-400' : 'text-gray-400'}`} />
            {timeLeft !== null ? formatTime(timeLeft) : '--:--:--'}
          </div>
        </div>

        {/* Question count */}
        <div className="flex w-1/3 items-center justify-end">
          <span className="text-sm text-gray-400">
            <span className="font-semibold text-gray-200">{answeredCount}</span>
            {' / '}
            <span className="font-semibold text-gray-200">{totalQuestions}</span>
            {' answered'}
          </span>
        </div>
      </header>

      {/* ── BODY ────────────────────────────────────────────────────────── */}
      <div className="flex flex-1 overflow-hidden">
        {/* ── LEFT SIDEBAR — question navigation ──────────────────────── */}
        <aside className="flex w-64 shrink-0 flex-col border-r border-gray-800 bg-gray-900/50 overflow-y-auto">
          <div className="border-b border-gray-800 px-4 py-3">
            <p className="text-xs font-semibold uppercase tracking-widest text-gray-500">
              Questions
            </p>
          </div>

          {/* Legend */}
          <div className="flex flex-wrap gap-x-3 gap-y-1 px-4 py-2 text-xs text-gray-500">
            <span className="flex items-center gap-1">
              <span className="inline-block h-2.5 w-2.5 rounded bg-gray-700" />
              Unanswered
            </span>
            <span className="flex items-center gap-1">
              <span className="inline-block h-2.5 w-2.5 rounded bg-indigo-600" />
              Answered
            </span>
            <span className="flex items-center gap-1">
              <span className="inline-block h-2.5 w-2.5 rounded bg-yellow-500" />
              Flagged
            </span>
          </div>

          {/* Grid */}
          <div className="p-4">
            <div className="grid grid-cols-5 gap-2">
              {questions.map((q, idx) => (
                <NavButton
                  key={q.id}
                  number={idx + 1}
                  state={getQuestionState(q.id)}
                  isCurrent={idx === currentIndex}
                  onClick={() => goTo(idx)}
                />
              ))}
            </div>
          </div>

          {/* Spacer + summary at bottom */}
          <div className="mt-auto border-t border-gray-800 px-4 py-4 text-xs text-gray-500 space-y-1">
            <div className="flex justify-between">
              <span>Answered</span>
              <span className="font-semibold text-indigo-400">{answeredCount}</span>
            </div>
            <div className="flex justify-between">
              <span>Flagged</span>
              <span className="font-semibold text-yellow-400">{flagged.size}</span>
            </div>
            <div className="flex justify-between">
              <span>Unanswered</span>
              <span className="font-semibold text-gray-300">{unansweredCount}</span>
            </div>
          </div>
        </aside>

        {/* ── MAIN QUESTION AREA ──────────────────────────────────────── */}
        <main className="flex flex-1 flex-col overflow-hidden">
          {/* Scrollable question body */}
          <div className="flex-1 overflow-y-auto px-8 py-8">
            {currentQuestion ? (
              <>
                {/* Question header */}
                <div className="mb-6 flex items-start justify-between gap-4">
                  <div className="flex items-center gap-3">
                    <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">
                      {currentIndex + 1}
                    </span>
                    <span className="text-xs font-medium text-gray-500">
                      Question {currentIndex + 1} of {totalQuestions}
                    </span>
                  </div>

                  {isFlagged && (
                    <span className="flex items-center gap-1.5 rounded-full bg-yellow-500/10 px-3 py-1 text-xs font-semibold text-yellow-400 ring-1 ring-yellow-500/30">
                      <Flag className="h-3 w-3" />
                      Marked for review
                    </span>
                  )}
                </div>

                {/* Question text */}
                <div className="mb-8 rounded-xl border border-gray-800 bg-gray-900 px-6 py-5">
                  <p className="text-base leading-relaxed text-gray-100 whitespace-pre-line">
                    {currentQuestion.text ?? currentQuestion.question_text ?? currentQuestion.body}
                  </p>
                </div>

                {/* Options */}
                <div className="space-y-3">
                  {(currentQuestion.options ?? []).map((option) => (
                    <OptionCard
                      key={option.id}
                      option={option}
                      isSelected={selectedOptionId === option.id}
                      onSelect={(optId) => handleSelectOption(currentQuestion.id, optId)}
                      disabled={isSubmitting}
                    />
                  ))}
                </div>
              </>
            ) : (
              <div className="flex h-full items-center justify-center">
                <p className="text-gray-500">No question to display.</p>
              </div>
            )}
          </div>

          {/* ── BOTTOM BAR ──────────────────────────────────────────────── */}
          <footer className="shrink-0 border-t border-gray-800 bg-gray-900/70 px-6 py-3 backdrop-blur">
            <div className="flex items-center justify-between gap-4">
              {/* Flag for review */}
              <button
                onClick={handleToggleFlag}
                disabled={!currentQuestion || isSubmitting}
                className={[
                  'flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-medium transition-all',
                  isFlagged
                    ? 'border-yellow-500/50 bg-yellow-500/10 text-yellow-400 hover:bg-yellow-500/20'
                    : 'border-gray-700 bg-gray-800 text-gray-400 hover:border-gray-500 hover:text-gray-200',
                  (!currentQuestion || isSubmitting) && 'cursor-not-allowed opacity-50',
                ].join(' ')}
              >
                <Flag className="h-4 w-4" />
                {isFlagged ? 'Unmark Review' : 'Flag for Review'}
              </button>

              {/* Navigation + submit */}
              <div className="flex items-center gap-3">
                <button
                  onClick={() => goTo(currentIndex - 1)}
                  disabled={currentIndex === 0 || isSubmitting}
                  className="flex items-center gap-1.5 rounded-xl border border-gray-700 bg-gray-800 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-40"
                >
                  <ChevronLeft className="h-4 w-4" />
                  Previous
                </button>

                <button
                  onClick={() => goTo(currentIndex + 1)}
                  disabled={currentIndex === totalQuestions - 1 || isSubmitting}
                  className="flex items-center gap-1.5 rounded-xl border border-gray-700 bg-gray-800 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-40"
                >
                  Next
                  <ChevronRight className="h-4 w-4" />
                </button>

                <div className="mx-1 h-6 w-px bg-gray-700" />

                <button
                  onClick={() => setShowModal(true)}
                  disabled={isSubmitting}
                  className="flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 disabled:opacity-60"
                >
                  <Send className="h-4 w-4" />
                  Submit Exam
                </button>
              </div>
            </div>
          </footer>
        </main>
      </div>

      {/* ── SUBMIT CONFIRMATION MODAL ────────────────────────────────────── */}
      {showModal && (
        <SubmitModal
          unansweredCount={unansweredCount}
          onConfirm={() => submitExam(false)}
          onCancel={() => setShowModal(false)}
          isSubmitting={isSubmitting}
        />
      )}

      {/* ── AUTO-SUBMIT OVERLAY (when time is up / submitting) ──────────── */}
      {isSubmitting && !showModal && (
        <div className="fixed inset-0 z-40 flex items-center justify-center bg-black/60 backdrop-blur-sm">
          <div className="flex flex-col items-center gap-4 rounded-2xl border border-gray-700 bg-gray-900 px-10 py-8 shadow-2xl">
            <span className="h-10 w-10 animate-spin rounded-full border-4 border-indigo-500 border-t-transparent" />
            <p className="text-sm font-medium text-gray-300">Submitting your exam…</p>
          </div>
        </div>
      )}
    </div>
  );
}
