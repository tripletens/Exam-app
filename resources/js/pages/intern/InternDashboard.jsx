import React, { useState, useEffect } from 'react';
import {
  BookOpen,
  CheckCircle,
  Award,
  TrendingUp,
  BarChart2,
  Clock,
  ChevronRight,
  Target,
} from 'lucide-react';
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
} from 'recharts';
import { toast } from 'react-toastify';
import api from '../../api';

// ─── Skeleton helpers ────────────────────────────────────────────────────────
const SkeletonBox = ({ className = '' }) => (
  <div className={`animate-pulse bg-gray-800 rounded-lg ${className}`} />
);

const StatCardSkeleton = () => (
  <div className="bg-gray-900 rounded-2xl p-6 flex flex-col gap-3 border border-gray-800">
    <SkeletonBox className="h-4 w-24" />
    <SkeletonBox className="h-8 w-16" />
    <SkeletonBox className="h-3 w-32" />
  </div>
);

// ─── Stat Card ───────────────────────────────────────────────────────────────
const StatCard = ({ icon: Icon, label, value, sub, accent = 'indigo', trend }) => {
  const accentMap = {
    indigo: {
      bg: 'bg-indigo-500/10',
      icon: 'text-indigo-400',
      border: 'border-indigo-500/20',
      ring: 'ring-indigo-500/30',
    },
    emerald: {
      bg: 'bg-emerald-500/10',
      icon: 'text-emerald-400',
      border: 'border-emerald-500/20',
      ring: 'ring-emerald-500/30',
    },
    violet: {
      bg: 'bg-violet-500/10',
      icon: 'text-violet-400',
      border: 'border-violet-500/20',
      ring: 'ring-violet-500/30',
    },
    amber: {
      bg: 'bg-amber-500/10',
      icon: 'text-amber-400',
      border: 'border-amber-500/20',
      ring: 'ring-amber-500/30',
    },
    sky: {
      bg: 'bg-sky-500/10',
      icon: 'text-sky-400',
      border: 'border-sky-500/20',
      ring: 'ring-sky-500/30',
    },
  };
  const a = accentMap[accent] || accentMap.indigo;

  return (
    <div
      className={`bg-gray-900 rounded-2xl p-6 flex flex-col gap-4 border ${a.border} hover:ring-1 ${a.ring} transition-all duration-200`}
    >
      <div className="flex items-center justify-between">
        <span className="text-sm font-medium text-gray-400 tracking-wide uppercase">{label}</span>
        <div className={`p-2 rounded-xl ${a.bg}`}>
          <Icon size={20} className={a.icon} />
        </div>
      </div>
      <div>
        <p className="text-3xl font-bold text-white">{value}</p>
        {sub && <p className="text-xs text-gray-500 mt-1">{sub}</p>}
      </div>
      {trend !== undefined && (
        <div className="flex items-center gap-1 text-xs text-emerald-400">
          <TrendingUp size={12} />
          <span>{trend}</span>
        </div>
      )}
    </div>
  );
};

// ─── Progress Bar ────────────────────────────────────────────────────────────
const ProgressBar = ({ value = 0, color = 'bg-indigo-500' }) => (
  <div className="w-full bg-gray-800 rounded-full h-2 overflow-hidden">
    <div
      className={`${color} h-2 rounded-full transition-all duration-500`}
      style={{ width: `${Math.min(100, Math.max(0, value))}%` }}
    />
  </div>
);

// ─── Custom Tooltip for BarChart ─────────────────────────────────────────────
const CustomTooltip = ({ active, payload, label }) => {
  if (active && payload && payload.length) {
    return (
      <div className="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 shadow-xl">
        <p className="text-xs text-gray-400 mb-1">{label}</p>
        <p className="text-white font-bold text-sm">{payload[0].value}%</p>
      </div>
    );
  }
  return null;
};

