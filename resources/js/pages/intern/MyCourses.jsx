import React, { useState, useEffect } from 'react';
import {
  BookOpen,
  CheckCircle,
  Clock,
  ChevronRight,
  Filter,
  Search,
  Layers,
  Zap,
  Star,
} from 'lucide-react';
import { toast } from 'react-toastify';
import { Link } from 'react-router-dom';
import api from '../../api';

// ─── Progress Bar ────────────────────────────────────────────────────────────
const ProgressBar = ({ value = 0 }) => (
  <div className="w-full bg-gray-800 rounded-full h-1.5 overflow-hidden">
    <div
      className="bg-indigo-500 h-1.5 rounded-full transition-all duration-500"
      style={{ width: `${Math.min(100, Math.max(0, value))}%` }}
    />
  </div>
);

// ─── Skeleton Card ────────────────────────────────────────────────────────────
const CourseCardSkeleton = () => (
  <div className="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden animate-pulse">
    <div className="bg-gray-800 h-44 w-full" />
    <div className="p-5 space-y-3">
      <div className="flex gap-2">
        <div className="bg-gray-800 h-5 w-20 rounded-full" />
        <div className="bg-gray-800 h-5 w-16 rounded-full" />
      </div>
      <div className="bg-gray-800 h-5 w-3/4 rounded" />
      <div className="bg-gray-800 h-4 w-full rounded" />
      <div className="bg-gray-800 h-1.5 w-full rounded-full" />
      <div className="bg-gray-800 h-10 w-full rounded-xl" />
    </div>
  </div>
);

// ─── Difficulty Badge ─────────────────────────────────────────────────────────
const DifficultyBadge = ({ level }) => {
  const map = {
    beginner: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    intermediate: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    advanced: 'bg-red-500/10 text-red-400 border-red-500/20',
  };
  const key = level?.toLowerCase() || 'beginner';
  return (
    <span
      className={`inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full border ${
        map[key] || map.beginner
      }`}
    >
      <Zap size={10} />
      {level || 'Beginner'}
    </span>
  );
};

// ─── Category Badge ────────────────────────────────────────────────────────────
const CategoryBadge = ({ category }) => (
  <span className="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
    <Layers size={10} />
    {category || 'General'}
  </span>
);

// ─── Course Card ───────────────────────────────────────────────────────────────
const CourseCard = ({ course }) => {
  const isCompleted = course.status === 'completed' || course.progress >= 100;
  const progress = course.progress ?? 0;

  return (
    <div className="bg-gray-900 border border-gray-800 hover:border-indigo-500/30 rounded-2xl overflow-hidden flex flex-col transition-all duration-200 group">
      {/* Thumbnail */}
      <div className="relative h-44 bg-gray-800 overflow-hidden">
        {course.thumbnail ? (
          <img
            src={course.thumbnail}
            alt={course.title}
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-900/40 to-gray-900">
            <BookOpen size={48} className="text-indigo-500/40" />
          </div>
        )}
        {isCompleted && (
          <div className="absolute top-3 right-3 bg-emerald-500 text-white text-xs font-bold px-2.5 py-1 rounded-full flex items-center gap-1">
            <CheckCircle size={11} />
            Completed
          </div>
        )}
      </div>

      {/* Body */}
      <div className="p-5 flex flex-col flex-1 gap-3">
        {/* Badges */}
        <div className="flex flex-wrap gap-2">
          {course.category && <CategoryBadge category={course.category} />}
          {course.difficulty && <DifficultyBadge level={course.difficulty} />}
        </div>

        {/* Title */}
        <h3 className="text-sm font-semibold text-white group-hover:text-indigo-300 transition-colors line-clamp-2 leading-snug">
          {course.title}
        </h3>

        {/* Description */}
        {course.description && (
          <p className="text-xs text-gray-500 line-clamp-2">{course.description}</p>
        )}

        {/* Meta */}
        {course.duration && (
          <div className="flex items-center gap-1 text-xs text-gray-500">
            <Clock size={12} />
            <span>{course.duration}</span>
          </div>
        )}

        {/* Progress */}
        <div className="mt-auto pt-2">
          <div className="flex justify-between text-xs text-gray-400 mb-2">
            <span>Progress</span>
            <span className={`font-semibold ${isCompleted ? 'text-emerald-400' : 'text-indigo-400'}`}>
              {progress}%
            </span>
          </div>
          <ProgressBar value={progress} />
        </div>

        {/* CTA Button */}
        <Link
          to={`/intern/courses/${course.id}`}
          className={`mt-2 w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-sm font-semibold transition-all duration-200 ${
            isCompleted
              ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20'
              : 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-500/20'
          }`}
        >
          {isCompleted ? (
            <>
              <Star size={15} /> Review Course
            </>
          ) : (
            <>
              <ChevronRight size={15} /> Continue Learning
            </>
          )}
        </Link>
      </div>
    </div>
  );
};

