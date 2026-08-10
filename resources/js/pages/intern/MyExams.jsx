import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
  FileText,
  Clock,
  Target,
  BookOpen,
  CheckSquare,
  CalendarClock,
  Play,
  AlertTriangle,
  Search,
  Filter,
  ChevronRight,
  Hourglass,
} from 'lucide-react';
import { toast } from 'react-toastify';
import api from '../../api';

// ─── Skeleton ─────────────────────────────────────────────────────────────────
const Skeleton = ({ className = '' }) => (
  <div className={`animate-pulse bg-gray-800 rounded-lg ${className}`} />
);

const ExamCardSkeleton = () => (
  <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">
    <Skeleton className="h-5 w-3/4" />
    <Skeleton className="h-4 w-1/2" />
    <div className="grid grid-cols-2 gap-3">
      <Skeleton className="h-14 w-full rounded-xl" />
      <Skeleton className="h-14 w-full rounded-xl" />
      <Skeleton className="h-14 w-full rounded-xl" />
      <Skeleton className="h-14 w-full rounded-xl" />
    </div>
    <Skeleton className="h-10 w-full rounded-xl" />
  </div>
);

// ─── Meta Item ────────────────────────────────────────────────────────────────
const MetaItem = ({ icon: Icon, label, value, accent = 'indigo' }) => {
  const accentMap = {
    indigo: 'text-indigo-400 bg-indigo-500/10',
    amber: 'text-amber-400 bg-amber-500/10',
    emerald: 'text-emerald-400 bg-emerald-500/10',
    red: 'text-red-400 bg-red-500/10',
    violet: 'text-violet-400 bg-violet-500/10',
  };
  const cls = accentMap[accent] || accentMap.indigo;

  return (
    <div className={`flex flex-col gap-1.5 p-3 rounded-xl ${cls.split(' ')[1]} border border-gray-800`}>
      <div className={`flex items-center gap-1.5 ${cls.split(' ')[0]}`}>
        <Icon size={13} />
        <span className="text-xs font-medium text-gray-400">{label}</span>
      </div>
      <span className="text-sm font-bold text-white">{value}</span>
    </div>
  );
};

// ─── Status Badge ─────────────────────────────────────────────────────────────
const StatusBadge = ({ exam }) => {
  const now = new Date();
  const deadline = exam.deadline ? new Date(exam.deadline) : null;
  const isOverdue = deadline && now > deadline;
  const attemptsLeft =
    exam.max_attempts !== null && exam.max_attempts !== undefined
      ? exam.max_attempts - (exam.attempts_used ?? 0)
      : null;
  const noAttempts = attemptsLeft !== null && attemptsLeft <= 0;

  if (noAttempts) {
    return (
      <span className="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-700/60 text-gray-400 border border-gray-700">
        No Attempts Left
      </span>
    );
  }
  if (isOverdue) {
    return (
      <span className="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-red-500/10 text-red-400 border border-red-500/20">
        <AlertTriangle size={11} /> Overdue
      </span>
    );
  }
  if (exam.status === 'completed' || exam.status === 'passed' || exam.status === 'failed') {
    return (
      <span className="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-700/60 text-gray-300 border border-gray-700">
        Attempted
      </span>
    );
  }
  return (
    <span className="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
      Available
    </span>
  );
};