// ─── Main Component ──────────────────────────────────────────────────────────
export default function InternDashboard() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchDashboard = async () => {
      try {
        const res = await api.get('/api/dashboard/intern');
        setData(res.data);
      } catch (err) {
        toast.error(
          err?.response?.data?.message || 'Failed to load dashboard. Please refresh.'
        );
      } finally {
        setLoading(false);
      }
    };
    fetchDashboard();
  }, []);

  // ── Loading State ──────────────────────────────────────────────────────────
  if (loading) {
    return (
      <div className="min-h-screen bg-gray-950 p-6 lg:p-8">
        <div className="max-w-7xl mx-auto space-y-8">
          <div className="space-y-2">
            <SkeletonBox className="h-6 w-48" />
            <SkeletonBox className="h-4 w-72" />
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            {Array.from({ length: 5 }).map((_, i) => (
              <StatCardSkeleton key={i} />
            ))}
          </div>
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div className="lg:col-span-2 bg-gray-900 rounded-2xl p-6 border border-gray-800">
              <SkeletonBox className="h-5 w-40 mb-6" />
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {Array.from({ length: 4 }).map((_, i) => (
                  <SkeletonBox key={i} className="h-36 w-full" />
                ))}
              </div>
            </div>
            <SkeletonBox className="h-72 w-full rounded-2xl" />
          </div>
        </div>
      </div>
    );
  }

  const stats = data?.stats || {};
  const inProgressCourses = data?.in_progress_courses || [];
  const recentResults = data?.recent_results || [];
  const scoreHistory = data?.score_history || [];
  const internName = data?.intern_name || 'Intern';

  return (
    <div className="min-h-screen bg-gray-950 p-6 lg:p-8">
      <div className="max-w-7xl mx-auto space-y-8">

        {/* Welcome Header */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 className="text-2xl font-bold text-white">
              Welcome back, <span className="text-indigo-400">{internName}</span> 👋
            </h1>
            <p className="text-gray-400 mt-1 text-sm">
              Here's an overview of your learning progress.
            </p>
          </div>
          <div className="flex items-center gap-2 bg-gray-900 border border-gray-800 rounded-xl px-4 py-2">
            <Clock size={16} className="text-indigo-400" />
            <span className="text-sm text-gray-300">
              {new Date().toLocaleDateString('en-GB', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
              })}
            </span>
          </div>
        </div>

        {/* Stat Cards */}
        <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
          <StatCard
            icon={BookOpen}
            label="Courses Assigned"
            value={stats.courses_assigned ?? 0}
            sub="Total assigned to you"
            accent="indigo"
          />
          <StatCard
            icon={CheckCircle}
            label="Courses Completed"
            value={stats.courses_completed ?? 0}
            sub="Finished courses"
            accent="emerald"
          />
          <StatCard
            icon={BarChart2}
            label="Exams Completed"
            value={stats.exams_completed ?? 0}
            sub="Submitted attempts"
            accent="violet"
          />
          <StatCard
            icon={Target}
            label="Average Score"
            value={`${stats.average_score ?? 0}%`}
            sub="Across all exams"
            accent="amber"
            trend={stats.score_trend || undefined}
          />
          <StatCard
            icon={Award}
            label="Overall Progress"
            value={`${stats.overall_progress ?? 0}%`}
            sub="Platform completion"
            accent="sky"
          />
        </div>

        {/* Overall Progress Bar */}
        <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6">
          <div className="flex items-center justify-between mb-3">
            <span className="text-sm font-medium text-gray-300">Overall Learning Progress</span>
            <span className="text-sm font-bold text-indigo-400">
              {stats.overall_progress ?? 0}%
            </span>
          </div>
          <ProgressBar value={stats.overall_progress ?? 0} color="bg-indigo-500" />
        </div>

        {/* In Progress Courses + Score Chart */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">

          {/* In Progress Courses */}
          <div className="lg:col-span-2 bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-base font-semibold text-white flex items-center gap-2">
                <BookOpen size={18} className="text-indigo-400" />
                In Progress Courses
              </h2>
              <a
                href="/intern/courses"
                className="text-xs text-indigo-400 hover:text-indigo-300 flex items-center gap-1 transition-colors"
              >
                View all <ChevronRight size={14} />
              </a>
            </div>

            {inProgressCourses.length === 0 ? (
              <div className="flex flex-col items-center justify-center h-40 text-gray-500">
                <BookOpen size={32} className="mb-2 opacity-40" />
                <p className="text-sm">No courses in progress.</p>
              </div>
            ) : (
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {inProgressCourses.map((course) => (
                  <a
                    key={course.id}
                    href={`/intern/courses/${course.id}`}
                    className="group bg-gray-800/60 hover:bg-gray-800 border border-gray-700 hover:border-indigo-500/40 rounded-xl p-4 flex flex-col gap-3 transition-all duration-200"
                  >
                    <div className="flex items-start justify-between gap-2">
                      <div>
                        <p className="text-sm font-semibold text-white group-hover:text-indigo-300 transition-colors line-clamp-2">
                          {course.title}
                        </p>
                        {course.category && (
                          <span className="inline-block mt-1 text-xs bg-indigo-500/10 text-indigo-400 px-2 py-0.5 rounded-full border border-indigo-500/20">
                            {course.category}
                          </span>
                        )}
                      </div>
                      <ChevronRight
                        size={16}
                        className="text-gray-500 group-hover:text-indigo-400 shrink-0 mt-0.5 transition-colors"
                      />
                    </div>
                    <div className="mt-auto">
                      <div className="flex justify-between text-xs text-gray-400 mb-1.5">
                        <span>Progress</span>
                        <span className="text-white font-medium">{course.progress ?? 0}%</span>
                      </div>
                      <ProgressBar value={course.progress ?? 0} />
                    </div>
                  </a>
                ))}
              </div>
            )}
          </div>

          {/* Score History Chart */}
          <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6 flex flex-col">
            <h2 className="text-base font-semibold text-white flex items-center gap-2 mb-6">
              <BarChart2 size={18} className="text-indigo-400" />
              Score History
            </h2>
            {scoreHistory.length === 0 ? (
              <div className="flex flex-col items-center justify-center flex-1 text-gray-500">
                <BarChart2 size={32} className="mb-2 opacity-40" />
                <p className="text-sm text-center">No exam scores yet.</p>
              </div>
            ) : (
              <div className="flex-1 min-h-0">
                <ResponsiveContainer width="100%" height={220}>
                  <BarChart
                    data={scoreHistory}
                    margin={{ top: 4, right: 4, left: -24, bottom: 0 }}
                  >
                    <CartesianGrid strokeDasharray="3 3" stroke="#1f2937" vertical={false} />
                    <XAxis
                      dataKey="label"
                      tick={{ fill: '#6b7280', fontSize: 11 }}
                      axisLine={false}
                      tickLine={false}
                    />
                    <YAxis
                      domain={[0, 100]}
                      tick={{ fill: '#6b7280', fontSize: 11 }}
                      axisLine={false}
                      tickLine={false}
                    />
                    <Tooltip content={<CustomTooltip />} cursor={{ fill: 'rgba(99,102,241,0.08)' }} />
                    <Bar
                      dataKey="score"
                      fill="#6366f1"
                      radius={[6, 6, 0, 0]}
                      maxBarSize={40}
                    />
                  </BarChart>
                </ResponsiveContainer>
              </div>
            )}
          </div>
        </div>

        {/* Recent Results Table */}
        <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6">
          <h2 className="text-base font-semibold text-white flex items-center gap-2 mb-6">
            <CheckCircle size={18} className="text-indigo-400" />
            Recent Exam Results
          </h2>

          {recentResults.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-12 text-gray-500">
              <Award size={36} className="mb-3 opacity-40" />
              <p className="text-sm">No exam results yet.</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-gray-800">
                    {['Exam', 'Course', 'Score', 'Status', 'Date'].map((h) => (
                      <th
                        key={h}
                        className="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider pr-4"
                      >
                        {h}
                      </th>
                    ))}
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-800/60">
                  {recentResults.map((result) => {
                    const passed =
                      result.passed === true ||
                      result.status?.toLowerCase() === 'passed' ||
                      (result.score !== undefined &&
                        result.pass_mark !== undefined &&
                        result.score >= result.pass_mark);
                    return (
                      <tr
                        key={result.id}
                        className="hover:bg-gray-800/40 transition-colors"
                      >
                        <td className="py-3 pr-4 text-gray-200 font-medium">{result.exam_title}</td>
                        <td className="py-3 pr-4 text-gray-400">{result.course_title}</td>
                        <td className="py-3 pr-4">
                          <span className="text-white font-semibold">
                            {result.score ?? '—'}
                            {result.score !== undefined && '%'}
                          </span>
                        </td>
                        <td className="py-3 pr-4">
                          <span
                            className={`inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full ${
                              passed
                                ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                                : 'bg-red-500/10 text-red-400 border border-red-500/20'
                            }`}
                          >
                            {passed ? <CheckCircle size={11} /> : <Clock size={11} />}
                            {passed ? 'Passed' : 'Failed'}
                          </span>
                        </td>
                        <td className="py-3 text-gray-500 text-xs">
                          {result.submitted_at
                            ? new Date(result.submitted_at).toLocaleDateString('en-GB', {
                                day: 'numeric',
                                month: 'short',
                                year: 'numeric',
                              })
                            : '—'}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
