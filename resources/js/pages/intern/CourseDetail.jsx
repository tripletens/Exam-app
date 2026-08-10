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
  Video,
  Sparkles,
  HelpCircle,
  CheckCircle2,
  XCircle,
  RotateCcw,
} from 'lucide-react';
import { toast } from 'react-toastify';
import api from '../../api';

// ─── Helper: YouTube Embed URL ───────────────────────────────────────────────
function getYouTubeEmbedUrl(url) {
  if (!url) return null;
  const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
  const match = url.match(regExp);
  return match && match[2].length === 11 ? `https://www.youtube.com/embed/${match[2]}` : null;
}

// ─── Helper: Simple Markdown to HTML Renderer ─────────────────────────────────
function renderMarkdown(text) {
  if (!text) return '';
  let html = text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');

  html = html.replace(/^### (.*$)/gim, '<h3 class="text-base font-bold text-indigo-400 mt-6 mb-2 flex items-center gap-2">$1</h3>');
  html = html.replace(/^## (.*$)/gim, '<h2 class="text-lg font-bold text-white mt-8 mb-3 pb-1 border-b border-gray-800">$1</h2>');
  html = html.replace(/^# (.*$)/gim, '<h1 class="text-xl font-extrabold text-white mt-4 mb-4">$1</h1>');

  html = html.replace(/```([a-z]*)\n([\s\S]*?)```/gim, (match, lang, code) => {
    return `<div class="my-4 rounded-xl overflow-hidden border border-gray-800 bg-gray-950 font-mono text-xs">
      <div class="px-4 py-1.5 bg-gray-900 border-b border-gray-800 text-gray-500 uppercase tracking-wider font-semibold text-[10px] flex justify-between">
        <span>${lang || 'code'}</span>
        <span>Lecture Code Snippet</span>
      </div>
      <pre class="p-4 text-emerald-400 overflow-x-auto leading-relaxed"><code>${code.trim()}</code></pre>
    </div>`;
  });

  html = html.replace(/`([^`]+)`/g, '<code class="bg-gray-800 text-indigo-300 px-1.5 py-0.5 rounded font-mono text-xs">$1</code>');
  html = html.replace(/\*\*([^*]+)\*\*/g, '<strong class="text-white font-semibold">$1</strong>');
  html = html.replace(/\*([^*]+)\*/g, '<em class="text-gray-300">$1</em>');
  html = html.replace(/^\- (.*$)/gim, '<li class="ml-4 list-disc text-gray-300 my-1">$1</li>');

  html = html.split('\n\n').map(p => {
    if (p.startsWith('<h') || p.startsWith('<div') || p.startsWith('<li')) return p;
    return `<p class="mb-4 text-gray-300 leading-relaxed text-sm">${p}</p>`;
  }).join('');

  return html;
}

// ─── Interactive Lesson Quiz Component ────────────────────────────────────────
const LessonQuizWidget = ({ quizData = [] }) => {
  const [userAnswers, setUserAnswers] = useState({});
  const [submitted, setSubmitted] = useState(false);

  useEffect(() => {
    setUserAnswers({});
    setSubmitted(false);
  }, [quizData]);

  if (!quizData || quizData.length === 0) return null;

  const handleSelect = (qIdx, optIdx) => {
    if (submitted) return;
    setUserAnswers(prev => ({ ...prev, [qIdx]: optIdx }));
  };

  const score = Object.entries(userAnswers).filter(([qIdx, optIdx]) => {
    return quizData[qIdx]?.correct === optIdx;
  }).length;

  return (
    <div className="card border-indigo-500/30 bg-gray-900/90 space-y-6">
      <div className="flex items-center justify-between border-b border-gray-800 pb-3">
        <div className="flex items-center gap-2">
          <div className="p-2 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
            <HelpCircle size={18} />
          </div>
          <div>
            <h3 className="text-base font-bold text-white">Lesson Checkpoint Quiz</h3>
            <p className="text-xs text-gray-400">Test your understanding of this lesson before continuing</p>
          </div>
        </div>
        {submitted && (
          <span className={`badge text-xs px-3 py-1 ${score === quizData.length ? 'badge-green' : 'badge-yellow'}`}>
            {score} / {quizData.length} Correct
          </span>
        )}
      </div>

      <div className="space-y-6">
        {quizData.map((q, qIdx) => {
          const selected = userAnswers[qIdx];
          const isCorrect = selected === q.correct;

          return (
            <div key={qIdx} className="p-4 rounded-xl bg-gray-950/60 border border-gray-800 space-y-3">
              <p className="text-sm font-semibold text-white">
                <span className="text-indigo-400 mr-2">Q{qIdx + 1}.</span>
                {q.question}
              </p>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                {q.options.map((opt, optIdx) => {
                  let btnStyle = 'bg-gray-900 border-gray-800 text-gray-300 hover:border-gray-700';

                  if (selected === optIdx) {
                    btnStyle = 'bg-indigo-600/20 border-indigo-500 text-indigo-300 font-semibold';
                  }

                  if (submitted) {
                    if (optIdx === q.correct) {
                      btnStyle = 'bg-emerald-500/20 border-emerald-500 text-emerald-300 font-semibold';
                    } else if (selected === optIdx && !isCorrect) {
                      btnStyle = 'bg-red-500/20 border-red-500 text-red-300';
                    }
                  }

                  return (
                    <button
                      key={optIdx}
                      onClick={() => handleSelect(qIdx, optIdx)}
                      className={`flex items-center gap-3 p-3 rounded-xl border text-left text-xs transition-all ${btnStyle}`}
                    >
                      <span className="w-5 h-5 rounded-full border flex items-center justify-center text-[10px] font-bold shrink-0">
                        {String.fromCharCode(65 + optIdx)}
                      </span>
                      <span className="flex-1">{opt}</span>
                    </button>
                  );
                })}
              </div>

              {submitted && q.explanation && (
                <div className="mt-3 p-3 rounded-lg bg-gray-900 border border-gray-800 text-xs text-gray-300">
                  <span className="font-semibold text-indigo-400">Explanation: </span>
                  {q.explanation}
                </div>
              )}
            </div>
          );
        })}
      </div>

      <div className="flex items-center justify-between pt-2">
        {!submitted ? (
          <button
            onClick={() => setSubmitted(true)}
            disabled={Object.keys(userAnswers).length < quizData.length}
            className="btn-primary text-xs disabled:opacity-40"
          >
            Check Answers
          </button>
        ) : (
          <button
            onClick={() => { setSubmitted(false); setUserAnswers({}); }}
            className="btn-secondary text-xs"
          >
            <RotateCcw size={14} /> Retry Quiz
          </button>
        )}
      </div>
    </div>
  );
};

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
    youtube: <Video size={14} className="text-red-500" />,
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
  const [completing, setCompleting] = useState(null);

  useEffect(() => {
    const fetchCourse = async () => {
      try {
        const res = await api.get(`/courses/${id}`);
        const data = res.data?.data || res.data;
        setCourse(data);

        if (data?.modules?.length) {
          const firstModule = data.modules[0];
          setOpenModules({ [firstModule.id]: true });

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

  const toggleModule = useCallback((moduleId) => {
    setOpenModules((prev) => ({ ...prev, [moduleId]: !prev[moduleId] }));
  }, []);

  const markComplete = useCallback(
    async (lesson) => {
      if (lesson.completed || completing === lesson.id) return;
      setCompleting(lesson.id);
      try {
        await api.post(`/lessons/${lesson.id}/complete`);
        setCourse((prev) => {
          if (!prev) return prev;
          const updatedModules = prev.modules.map((mod) => ({
            ...mod,
            lessons: (mod.lessons || []).map((l) =>
              l.id === lesson.id ? { ...l, completed: true } : l
            ),
          }));
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

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-950 flex">
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

  const moduleResources = activeModule?.resources || [];
  const videoResource = moduleResources.find(r => r.type?.value === 'youtube' || r.type === 'youtube');
  const embedUrl = videoResource ? getYouTubeEmbedUrl(videoResource.url) : null;

  return (
    <div className="min-h-screen bg-gray-950 flex flex-col">
      {/* Top Header Bar */}
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
                  <span className="inline-flex items-center gap-1 text-xs text-indigo-400 font-medium">
                    <Layers size={11} /> {course.category}
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
        {/* Left Sidebar – Modules & Lessons */}
        <aside className="w-80 shrink-0 bg-gray-900 border-r border-gray-800 overflow-y-auto hidden lg:flex flex-col">
          <div className="p-4 border-b border-gray-800">
            <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
              Modules & Lessons
            </p>
          </div>
          <nav className="flex-1 p-3 space-y-1">
            {modules.map((mod, modIdx) => {
              const isOpen = !!openModules[mod.id];
              const modLessons = mod.lessons || [];
              const modCompleted = modLessons.filter((l) => l.completed).length;

              return (
                <div key={mod.id}>
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
                          </button>
                        );
                      })}
                    </div>
                  )}
                </div>
              );
            })}
          </nav>
        </aside>

        {/* Main Content Area */}
        <main className="flex-1 overflow-y-auto">
          {activeLesson ? (
            <div className="p-6 lg:p-8 max-w-4xl mx-auto space-y-6 animate-fade-in">
              {/* Lesson Header */}
              <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-800">
                <div>
                  {activeModule && (
                    <p className="text-xs text-indigo-400 uppercase tracking-wider font-semibold mb-1">
                      {activeModule.title}
                    </p>
                  )}
                  <h2 className="text-2xl font-extrabold text-white">{activeLesson.title}</h2>
                  {activeLesson.duration_minutes && (
                    <span className="inline-flex items-center gap-1 mt-1.5 text-xs text-gray-500">
                      <Clock size={12} /> {activeLesson.duration_minutes} mins estimated
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
                      <CheckCircle size={16} /> Lesson Completed
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

              {/* YouTube Video Player Embed */}
              {embedUrl && (
                <div className="space-y-2">
                  <div className="flex items-center justify-between text-xs text-gray-400 px-1">
                    <span className="flex items-center gap-1.5 font-semibold text-red-400">
                      <Video size={16} /> Video Lecture
                    </span>
                    <span>HD 1080p</span>
                  </div>
                  <div className="rounded-2xl overflow-hidden aspect-video bg-black shadow-2xl border border-gray-800">
                    <iframe
                      src={embedUrl}
                      title={activeLesson.title}
                      className="w-full h-full"
                      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                      allowFullScreen
                    />
                  </div>
                </div>
              )}

              {/* Lecture Notes (Markdown Formatted) */}
              {activeLesson.content && (
                <div className="card space-y-3 border-gray-800 bg-gray-900/90">
                  <div className="flex items-center gap-2 text-xs font-semibold text-indigo-400 uppercase tracking-wider border-b border-gray-800 pb-3">
                    <Sparkles size={14} /> Lecture Notes & Curriculum Study Guide
                  </div>
                  <div
                    className="prose prose-invert max-w-none text-gray-300 leading-relaxed text-sm"
                    dangerouslySetInnerHTML={{ __html: renderMarkdown(activeLesson.content) }}
                  />
                </div>
              )}

              {/* Interactive Lesson Checkpoint Quiz */}
              {activeLesson.quiz_data && activeLesson.quiz_data.length > 0 && (
                <LessonQuizWidget quizData={activeLesson.quiz_data} />
              )}

              {/* Module Learning Resources */}
              {moduleResources.length > 0 && (
                <div className="card border-gray-800 bg-gray-900/60 space-y-4">
                  <h3 className="text-sm font-semibold text-white flex items-center gap-2">
                    <FileText size={16} className="text-indigo-400" />
                    Recommended Study Resources
                  </h3>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {moduleResources.map((res) => (
                      <a
                        key={res.id}
                        href={res.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex items-center gap-3 p-3.5 rounded-xl bg-gray-800/60 hover:bg-gray-800 border border-gray-700/60 hover:border-indigo-500/40 transition-all group"
                      >
                        <div className="p-2.5 rounded-xl bg-gray-900 border border-gray-700/80">
                          <ResourceIcon type={res.type?.value || res.type} />
                        </div>
                        <div className="flex-1 min-w-0">
                          <p className="text-xs font-semibold text-gray-200 group-hover:text-white truncate transition-colors">
                            {res.title}
                          </p>
                          <p className="text-[11px] text-gray-500 capitalize mt-0.5">
                            {res.type?.value || res.type} {res.author ? `• ${res.author}` : ''}
                          </p>
                        </div>
                        <ExternalLink size={14} className="text-gray-500 group-hover:text-indigo-400 transition-colors shrink-0" />
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
                  <div className="flex items-center justify-between pt-6 border-t border-gray-800">
                    <button
                      onClick={() => prev && setActiveLesson(prev)}
                      disabled={!prev}
                      className="btn-secondary text-xs disabled:opacity-30 disabled:cursor-not-allowed"
                    >
                      <ArrowLeft size={14} /> Previous Lesson
                    </button>
                    <button
                      onClick={() => next && setActiveLesson(next)}
                      disabled={!next}
                      className="btn-primary text-xs disabled:opacity-30 disabled:cursor-not-allowed"
                    >
                      Next Lesson <ChevronRight size={14} />
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
