'use client';

import { useState } from 'react';
import { motion } from 'framer-motion';
import { useAuth } from '@/contexts/AuthContext';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import toast from 'react-hot-toast';
import { EnvelopeIcon, LockClosedIcon, UserIcon } from '@heroicons/react/24/outline';
import AuthInput from '@/components/auth/AuthInput';
import AuthButton from '@/components/auth/AuthButton';
import BrandingPanel from '@/components/auth/BrandingPanel';
import AuthBackground from '@/components/auth/AuthBackground';

export default function RegisterPage() {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [errors, setErrors] = useState<{ 
        name?: string;
        email?: string; 
        password?: string; 
        confirmPassword?: string;
        general?: string;
    }>({});
    const [loading, setLoading] = useState(false);
    const { register } = useAuth();
    const router = useRouter();

    const validateForm = () => {
        const newErrors: { 
            name?: string;
            email?: string; 
            password?: string; 
            confirmPassword?: string;
        } = {};

        if (!name.trim()) {
            newErrors.name = 'El nombre es requerido';
        }

        if (!email) {
            newErrors.email = 'El email es requerido';
        } else if (!/\S+@\S+\.\S+/.test(email)) {
            newErrors.email = 'El email no es válido';
        }

        if (!password) {
            newErrors.password = 'La contraseña es requerida';
        } else if (password.length < 8) {
            newErrors.password = 'La contraseña debe tener al menos 8 caracteres';
        }

        if (!confirmPassword) {
            newErrors.confirmPassword = 'Confirma tu contraseña';
        } else if (password !== confirmPassword) {
            newErrors.confirmPassword = 'Las contraseñas no coinciden';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setErrors({});

        if (!validateForm()) {
            return;
        }

        setLoading(true);

        try {
            await register(name, email, password);
        } catch (err: any) {
            const errorMessage = err.message || 'Error al crear la cuenta';
            
            // Check if registration is disabled
            if (errorMessage.includes('registro público está deshabilitado') || 
                errorMessage.includes('registration_disabled')) {
                // Show toast and redirect to login
                toast.error(errorMessage, { duration: 5000 });
                setTimeout(() => {
                    router.push('/login');
                }, 2000);
            } else {
                setErrors({ general: errorMessage });
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 relative overflow-hidden">
            <AuthBackground />

            {/* Main Container */}
            <div className="relative w-full max-w-6xl grid lg:grid-cols-2 gap-8 items-center">
                {/* Branding Panel - Hidden on mobile */}
                <BrandingPanel />

                {/* Register Form */}
                <motion.div
                    initial={{ opacity: 0, x: 20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ duration: 0.5, ease: 'easeOut' }}
                    className="bg-slate-900/80 backdrop-blur-xl p-8 md:p-12 rounded-2xl shadow-2xl border border-slate-700/50"
                >
                    {/* Mobile Logo */}
                    <div className="lg:hidden mb-8 text-center">
                        <h1 className="text-3xl font-bold bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent mb-2">
                            NebulaDesk
                        </h1>
                        <p className="text-sm text-slate-400">Centro de soporte interno</p>
                    </div>

                    <div className="mb-8">
                        <h2 className="text-3xl font-bold text-white mb-2">
                            Crea tu cuenta
                        </h2>
                        <p className="text-slate-400">
                            Únete a la plataforma de soporte
                        </p>
                    </div>

                    {errors.general && (
                        <motion.div
                            initial={{ opacity: 0, y: -10 }}
                            animate={{ opacity: 1, y: 0 }}
                            className="mb-6 p-4 rounded-lg border bg-red-500/10 border-red-500/50 text-red-300"
                        >
                            <p className="text-sm">{errors.general}</p>
                        </motion.div>
                    )}

                    <form onSubmit={handleSubmit} className="space-y-5">
                        <AuthInput
                            id="name"
                            type="text"
                            label="Nombre completo"
                            placeholder="Tu nombre"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            error={errors.name}
                            disabled={loading}
                            icon={<UserIcon className="w-5 h-5" />}
                        />

                        <AuthInput
                            id="email"
                            type="email"
                            label="Email"
                            placeholder="tu@email.com"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            error={errors.email}
                            disabled={loading}
                            icon={<EnvelopeIcon className="w-5 h-5" />}
                        />

                        <AuthInput
                            id="password"
                            type="password"
                            label="Contraseña"
                            placeholder="Mínimo 8 caracteres"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            error={errors.password}
                            disabled={loading}
                            icon={<LockClosedIcon className="w-5 h-5" />}
                        />

                        <AuthInput
                            id="confirmPassword"
                            type="password"
                            label="Confirmar contraseña"
                            placeholder="Repite tu contraseña"
                            value={confirmPassword}
                            onChange={(e) => setConfirmPassword(e.target.value)}
                            error={errors.confirmPassword}
                            disabled={loading}
                            icon={<LockClosedIcon className="w-5 h-5" />}
                        />

                        <AuthButton
                            type="submit"
                            loading={loading}
                            loadingText="Creando cuenta..."
                        >
                            Crear cuenta
                        </AuthButton>
                    </form>

                    <div className="mt-8 text-center">
                        <p className="text-sm text-slate-400">
                            ¿Ya tienes cuenta?{' '}
                            <Link 
                                href="/login" 
                                className="text-indigo-400 hover:text-indigo-300 font-medium transition-colors"
                            >
                                Inicia sesión aquí
                            </Link>
                        </p>
                    </div>
                </motion.div>
            </div>
        </div>
    );
}
