import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import {
  FileText,
  Clock,
  Target,
  CheckSquare,
  Repeat,
  AlertTriangle,
  ArrowLeft,
  Play,
  ShieldAlert,
  Info,
  BookOpen,
  Eye,
  EyeOff,
} from 'lucide-react';
import { toast } from 'react-toastify';
import api from '../../api';

// ─── Skeleton ─────────────────────────────────────────────────────────────────
const Skeleton = ({ className = '' }) => (
  <div className={`animate-pulse bg-gray-800 rounded-lg ${className}`} />
);

// ─── Detail Row ───────────────────────────────────────────────────────────────
const DetailRow = ({ icon: Icon, label, value, accent = 'indigo' }) => {
  const accentColors = {
    indigo: 'text-indigo-400 bg-indigo-500/10 border-indigo-500/20',
    violet: 'text-violet-400 bg-violet-500/10 border-violet-500/20',
    emerald: 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20',
    amber: 'text-amber-400 bg-amber-500/10 border-amber-500/20',
    sky: 'text-sky-400 bg-sky-500/10 border-sky-500/20',
  };
  const cls = accentColors[accent] || accentColors.indigo;

  return (
    <div className="flex items-center gap-4 p-4 bg-gray-800/40 rounded-xl border border-gray-800">
      <div className={`p-2.5 rounded-xl border ${cls}`}>
        <Icon size={18} className={cls.split(' ')[0]} />
      </div>
      <div className="flex-1 min-w-0">
        <p className="text-xs text-gray-500 font-medium">{label}</p>
        <p className="text-base font-bold text-white mt-0.5">{value}</p>
      </div>
    </div>
  );
};

// ─── Warning Notice ───────────────────────────────────────────────────────────
const WarningNotice = ({ icon: Icon = AlertTriangle, title, description, variant = 'warning' }) => {
  const variants = {
    warning: 'bg-amber-500/5 border-amber-500/20 text-amber-400',
    danger: 'bg-red-500/5 border-red-500/20 text-red-400',
    info: 'bg-blue-500/5 border-blue-500/20 text-blue-400',
  };
  const cls = variants[variant] || variants.warning;

  return (
    <div className={`flex gap-3 p-4 rounded-xl border ${cls}`}>
      <Icon size={18} className="shrink-0 mt-0.5" />
      <div>
        <p className="text-sm font-semibold">{title}</p>
        <p className="text-xs opacity-80 mt-0.5 leading-relaxed">{description}</p>
      </div>
    </div>
  );
};