// ─── Exam Card ────────────────────────────────────────────────────────────────
const ExamCard = ({ exam }) => {
  const deadline = exam.deadline ? new Date(exam.deadline) : null;
  const now = new Date();
  const isOverdue = deadline && now > deadline;
  const attemptsLeft =
    exam.max_attempts !== null && exam.max_attempts !== undefined
      ? exam.max_attempts - (exam.attempts_used ?? 0)
      : null;
  const canStart = !isOverdue && (attemptsLeft === null || attemptsLeft > 0);

  const deadlineString = deadline
    ? deadline.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    : 'No deadline';

  return (
    <div className="bg-gray-900 border border-gray-800 hover:border-indigo-500/20 rounded-2xl p-6 flex flex-col gap-4 transition-all duration-200">
      {/* Header */}
      <div className="flex items-start justify-between gap-3">
        <div className="min-w-0">
          <h3 className="text-base font-bold text-white line-clamp-2">{exam.title}</h3>
          {exam.course_title && (
            <p className="text-xs text-gray-500 mt-1 flex items-center gap-1">
              <BookOpen size={11} /> {exam.course_title}
            </p>
          )}
        </div>
        <StatusBadge exam={exam} />
      </div>

      {/* Meta Grid */}
      <div className="grid grid-cols-2 gap-2">
        <MetaItem
          icon={CheckSquare}
          label="Questions"
          value={exam.questions_count ?? '—'}
          accent="indigo"
        />
        <MetaItem
          icon={Clock}
          label="Duration"
          value={exam.duration ? `${exam.duration} min` : '—'}
          accent="violet"
        />
        <MetaItem
          icon={Target}
          label="Pass Mark"
          value={exam.pass_mark ? `${exam.pass_mark}%` : '—'}
          accent="emerald"
        />
        <MetaItem
          icon={Hourglass}
          label="Attempts Left"
          value={attemptsLeft !== null ? attemptsLeft : '∞'}
          accent="amber"
        />
      </div>

      {/* Deadline */}
      <div
        className={`flex items-center gap-2 text-xs px-3 py-2 rounded-xl ${
          isOverdue
            ? 'bg-red-500/10 text-red-400 border border-red-500/20'
            : 'bg-gray-800/60 text-gray-400 border border-gray-700'
        }`}
      >
        <CalendarClock size={13} />
        <span>
          <span className="font-medium">Deadline: </span>
          {deadlineString}
        </span>
      </div>

      {/* CTA */}
      {canStart ? (
        <Link
          to={`/intern/exams/${exam.id}/instructions`}
          className="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-indigo-500/20 transition-all duration-200 mt-auto"
        >
          <Play size={15} />
          Start Exam
        </Link>
      ) : (
        <button
          disabled
          className="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-gray-800 text-gray-500 text-sm font-semibold rounded-xl cursor-not-allowed mt-auto"
        >
          {isOverdue ? (
            <>
              <AlertTriangle size={15} /> Deadline Passed
            </>
          ) : (
            <>
              <FileText size={15} /> No Attempts Remaining
            </>
          )}
        </button>
      )}
    </div>
  );
};

// ─── Main Component ───────────────────────────────────────────────────────────
export default function MyExams() {
  const [exams, setExams] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [filter, setFilter] = useState('all'); // 'all' | 'available' | 'attempted'

  useEffect(() => {
    const fetchExams = async () => {
      try {
        const res = await api.get('/exams');
        setExams(res.data?.data || res.data || []);
      } catch (err) {
        toast.error(err?.response?.data?.message || 'Failed to load exams.');
      } finally {
        setLoading(false);
      }
    };
    fetchExams();
  }, []);

  const filtered = exams.filter((e) => {
    const attempted = ['completed', 'passed', 'failed'].includes(e.status);
    const matchFilter =
      filter === 'all' ||
      (filter === 'available' && !attempted) ||
      (filter === 'attempted' && attempted);
    const matchSearch =
      !search ||
      e.title?.toLowerCase().includes(search.toLowerCase()) ||
      e.course_title?.toLowerCase().includes(search.toLowerCase());
    return matchFilter && matchSearch;
  });

  const tabs = [
    { key: 'all', label: 'All Exams' },
    { key: 'available', label: 'Available' },
    { key: 'attempted', label: 'Attempted' },
  ];

  return (
    <div className="min-h-screen bg-gray-950 p-6 lg:p-8">
      <div className="max-w-7xl mx-auto space-y-6">

        {/* Header */}
        <div>
          <h1 className="text-2xl font-bold text-white flex items-center gap-2">
            <FileText className="text-indigo-400" size={24} />
            My Exams
          </h1>
          <p className="text-gray-400 text-sm mt-1">
            View and take exams assigned to you. Check deadlines and attempt limits.
          </p>
        </div>

        {/* Filters + Search */}
        <div className="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
          <div className="flex gap-1 bg-gray-900 border border-gray-800 rounded-xl p-1">
            {tabs.map((t) => (
              <button
                key={t.key}
                onClick={() => setFilter(t.key)}
                className={`flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150 ${
                  filter === t.key
                    ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20'
                    : 'text-gray-400 hover:text-gray-200'
                }`}
              >
                <Filter size={13} />
                {t.label}
              </button>
            ))}
          </div>
          <div className="relative">
            <Search
              size={15}
              className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none"
            />
            <input
              type="text"
              placeholder="Search exams..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="pl-9 pr-4 py-2.5 rounded-xl bg-gray-900 border border-gray-700 text-sm text-gray-200 placeholder-gray-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30 w-64 transition-all"
            />
          </div>
        </div>

        {/* Count */}
        {!loading && (
          <p className="text-xs text-gray-500">
            Showing <span className="text-gray-300 font-medium">{filtered.length}</span> exam
            {filtered.length !== 1 ? 's' : ''}
          </p>
        )}

        {/* Grid */}
        {loading ? (
          <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            {Array.from({ length: 6 }).map((_, i) => (
              <ExamCardSkeleton key={i} />
            ))}
          </div>
        ) : filtered.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-24 text-gray-600">
            <FileText size={48} className="mb-4 opacity-30" />
            <p className="text-base font-medium text-gray-400">No exams found.</p>
            <p className="text-sm mt-1">Try adjusting your filters.</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            {filtered.map((exam) => (
              <ExamCard key={exam.id} exam={exam} />
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
