'use client';

import { useAuth } from '@/contexts/AuthContext';
import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import Link from 'next/link';

interface RoleGuardProps {
    children: React.ReactNode;
    allowedRoles: string[];
    fallback?: React.ReactNode;
}

export default function RoleGuard({ children, allowedRoles, fallback }: RoleGuardProps) {
    const { user, loading, isAuthenticated } = useAuth();
    const router = useRouter();

    useEffect(() => {
        if (!loading && !isAuthenticated) {
            router.push('/login');
        }
    }, [loading, isAuthenticated, router]);

    if (loading) {
        return (
            <div className="flex justify-center items-center min-h-[200px]">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>
        );
    }

    if (!isAuthenticated) {
        return null; // Will redirect in useEffect
    }

    const hasPermission = user?.role && allowedRoles.includes(user.role);

    if (!hasPermission) {
        if (fallback) {
            return <>{fallback}</>;
        }
        
        // Default fallback: Redirect to access denied page
        // We use a useEffect-like approach here to avoid rendering nothing while redirecting
        // But for better UX, we can render a "Access Denied" message immediately if no fallback provided
        // or redirect. Let's redirect.
        router.push('/dashboard/access-denied');
        return null;
    }

    return <>{children}</>;
}
