import React, { useState, useEffect, useCallback } from 'react';
import { useParams, Link } from 'react-router-dom';
import {
  BookOpen,
  CheckCircle,
  Circle,
  ChevronRight,
  ChevronDown,
  FileText,
  Download,
  PlayCircle,
  Lock,
  Award,
  Clock,
  Layers,
  ArrowLeft,
  ExternalLink,
} from 'lucide-react';
import { toast } from 'react-toastify';
import api from '../../api';

// ─── Progress Bar ────────────────────────────────────────────────────────────
const ProgressBar = ({ value = 0 }) => (
  <div className="w-full bg-gray-800 rounded-full h-2 overflow-hidden">
    <div
      className="bg-indigo-500 h-2 rounded-full transition-all duration-500"
      style={{ width: `${Math.min(100, Math.max(0, value))}%` }}
    />
  </div>
);

// ─── Skeleton ─────────────────────────────────────────────────────────────────
const Skeleton = ({ className = '' }) => (
  <div className={`animate-pulse bg-gray-800 rounded-lg ${className}`} />
);

// ─── Resource Icon ────────────────────────────────────────────────────────────
const ResourceIcon = ({ type }) => {
  const map = {
    pdf: <FileText size={14} className="text-red-400" />,
    video: <PlayCircle size={14} className="text-blue-400" />,
    link: <ExternalLink size={14} className="text-indigo-400" />,
  };
  return map[type?.toLowerCase()] || <Download size={14} className="text-gray-400" />;
};

