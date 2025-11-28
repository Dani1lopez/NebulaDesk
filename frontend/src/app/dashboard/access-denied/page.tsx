'use client';

import Link from 'next/link';
import { ShieldExclamationIcon } from '@heroicons/react/24/outline';

export default function AccessDeniedPage() {
    return (
        <div className="min-h-[60vh] flex flex-col items-center justify-center p-4 text-center">
            <div className="bg-red-50 dark:bg-red-900/20 p-6 rounded-full mb-6">
                <ShieldExclamationIcon className="w-16 h-16 text-red-600 dark:text-red-400" />
            </div>
            
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                Acceso Denegado
            </h1>
            
            <p className="text-lg text-gray-600 dark:text-gray-300 max-w-md mb-8">
                No tienes los permisos necesarios para acceder a esta sección. 
                Si crees que esto es un error, contacta a tu administrador.
            </p>
            
            <Link 
                href="/dashboard"
                className="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors shadow-sm"
            >
                Volver al Dashboard
            </Link>
        </div>
    );
}
