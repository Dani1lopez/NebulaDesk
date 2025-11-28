'use client';

import React, { Component, ErrorInfo, ReactNode } from 'react';
import { ExclamationTriangleIcon } from '@heroicons/react/24/outline';

interface Props {
    children?: ReactNode;
}

interface State {
    hasError: boolean;
    error: Error | null;
}

class ErrorBoundary extends Component<Props, State> {
    public state: State = {
        hasError: false,
        error: null
    };

    public static getDerivedStateFromError(error: Error): State {
        return { hasError: true, error };
    }

    public componentDidCatch(error: Error, errorInfo: ErrorInfo) {
        console.error('Uncaught error:', error, errorInfo);
    }

    public render() {
        if (this.state.hasError) {
            return (
                <div className="min-h-[60vh] flex flex-col items-center justify-center p-4 text-center">
                    <div className="bg-red-50 dark:bg-red-900/20 p-6 rounded-full mb-6">
                        <ExclamationTriangleIcon className="w-16 h-16 text-red-600 dark:text-red-400" />
                    </div>
                    
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                        Algo salió mal
                    </h1>
                    
                    <p className="text-lg text-gray-600 dark:text-gray-300 max-w-md mb-8">
                        Ha ocurrido un error inesperado. Hemos registrado el problema y estamos trabajando para solucionarlo.
                    </p>
                    
                    <div className="flex gap-4">
                        <button
                            onClick={() => window.location.reload()}
                            className="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors shadow-sm"
                        >
                            Recargar página
                        </button>
                        
                        <button
                            onClick={() => window.location.href = '/dashboard'}
                            className="px-6 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm"
                        >
                            Volver al Dashboard
                        </button>
                    </div>

                    {process.env.NODE_ENV === 'development' && this.state.error && (
                        <div className="mt-8 p-4 bg-gray-100 dark:bg-gray-900 rounded-lg text-left overflow-auto max-w-2xl w-full max-h-64">
                            <p className="font-mono text-sm text-red-600 dark:text-red-400 mb-2">
                                {this.state.error.toString()}
                            </p>
                        </div>
                    )}
                </div>
            );
        }

        return this.props.children;
    }
}

export default ErrorBoundary;