// ─── Main Component ───────────────────────────────────────────────────────────
export default function ExamInstructions() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [exam, setExam] = useState(null);
  const [loading, setLoading] = useState(true);
  const [starting, setStarting] = useState(false);
  const [agreed, setAgreed] = useState(false);

  useEffect(() => {
    const fetchExam = async () => {
      try {
        const res = await api.get(`/api/exams/${id}`);
        setExam(res.data?.data || res.data);
      } catch (err) {
        toast.error(err?.response?.data?.message || 'Failed to load exam details.');
      } finally {
        setLoading(false);
      }
    };
    fetchExam();
  }, [id]);

  const handleStart = async () => {
    if (!agreed) {
      toast.warning('Please confirm that you have read the instructions before starting.');
      return;
    }
    setStarting(true);
    try {
      const res = await api.post(`/api/exams/${id}/start`);
      const attemptId = res.data?.attempt_id || res.data?.data?.id || res.data?.id;
      if (!attemptId) {
        throw new Error('No attempt ID returned from server.');
      }
      toast.success('Exam started! Good luck!');
      navigate(`/intern/exams/take/${attemptId}`);
    } catch (err) {
      toast.error(err?.response?.data?.message || 'Failed to start exam. Please try again.');
    } finally {
      setStarting(false);
    }
  };

  // ── Loading ────────────────────────────────────────────────────────────────
  if (loading) {
    return (
      <div className="min-h-screen bg-gray-950 p-6 lg:p-8">
        <div className="max-w-3xl mx-auto space-y-6">
          <Skeleton className="h-6 w-48" />
          <Skeleton className="h-8 w-3/4" />
          <Skeleton className="h-4 w-full" />
          <Skeleton className="h-4 w-2/3" />
          <div className="grid grid-cols-2 gap-3">
            {Array.from({ length: 4 }).map((_, i) => (
              <Skeleton key={i} className="h-20 w-full rounded-xl" />
            ))}
          </div>
          <Skeleton className="h-32 w-full rounded-xl" />
          <Skeleton className="h-12 w-full rounded-xl" />
        </div>
      </div>
    );
  }

  if (!exam) {
    return (
      <div className="min-h-screen bg-gray-950 flex items-center justify-center">
        <div className="text-center space-y-3">
          <FileText size={48} className="text-gray-600 mx-auto" />
          <p className="text-gray-400">Exam not found.</p>
          <Link
            to="/intern/exams"
            className="inline-flex items-center gap-2 text-indigo-400 hover:text-indigo-300 text-sm"
          >
            <ArrowLeft size={14} /> Back to Exams
          </Link>
        </div>
      </div>
    );
  }

  const attemptsLeft =
    exam.max_attempts !== null && exam.max_attempts !== undefined
      ? exam.max_attempts - (exam.attempts_used ?? 0)
      : null;

  return (
    <div className="min-h-screen bg-gray-950 p-6 lg:p-8">
      <div className="max-w-3xl mx-auto space-y-6">

        {/* Back link */}
        <Link
          to="/intern/exams"
          className="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-300 transition-colors"
        >
          <ArrowLeft size={15} /> Back to Exams
        </Link>

        {/* Exam Header */}
        <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-3">
          <div className="flex items-start gap-3">
            <div className="p-3 rounded-xl bg-indigo-500/10 border border-indigo-500/20 shrink-0">
              <FileText size={24} className="text-indigo-400" />
            </div>
            <div>
              <h1 className="text-xl font-bold text-white">{exam.title}</h1>
              {exam.course_title && (
                <p className="text-sm text-gray-500 mt-1 flex items-center gap-1">
                  <BookOpen size={13} /> {exam.course_title}
                </p>
              )}
            </div>
          </div>

          {exam.description && (
            <p className="text-sm text-gray-400 leading-relaxed border-t border-gray-800 pt-4">
              {exam.description}
            </p>
          )}
        </div>

        {/* Exam Details Grid */}
        <div>
          <h2 className="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
            <Info size={14} className="text-indigo-400" />
            Exam Details
          </h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <DetailRow
              icon={Clock}
              label="Time Limit"
              value={exam.duration ? `${exam.duration} minutes` : 'No time limit'}
              accent="indigo"
            />
            <DetailRow
              icon={CheckSquare}
              label="Total Questions"
              value={exam.questions_count ?? '—'}
              accent="violet"
            />
            <DetailRow
              icon={Target}
              label="Pass Mark"
              value={exam.pass_mark ? `${exam.pass_mark}%` : '—'}
              accent="emerald"
            />
            <DetailRow
              icon={Repeat}
              label="Attempts Allowed"
              value={
                exam.max_attempts !== null && exam.max_attempts !== undefined
                  ? `${exam.max_attempts} (${attemptsLeft ?? 0} remaining)`
                  : 'Unlimited'
              }
              accent="amber"
            />
            {exam.show_results_immediately !== undefined && (
              <DetailRow
                icon={exam.show_results_immediately ? Eye : EyeOff}
                label="Results"
                value={exam.show_results_immediately ? 'Shown immediately' : 'Reviewed by admin'}
                accent="sky"
              />
            )}
          </div>
        </div>

        {/* Warning Notices */}
        <div className="space-y-3">
          <h2 className="text-sm font-semibold text-gray-400 uppercase tracking-wider flex items-center gap-2">
            <ShieldAlert size={14} className="text-amber-400" />
            Important Instructions
          </h2>

          <WarningNotice
            icon={Clock}
            variant="warning"
            title="The timer starts immediately"
            description="Once you click 'Start Exam', the countdown timer begins. You cannot pause the timer once the exam has started. Make sure you are in a quiet environment with a stable connection."
          />

          <WarningNotice
            icon={AlertTriangle}
            variant="danger"
            title="No going back to previous questions"
            description="Once you navigate away from a question or submit an answer, you may not be able to return and change it. Read each question carefully before answering."
          />

          <WarningNotice
            icon={ShieldAlert}
            variant="warning"
            title="Do not refresh or close the tab"
            description="Refreshing or closing the browser during the exam may result in your attempt being auto-submitted or lost. Ensure a stable internet connection throughout."
          />

          {exam.duration && (
            <WarningNotice
              icon={Info}
              variant="info"
              title="Auto-submission on time expiry"
              description={`Your exam will be automatically submitted after ${exam.duration} minutes. Unanswered questions will be marked as incorrect.`}
            />
          )}
        </div>

        {/* Agreement Checkbox */}
        <div className="bg-gray-900 border border-gray-800 rounded-2xl p-5">
          <label className="flex items-start gap-3 cursor-pointer group">
            <div className="mt-0.5">
              <input
                type="checkbox"
                checked={agreed}
                onChange={(e) => setAgreed(e.target.checked)}
                className="w-4 h-4 rounded accent-indigo-500 cursor-pointer"
              />
            </div>
            <span className="text-sm text-gray-300 group-hover:text-white transition-colors leading-relaxed">
              I have read and understood all the instructions above. I am ready to begin the exam
              and I accept the terms and conditions of this assessment.
            </span>
          </label>
        </div>

        {/* Start Button */}
        <div className="flex flex-col sm:flex-row gap-3">
          <Link
            to="/intern/exams"
            className="flex-1 flex items-center justify-center gap-2 py-3 px-6 rounded-xl border border-gray-700 text-gray-400 hover:text-white hover:border-gray-600 text-sm font-semibold transition-all duration-200"
          >
            <ArrowLeft size={16} />
            Cancel
          </Link>
          <button
            onClick={handleStart}
            disabled={starting || !agreed}
            className={`flex-1 flex items-center justify-center gap-2 py-3 px-6 rounded-xl text-sm font-bold transition-all duration-200 ${
              agreed && !starting
                ? 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-xl shadow-indigo-500/25'
                : 'bg-gray-800 text-gray-500 cursor-not-allowed'
            }`}
          >
            {starting ? (
              <>
                <div className="w-4 h-4 border-2 border-indigo-300 border-t-transparent rounded-full animate-spin" />
                Starting…
              </>
            ) : (
              <>
                <Play size={16} />
                Start Exam
              </>
            )}
          </button>
        </div>

        {attemptsLeft !== null && attemptsLeft <= 0 && (
          <p className="text-center text-sm text-red-400 flex items-center justify-center gap-2">
            <AlertTriangle size={14} /> You have no remaining attempts for this exam.
          </p>
        )}
      </div>
    </div>
  );
}
