'use client';

import { HTMLMotionProps } from 'framer-motion';
import { motion } from 'framer-motion';

interface AuthButtonProps extends HTMLMotionProps<"button"> {
    loading?: boolean;
    loadingText?: string;
    children?: React.ReactNode;
}

export default function AuthButton({ 
    children, 
    loading = false, 
    loadingText = 'Cargando...',
    className,
    disabled,
    ...props 
}: AuthButtonProps) {
    return (
        <motion.button
            whileHover={{ scale: disabled || loading ? 1 : 1.01 }}
            whileTap={{ scale: disabled || loading ? 1 : 0.99 }}
            transition={{ duration: 0.2 }}
            disabled={disabled || loading}
            className={`
                w-full py-3 px-6
                bg-gradient-to-r from-indigo-600 to-purple-600
                hover:from-indigo-700 hover:to-purple-700
                text-white font-medium
                rounded-lg
                shadow-lg shadow-indigo-500/30
                disabled:opacity-50 disabled:cursor-not-allowed
                transition-all duration-200
                flex items-center justify-center gap-2
                ${className || ''}
            `}
            {...props}
        >
            {loading && (
                <svg 
                    className="animate-spin h-5 w-5" 
                    xmlns="http://www.w3.org/2000/svg" 
                    fill="none" 
                    viewBox="0 0 24 24"
                >
                    <circle 
                        className="opacity-25" 
                        cx="12" 
                        cy="12" 
                        r="10" 
                        stroke="currentColor" 
                        strokeWidth="4"
                    />
                    <path 
                        className="opacity-75" 
                        fill="currentColor" 
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    />
                </svg>
            )}
            <span>{loading ? loadingText : (children as React.ReactNode)}</span>
        </motion.button>
    );
}
