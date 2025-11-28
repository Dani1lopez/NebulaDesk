'use client';

import { forwardRef } from 'react';
import { HTMLMotionProps } from 'framer-motion';
import { motion } from 'framer-motion';

interface AuthInputProps extends HTMLMotionProps<"input"> {
    label: string;
    error?: string;
    icon?: React.ReactNode;
}

const AuthInput = forwardRef<HTMLInputElement, AuthInputProps>(
    ({ label, error, icon, className, ...props }, ref) => {
        return (
            <div className="w-full">
                <label 
                    htmlFor={props.id} 
                    className="block text-sm font-medium text-slate-300 mb-2"
                >
                    {label}
                </label>
                <div className="relative">
                    {icon && (
                        <div className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            {icon}
                        </div>
                    )}
                    <motion.input
                        ref={ref}
                        whileFocus={{ scale: 1.01 }}
                        transition={{ duration: 0.2 }}
                        className={`
                            w-full px-4 py-3 
                            ${icon ? 'pl-11' : ''}
                            bg-slate-800/50 
                            border border-slate-700 
                            rounded-lg 
                            text-white 
                            placeholder-slate-500
                            focus:outline-none 
                            focus:border-indigo-500 
                            focus:ring-2 
                            focus:ring-indigo-500/20
                            disabled:opacity-50 
                            disabled:cursor-not-allowed
                            transition-all duration-200
                            ${error ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''}
                            ${className || ''}
                        `}
                        aria-invalid={error ? 'true' : 'false'}
                        {...props}
                    />
                </div>
                {error && (
                    <motion.p
                        initial={{ opacity: 0, y: -10 }}
                        animate={{ opacity: 1, y: 0 }}
                        className="mt-1 text-sm text-red-400"
                    >
                        {error}
                    </motion.p>
                )}
            </div>
        );
    }
);

AuthInput.displayName = 'AuthInput';

export default AuthInput;