// ─── Main Component ────────────────────────────────────────────────────────────
export default function MyCourses() {
  const [courses, setCourses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState('all'); // 'all' | 'in_progress' | 'completed'
  const [search, setSearch] = useState('');

  useEffect(() => {
    const fetchCourses = async () => {
      try {
        const res = await api.get('/api/courses');
        setCourses(res.data?.data || res.data || []);
      } catch (err) {
        toast.error(err?.response?.data?.message || 'Failed to load courses.');
      } finally {
        setLoading(false);
      }
    };
    fetchCourses();
  }, []);

  const filtered = courses.filter((c) => {
    const isCompleted = c.status === 'completed' || c.progress >= 100;
    const matchStatus =
      filter === 'all' ||
      (filter === 'completed' && isCompleted) ||
      (filter === 'in_progress' && !isCompleted);
    const matchSearch =
      !search ||
      c.title?.toLowerCase().includes(search.toLowerCase()) ||
      c.category?.toLowerCase().includes(search.toLowerCase());
    return matchStatus && matchSearch;
  });

  const tabs = [
    { key: 'all', label: 'All Courses' },
    { key: 'in_progress', label: 'In Progress' },
    { key: 'completed', label: 'Completed' },
  ];

  return (
    <div className="min-h-screen bg-gray-950 p-6 lg:p-8">
      <div className="max-w-7xl mx-auto space-y-6">

        {/* Header */}
        <div>
          <h1 className="text-2xl font-bold text-white flex items-center gap-2">
            <BookOpen className="text-indigo-400" size={24} />
            My Courses
          </h1>
          <p className="text-gray-400 text-sm mt-1">
            All courses assigned to you. Track your progress and continue learning.
          </p>
        </div>

        {/* Filters + Search */}
        <div className="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
          {/* Tabs */}
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

          {/* Search */}
          <div className="relative">
            <Search
              size={15}
              className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none"
            />
            <input
              type="text"
              placeholder="Search courses..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="pl-9 pr-4 py-2.5 rounded-xl bg-gray-900 border border-gray-700 text-sm text-gray-200 placeholder-gray-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30 w-64 transition-all"
            />
          </div>
        </div>

        {/* Count label */}
        {!loading && (
          <p className="text-xs text-gray-500">
            Showing <span className="text-gray-300 font-medium">{filtered.length}</span> course
            {filtered.length !== 1 ? 's' : ''}
          </p>
        )}

        {/* Grid */}
        {loading ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            {Array.from({ length: 8 }).map((_, i) => (
              <CourseCardSkeleton key={i} />
            ))}
          </div>
        ) : filtered.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-24 text-gray-600">
            <BookOpen size={48} className="mb-4 opacity-30" />
            <p className="text-base font-medium text-gray-400">No courses found.</p>
            <p className="text-sm mt-1">Try adjusting your filters or search term.</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            {filtered.map((course) => (
              <CourseCard key={course.id} course={course} />
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
