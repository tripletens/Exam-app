import React, { useEffect, useState } from 'react';
import { certificateApi } from '../../api';
import { toast } from 'react-toastify';
import { Loader2, Award, Download, Calendar, ShieldCheck } from 'lucide-react';

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
            toast.success('Certificate downloaded!');
        } catch {
            toast.error('Download failed');
        } finally {
            setDownloadingId(null);
        }
    };

    return (
        <div className="space-y-6 animate-fade-in">
            <div className="section-header">
                <div>
                    <h1 className="page-title">My Certificates</h1>
                    <p className="page-subtitle">Official certificates issued by Lythub Technologies</p>
                </div>
            </div>

            {loading ? (
                <div className="flex justify-center py-16">
                    <Loader2 className="animate-spin text-indigo-500" size={32} />
                </div>
            ) : certificates.length === 0 ? (
                <div className="card text-center py-16">
                    <Award size={48} className="mx-auto mb-3 text-gray-600" />
                    <h3 className="text-lg font-semibold text-gray-300">No Certificates Earned Yet</h3>
                    <p className="text-sm text-gray-500 max-w-sm mx-auto mt-1">
                        Complete your assigned training courses and assessments to earn your official certificates.
                    </p>
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {certificates.map(cert => (
                        <div key={cert.id} className="card relative border-indigo-500/20 hover:border-indigo-500/40 transition-all">
                            <div className="flex items-start justify-between gap-4 mb-4">
                                <div className="p-3 bg-indigo-500/10 border border-indigo-500/20 rounded-xl text-indigo-400">
                                    <Award size={28} />
                                </div>
                                <span className="badge badge-green flex items-center gap-1">
                                    <ShieldCheck size={12} /> Verified
                                </span>
                            </div>

                            <h3 className="text-lg font-bold text-white mb-1">
                                {cert.course?.title || cert.learning_path?.title || 'Training Certification'}
                            </h3>
                            <p className="text-xs text-gray-400 mb-4 font-mono">No: {cert.certificate_number}</p>

                            <div className="flex items-center justify-between pt-4 border-t border-gray-800 text-xs text-gray-500">
                                <div className="flex items-center gap-1">
                                    <Calendar size={13} />
                                    <span>Issued: {new Date(cert.issued_at).toLocaleDateString()}</span>
                                </div>

                                <button
                                    onClick={() => handleDownload(cert)}
                                    disabled={downloadingId === cert.id}
                                    className="btn-primary text-xs py-1.5 px-3"
                                >
                                    {downloadingId === cert.id ? (
                                        <Loader2 size={13} className="animate-spin" />
                                    ) : (
                                        <Download size={13} />
                                    )}
                                    Download PDF
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
