import React, { useEffect, useState } from 'react';
import { certificateApi } from '../../api';
import { toast } from 'react-toastify';
import { Loader2, Award, Download, Calendar, ShieldCheck, FileCheck2, ExternalLink } from 'lucide-react';

export default function MyCertificates() {
    const [certificates, setCertificates] = useState([]);
    const [loading, setLoading] = useState(true);
    const [downloadingId, setDownloadingId] = useState(null);

    useEffect(() => {
        certificateApi.list()
            .then(res => setCertificates(res.data.data || []))
            .catch(() => toast.error('Failed to load certificates'))
            .finally(() => setLoading(false));
    }, []);

    const handleDownload = async (cert) => {
        setDownloadingId(cert.id);
        try {
            const res = await certificateApi.download(cert.id);
            const blob = new Blob([res.data], { type: 'application/pdf' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${cert.certificate_number}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            toast.success('Certificate downloaded successfully!');
        } catch {
            toast.error('Download failed. Please try again.');
        } finally {
            setDownloadingId(null);
        }
    };

    return (
        <div className="space-y-6">
            <div className="bg-gradient-to-r from-amber-950/40 via-gray-900 to-indigo-950/40 border border-amber-500/20 rounded-2xl p-6 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div className="flex items-center gap-4">
                    <div className="p-3.5 bg-amber-500/10 border border-amber-500/30 rounded-2xl text-amber-400">
                        <Award size={32} />
                    </div>
                    <div>
                        <h1 className="text-xl font-bold text-white">Official Certificates of Completion</h1>
                        <p className="text-xs text-gray-400 mt-1">Verified industry credentials issued by Lythub Technologies</p>
                    </div>
                </div>
                <span className="badge badge-green flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold">
                    <ShieldCheck size={14} /> Official Issuer Seal
                </span>
            </div>

            {loading ? (
                <div className="flex justify-center py-20">
                    <Loader2 className="animate-spin text-amber-500" size={32} />
                </div>
            ) : certificates.length === 0 ? (
                <div className="bg-gray-900 border border-gray-800 rounded-2xl p-16 text-center space-y-3">
                    <Award size={48} className="mx-auto text-amber-500/40" />
                    <h3 className="text-lg font-bold text-white">No Certificates Earned Yet</h3>
                    <p className="text-gray-400 text-xs max-w-sm mx-auto">
                        Complete your assigned training courses and pass the module certification exams to earn your official certificates.
                    </p>
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {certificates.map(cert => (
                        <div
                            key={cert.id}
                            className="bg-gray-900 border border-amber-500/30 rounded-2xl p-6 shadow-xl relative flex flex-col justify-between overflow-hidden group hover:border-amber-500/60 transition-all"
                        >
                            {/* Gold foil corner accent */}
                            <div className="absolute top-0 right-0 w-24 h-24 bg-amber-500/10 rounded-bl-full pointer-events-none transition-all group-hover:bg-amber-500/20" />

                            <div>
                                <div className="flex items-center justify-between gap-3 mb-4">
                                    <div className="flex items-center gap-2">
                                        <FileCheck2 className="text-amber-400" size={20} />
                                        <span className="text-xs font-bold text-amber-400 uppercase tracking-widest">Certificate of Completion</span>
                                    </div>
                                    <span className="badge badge-green text-[10px] uppercase tracking-wider flex items-center gap-1">
                                        <ShieldCheck size={11} /> Verified
                                    </span>
                                </div>

                                <h3 className="text-lg font-extrabold text-white mb-1.5 leading-snug">
                                    {cert.course?.title || cert.learning_path?.title || 'Software Engineering Certification'}
                                </h3>

                                <div className="text-xs font-mono text-gray-400 mb-4 bg-gray-950/60 px-3 py-1.5 rounded-lg border border-gray-800/80 w-fit">
                                    ID: {cert.certificate_number}
                                </div>
                            </div>

                            <div className="pt-4 border-t border-gray-800 flex items-center justify-between gap-3">
                                <div className="text-xs text-gray-500 flex items-center gap-1.5">
                                    <Calendar size={13} className="text-gray-400" />
                                    <span>Issued: {new Date(cert.issued_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
                                </div>

                                <button
                                    onClick={() => handleDownload(cert)}
                                    disabled={downloadingId === cert.id}
                                    className="px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-gray-950 font-bold text-xs rounded-xl shadow-lg shadow-amber-500/20 flex items-center gap-2 transition-all disabled:opacity-50"
                                >
                                    {downloadingId === cert.id ? (
                                        <>
                                            <Loader2 size={14} className="animate-spin" />
                                            Generating PDF...
                                        </>
                                    ) : (
                                        <>
                                            <Download size={14} />
                                            Download PDF
                                        </>
                                    )}
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
