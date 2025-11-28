'use client';

import { useState } from 'react';
import api from '@/lib/axios';
import { useAuth } from '@/contexts/AuthContext';
import { EnvelopeIcon, CheckCircleIcon } from '@heroicons/react/24/outline';

export default function VerifyEmailNoticePage() {
    const { user, isEmailVerified } = useAuth();
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState<{ text: string; type: 'success' | 'error' } | null>(null);

    const handleResend = async () => {
        setLoading(true);
        setMessage(null);

        try {
            await api.post('/api/email/resend');
            setMessage({
                text: 'Se ha enviado un nuevo enlace de verificación a tu correo.',
                type: 'success'
            });
        } catch (error: any) {
            if (error.response?.status === 429) {
                setMessage({
                    text: 'Por favor espera unos minutos antes de solicitar otro correo.',
                    type: 'error'
                });
            } else {
                setMessage({
                    text: 'Ocurrió un error al enviar el correo. Inténtalo de nuevo más tarde.',
                    type: 'error'
                });
            }
        } finally {
            setLoading(false);
        }
    };

    if (isEmailVerified()) {
        return (
            <div className="min-h-[60vh] flex flex-col items-center justify-center p-4 text-center">
                <div className="bg-green-50 dark:bg-green-900/20 p-6 rounded-full mb-6">
                    <CheckCircleIcon className="w-16 h-16 text-green-600 dark:text-green-400" />
                </div>
                <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    ¡Email Verificado!
                </h1>
                <p className="text-lg text-gray-600 dark:text-gray-300 max-w-md">
                    Tu dirección de correo electrónico ya ha sido verificada. Puedes acceder a todas las funcionalidades.
                </p>
            </div>
        );
    }

    return (
        <div className="min-h-[60vh] flex flex-col items-center justify-center p-4 text-center">
            <div className="bg-indigo-50 dark:bg-indigo-900/20 p-6 rounded-full mb-6">
                <EnvelopeIcon className="w-16 h-16 text-indigo-600 dark:text-indigo-400" />
            </div>
            
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                Verifica tu correo electrónico
            </h1>
            
            <p className="text-lg text-gray-600 dark:text-gray-300 max-w-md mb-8">
                Para acceder a esta sección, necesitas verificar tu dirección de correo electrónico ({user?.email}).
                Por favor revisa tu bandeja de entrada.
            </p>
            
            <div className="space-y-4">
                <button 
                    onClick={handleResend}
                    disabled={loading}
                    className={`px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors shadow-sm flex items-center justify-center mx-auto ${loading ? 'opacity-70 cursor-not-allowed' : ''}`}
                >
                    {loading ? (
                        <>
                            <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                            Enviando...
                        </>
                    ) : (
                        'Reenviar correo de verificación'
                    )}
                </button>

                {message && (
                    <div className={`p-4 rounded-md max-w-md mx-auto ${message.type === 'success' ? 'bg-green-50 text-green-800 dark:bg-green-900/30 dark:text-green-200' : 'bg-red-50 text-red-800 dark:bg-red-900/30 dark:text-red-200'}`}>
                        {message.text}
                    </div>
                )}
            </div>
        </div>
    );
}
