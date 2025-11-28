'use client';

import { motion } from 'framer-motion';
import { ShieldCheckIcon, ClockIcon, UserGroupIcon } from '@heroicons/react/24/outline';

export default function BrandingPanel() {
    const features = [
        {
            icon: <UserGroupIcon className="w-6 h-6" />,
            title: 'Gestión de tickets multi-organización',
            description: 'Administra múltiples organizaciones desde un único panel'
        },
        {
            icon: <ClockIcon className="w-6 h-6" />,
            title: 'SLA en tiempo real',
            description: 'Monitoreo y alertas de acuerdos de nivel de servicio'
        },
        {
            icon: <ShieldCheckIcon className="w-6 h-6" />,
            title: 'Seguridad y control de accesos (RBAC)',
            description: 'Sistema de permisos basado en roles'
        }
    ];

    return (
        <motion.div
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.5, ease: 'easeOut' }}
            className="hidden lg:flex flex-col justify-center p-12 bg-white/5 backdrop-blur-xl rounded-2xl border border-white/10"
        >
            {/* Logo y Título */}
            <div className="mb-12">
                <motion.h1 
                    className="text-5xl font-bold bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent mb-4"
                    initial={{ opacity: 0, y: -10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.2, duration: 0.5 }}
                >
                    NebulaDesk
                </motion.h1>
                <motion.p 
                    className="text-xl text-slate-300"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ delay: 0.3, duration: 0.5 }}
                >
                    Centro de soporte interno
                </motion.p>
                <motion.p 
                    className="text-sm text-slate-400 mt-2"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ delay: 0.4, duration: 0.5 }}
                >
                    Tu panel de control para tickets y SLAs
                </motion.p>
            </div>

            {/* Features */}
            <div className="space-y-6">
                {features.map((feature, index) => (
                    <motion.div
                        key={index}
                        initial={{ opacity: 0, x: -20 }}
                        animate={{ opacity: 1, x: 0 }}
                        transition={{ delay: 0.5 + index * 0.1, duration: 0.4 }}
                        whileHover={{ x: 5, transition: { duration: 0.2 } }}
                        className="flex items-start gap-4 p-4 rounded-lg bg-white/5 hover:bg-white/10 transition-all duration-200 border border-transparent hover:border-indigo-500/30"
                    >
                        <div className="flex-shrink-0 w-12 h-12 rounded-lg bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center text-indigo-400 border border-indigo-500/30">
                            {feature.icon}
                        </div>
                        <div>
                            <h3 className="text-white font-medium mb-1">
                                {feature.title}
                            </h3>
                            <p className="text-sm text-slate-400">
                                {feature.description}
                            </p>
                        </div>
                    </motion.div>
                ))}
            </div>

            {/* Decorative Element */}
            <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ delay: 1, duration: 1 }}
                className="mt-12 pt-8 border-t border-white/10"
            >
                <p className="text-xs text-slate-500 text-center">
                    Plataforma empresarial de gestión de tickets
                </p>
            </motion.div>
        </motion.div>
    );
}