// ─── Main Component ───────────────────────────────────────────────────────────
export default function CourseDetail() {
  const { id } = useParams();
  const [course, setCourse] = useState(null);
  const [loading, setLoading] = useState(true);
  const [activeLesson, setActiveLesson] = useState(null);
  const [openModules, setOpenModules] = useState({});
  const [completing, setCompleting] = useState(null); // lessonId being marked complete

  // Fetch course
  useEffect(() => {
    const fetchCourse = async () => {
      try {
        const res = await api.get(`/api/courses/${id}`);
        const data = res.data?.data || res.data;
        setCourse(data);

        // Open the first module and select first incomplete lesson by default
        if (data?.modules?.length) {
          const firstModule = data.modules[0];
          setOpenModules({ [firstModule.id]: true });

          // Find first incomplete lesson
          let found = null;
          for (const mod of data.modules) {
            for (const lesson of mod.lessons || []) {
              if (!lesson.completed) {
                found = lesson;
                break;
              }
            }
            if (found) break;
          }
          setActiveLesson(found || data.modules[0]?.lessons?.[0] || null);
        }
      } catch (err) {
        toast.error(err?.response?.data?.message || 'Failed to load course.');
      } finally {
        setLoading(false);
      }
    };
    fetchCourse();
  }, [id]);

  // Toggle module open/close
  const toggleModule = useCallback((moduleId) => {
    setOpenModules((prev) => ({ ...prev, [moduleId]: !prev[moduleId] }));
  }, []);

  // Mark lesson complete
  const markComplete = useCallback(
    async (lesson) => {
      if (lesson.completed || completing === lesson.id) return;
      setCompleting(lesson.id);
      try {
        await api.post(`/api/lessons/${lesson.id}/complete`);
        // Update local state
        setCourse((prev) => {
          if (!prev) return prev;
          const updatedModules = prev.modules.map((mod) => ({
            ...mod,
            lessons: (mod.lessons || []).map((l) =>
              l.id === lesson.id ? { ...l, completed: true } : l
            ),
          }));
          // Recalculate progress
          const allLessons = updatedModules.flatMap((m) => m.lessons || []);
          const completedCount = allLessons.filter((l) => l.completed).length;
          const progress = allLessons.length
            ? Math.round((completedCount / allLessons.length) * 100)
            : 0;
          return { ...prev, modules: updatedModules, progress };
        });
        setActiveLesson((prev) =>
          prev?.id === lesson.id ? { ...prev, completed: true } : prev
        );
        toast.success('Lesson marked as complete!');
      } catch (err) {
        toast.error(err?.response?.data?.message || 'Failed to mark lesson complete.');
      } finally {
        setCompleting(null);
      }
    },
    [completing]
  );

  // ── Loading ────────────────────────────────────────────────────────────────
  if (loading) {
    return (
      <div className="min-h-screen bg-gray-950 flex">
        {/* Sidebar skeleton */}
        <aside className="w-80 shrink-0 bg-gray-900 border-r border-gray-800 p-4 space-y-4 hidden lg:block">
          <Skeleton className="h-6 w-40 mb-6" />
          {Array.from({ length: 3 }).map((_, i) => (
            <div key={i} className="space-y-2">
              <Skeleton className="h-10 w-full" />
              <Skeleton className="h-8 w-full" />
              <Skeleton className="h-8 w-full" />
            </div>
          ))}
        </aside>
        {/* Content skeleton */}
        <main className="flex-1 p-6 lg:p-8 space-y-6">
          <Skeleton className="h-8 w-64" />
          <Skeleton className="h-4 w-full" />
          <Skeleton className="h-4 w-3/4" />
          <Skeleton className="h-48 w-full" />
        </main>
      </div>
    );
  }

  if (!course) {
    return (
      <div className="min-h-screen bg-gray-950 flex items-center justify-center">
        <div className="text-center space-y-3">
          <BookOpen size={48} className="text-gray-600 mx-auto" />
          <p className="text-gray-400">Course not found.</p>
          <Link
            to="/intern/courses"
            className="inline-flex items-center gap-2 text-indigo-400 hover:text-indigo-300 text-sm"
          >
            <ArrowLeft size={14} /> Back to Courses
          </Link>
        </div>
      </div>
    );
  }

  const modules = course.modules || [];
  const allLessons = modules.flatMap((m) => m.lessons || []);
  const completedCount = allLessons.filter((l) => l.completed).length;
  const progress = course.progress ?? (allLessons.length ? Math.round((completedCount / allLessons.length) * 100) : 0);
  const activeModule = modules.find((m) =>
    (m.lessons || []).some((l) => l.id === activeLesson?.id)
  );

  return (
    <div className="min-h-screen bg-gray-950 flex flex-col">

      {/* ── Top Header Bar ──────────────────────────────────────────────── */}
      <div className="bg-gray-900 border-b border-gray-800 px-6 py-4">
        <div className="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div className="flex items-center gap-3">
            <Link
              to="/intern/courses"
              className="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white transition-colors"
            >
              <ArrowLeft size={16} />
            </Link>
            <div>
              <h1 className="text-base font-bold text-white line-clamp-1">{course.title}</h1>
              <div className="flex items-center gap-3 mt-0.5">
                {course.category && (
                  <span className="inline-flex items-center gap-1 text-xs text-indigo-400">
                    <Layers size={11} /> {course.category}
                  </span>
                )}
                {course.duration && (
                  <span className="inline-flex items-center gap-1 text-xs text-gray-500">
                    <Clock size={11} /> {course.duration}
                  </span>
                )}
              </div>
            </div>
          </div>
          <div className="flex items-center gap-4 min-w-0 sm:w-64">
            <div className="flex-1">
              <div className="flex justify-between text-xs text-gray-400 mb-1.5">
                <span>{completedCount}/{allLessons.length} lessons</span>
                <span className="text-indigo-400 font-semibold">{progress}%</span>
              </div>
              <ProgressBar value={progress} />
            </div>
            {progress === 100 && (
              <Award size={20} className="text-amber-400 shrink-0" />
            )}
          </div>
        </div>
      </div>

      <div className="flex flex-1 overflow-hidden max-w-full">

        {/* ── Left Sidebar – Modules & Lessons ────────────────────────── */}
        <aside className="w-80 shrink-0 bg-gray-900 border-r border-gray-800 overflow-y-auto hidden lg:flex flex-col">
          <div className="p-4 border-b border-gray-800">
            <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
              Course Content
            </p>
          </div>
          <nav className="flex-1 p-3 space-y-1">
            {modules.map((mod, modIdx) => {
              const isOpen = !!openModules[mod.id];
              const modLessons = mod.lessons || [];
              const modCompleted = modLessons.filter((l) => l.completed).length;

              return (
                <div key={mod.id}>
                  {/* Module Header */}
                  <button
                    onClick={() => toggleModule(mod.id)}
                    className="w-full flex items-center justify-between px-3 py-3 rounded-xl hover:bg-gray-800/60 transition-colors group"
                  >
                    <div className="flex items-center gap-2 min-w-0">
                      <span className="shrink-0 text-xs text-gray-600 font-mono">
                        {String(modIdx + 1).padStart(2, '0')}
                      </span>
                      <span className="text-sm font-semibold text-gray-200 truncate group-hover:text-white transition-colors">
                        {mod.title}
                      </span>
                    </div>
                    <div className="flex items-center gap-2 shrink-0">
                      <span className="text-xs text-gray-500">
                        {modCompleted}/{modLessons.length}
                      </span>
                      <ChevronDown
                        size={14}
                        className={`text-gray-500 transition-transform duration-200 ${
                          isOpen ? 'rotate-180' : ''
                        }`}
                      />
                    </div>
                  </button>

                  {/* Lessons */}
                  {isOpen && (
                    <div className="ml-3 pl-3 border-l border-gray-800 mt-1 mb-2 space-y-0.5">
                      {modLessons.map((lesson) => {
                        const isActive = activeLesson?.id === lesson.id;
                        return (
                          <button
                            key={lesson.id}
                            onClick={() => setActiveLesson(lesson)}
                            className={`w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-left transition-all duration-150 ${
                              isActive
                                ? 'bg-indigo-600/20 border border-indigo-500/30 text-indigo-300'
                                : 'hover:bg-gray-800/60 text-gray-400 hover:text-gray-200'
                            }`}
                          >
                            {lesson.completed ? (
                              <CheckCircle
                                size={15}
                                className={`shrink-0 ${
                                  isActive ? 'text-indigo-400' : 'text-emerald-400'
                                }`}
                              />
                            ) : (
                              <Circle
                                size={15}
                                className={`shrink-0 ${
                                  isActive ? 'text-indigo-400' : 'text-gray-600'
                                }`}
                              />
                            )}
                            <span className="text-xs font-medium line-clamp-2 leading-snug">
                              {lesson.title}
                            </span>
                            {lesson.duration && (
                              <span className="ml-auto text-xs text-gray-600 shrink-0">
                                {lesson.duration}
                              </span>
                            )}
                          </button>
                        );
                      })}

                      {/* Module Resources */}
                      {(mod.resources || []).length > 0 && (
                        <div className="mt-2 pt-2 border-t border-gray-800/60">
                          <p className="text-xs font-semibold text-gray-600 uppercase tracking-wider px-3 mb-1">
                            Resources
                          </p>
                          {mod.resources.map((res) => (
                            <a
                              key={res.id}
                              href={res.url}
                              target="_blank"
                              rel="noopener noreferrer"
                              className="flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-gray-500 hover:text-indigo-400 hover:bg-gray-800/40 transition-colors"
                            >
                              <ResourceIcon type={res.type} />
                              <span className="truncate">{res.title}</span>
                            </a>
                          ))}
                        </div>
                      )}
                    </div>
                  )}
                </div>
              );
            })}
          </nav>
        </aside>

        {/* ── Main Content Area ────────────────────────────────────────── */}
        <main className="flex-1 overflow-y-auto">
          {activeLesson ? (
            <div className="p-6 lg:p-8 max-w-4xl mx-auto space-y-6">

              {/* Lesson Header */}
              <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                  {activeModule && (
                    <p className="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">
                      {activeModule.title}
                    </p>
                  )}
                  <h2 className="text-xl font-bold text-white">{activeLesson.title}</h2>
                  {activeLesson.duration && (
                    <span className="inline-flex items-center gap-1 mt-1 text-xs text-gray-500">
                      <Clock size={12} /> {activeLesson.duration}
                    </span>
                  )}
                </div>
                <button
                  onClick={() => markComplete(activeLesson)}
                  disabled={activeLesson.completed || completing === activeLesson.id}
                  className={`shrink-0 flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 ${
                    activeLesson.completed
                      ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 cursor-default'
                      : completing === activeLesson.id
                      ? 'bg-indigo-700/50 text-indigo-300 cursor-wait'
                      : 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-500/20'
                  }`}
                >
                  {activeLesson.completed ? (
                    <>
                      <CheckCircle size={16} /> Completed
                    </>
                  ) : completing === activeLesson.id ? (
                    <>
                      <div className="w-4 h-4 border-2 border-indigo-300 border-t-transparent rounded-full animate-spin" />
                      Saving…
                    </>
                  ) : (
                    <>
                      <CheckCircle size={16} /> Mark as Complete
                    </>
                  )}
                </button>
              </div>

              {/* Video Embed */}
              {activeLesson.video_url && (
                <div className="rounded-2xl overflow-hidden aspect-video bg-black">
                  <iframe
                    src={activeLesson.video_url}
                    title={activeLesson.title}
                    className="w-full h-full"
                    allowFullScreen
                  />
                </div>
              )}

              {/* Lesson Content */}
              {activeLesson.content && (
                <div
                  className="prose prose-invert prose-sm max-w-none bg-gray-900 border border-gray-800 rounded-2xl p-6 text-gray-300 leading-relaxed"
                  dangerouslySetInnerHTML={{ __html: activeLesson.content }}
                />
              )}

              {/* Lesson Resources */}
              {(activeLesson.resources || []).length > 0 && (
                <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                  <h3 className="text-sm font-semibold text-white mb-4 flex items-center gap-2">
                    <FileText size={16} className="text-indigo-400" />
                    Lesson Resources
                  </h3>
                  <div className="space-y-2">
                    {activeLesson.resources.map((res) => (
                      <a
                        key={res.id}
                        href={res.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex items-center gap-3 p-3 rounded-xl bg-gray-800/50 hover:bg-gray-800 border border-gray-700 hover:border-indigo-500/30 transition-all group"
                      >
                        <div className="p-2 rounded-lg bg-gray-700">
                          <ResourceIcon type={res.type} />
                        </div>
                        <div className="flex-1 min-w-0">
                          <p className="text-sm text-gray-200 group-hover:text-white truncate transition-colors">
                            {res.title}
                          </p>
                          {res.type && (
                            <p className="text-xs text-gray-500 capitalize">{res.type}</p>
                          )}
                        </div>
                        <Download size={14} className="text-gray-500 group-hover:text-indigo-400 transition-colors shrink-0" />
                      </a>
                    ))}
                  </div>
                </div>
              )}

              {/* Navigation: Prev / Next lesson */}
              {(() => {
                const idx = allLessons.findIndex((l) => l.id === activeLesson.id);
                const prev = idx > 0 ? allLessons[idx - 1] : null;
                const next = idx < allLessons.length - 1 ? allLessons[idx + 1] : null;
                return (
                  <div className="flex items-center justify-between pt-2 border-t border-gray-800">
                    <button
                      onClick={() => prev && setActiveLesson(prev)}
                      disabled={!prev}
                      className="flex items-center gap-2 text-sm text-gray-400 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                    >
                      <ArrowLeft size={15} />
                      {prev ? prev.title : 'No previous'}
                    </button>
                    <button
                      onClick={() => next && setActiveLesson(next)}
                      disabled={!next}
                      className="flex items-center gap-2 text-sm text-gray-400 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                    >
                      {next ? next.title : 'No next'}
                      <ChevronRight size={15} />
                    </button>
                  </div>
                );
              })()}
            </div>
          ) : (
            <div className="flex flex-col items-center justify-center h-full py-24 text-gray-600">
              <Lock size={40} className="mb-4 opacity-40" />
              <p className="text-gray-400">Select a lesson from the sidebar to begin.</p>
            </div>
          )}
        </main>
      </div>
    </div>
  );
}
