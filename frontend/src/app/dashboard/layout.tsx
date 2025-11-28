'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import clsx from 'clsx';
import { useAuth } from '@/contexts/AuthContext';
import {
    HomeIcon,
    TicketIcon,
    UsersIcon,
    ClipboardDocumentListIcon,
    PlusCircleIcon,
    UserCircleIcon,
    ChartBarIcon,
} from '@heroicons/react/24/outline';
import ErrorBoundary from '@/components/ErrorBoundary';

export default function DashboardLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    const { user, logout, isAdmin, isOwner } = useAuth();
    const pathname = usePathname();

    const navigation = [
        { name: 'Overview', href: '/dashboard', icon: HomeIcon },
        { name: 'Tickets', href: '/dashboard/tickets', icon: TicketIcon },
        // Show Organizations to admin and owner
        ...((isAdmin() || isOwner()) ? [
            { name: 'Organizations', href: '/dashboard/organizations', icon: ClipboardDocumentListIcon },
        ] : []),
        // Only show Users and Create Organization to admins
        ...(isAdmin() ? [
            { name: 'Users', href: '/dashboard/users', icon: UsersIcon },
        ] : []),
        { name: 'SLA Dashboard', href: '/dashboard/sla', icon: ChartBarIcon },
        { name: 'Audit Logs', href: '/dashboard/audit-logs', icon: ClipboardDocumentListIcon },
        { name: 'Profile', href: '/dashboard/profile', icon: UserCircleIcon },
    ];

    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900 flex">
            {/* Sidebar */}
            <aside className="w-64 bg-white dark:bg-gray-800 shadow-md hidden md:block">
                <div className="flex flex-col h-screen">
                    <div className="p-6">
                        <h1 className="text-2xl font-bold text-indigo-600 dark:text-indigo-400">NebulaDesk</h1>
                    </div>
                    <nav className="mt-6 flex-1 overflow-y-auto">
                        {navigation.map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={clsx(
                                    'flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-gray-700 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors',
                                    pathname === item.href && 'bg-indigo-50 dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 border-r-4 border-indigo-600'
                                )}
                            >
                                {item.name === 'Profile' && user?.avatar ? (
                                    <img 
                                        src={`http://localhost:8000${user.avatar}`} 
                                        alt="Profile" 
                                        className="w-5 h-5 mr-3 rounded-full object-cover"
                                    />
                                ) : (
                                    <item.icon className="w-5 h-5 mr-3" />
                                )}
                                {item.name}
                            </Link>
                        ))}
                    </nav>
                    {/* Logout Button */}
                    <div className="p-4 border-t border-gray-200 dark:border-gray-700">
                        <button
                            onClick={logout}
                            className="w-full rounded-xl px-4 py-3 text-sm font-medium bg-red-500/90 hover:bg-red-600 text-white transition-all duration-200 hover:shadow-lg active:scale-95"
                        >
                            Cerrar sesión
                        </button>
                    </div>
                </div>
            </aside>

            {/* Main Content */}
            <main className="flex-1 p-8 overflow-y-auto">
                <ErrorBoundary>
                    {children}
                </ErrorBoundary>
            </main>
        </div>
    );
}
