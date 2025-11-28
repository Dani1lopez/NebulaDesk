'use client';

import { useState, useEffect } from 'react';
import api from '@/lib/axios';
import Link from 'next/link';
import { useAuth } from '@/contexts/AuthContext';
import { useRouter } from 'next/navigation';
import Toast from '@/components/Toast';
import { AnimatePresence } from 'framer-motion';
import RoleGuard from '@/components/RoleGuard';

interface Organization {
    id: number;
    name: string;
    domain?: string;
    is_active: boolean;
    created_at: string;
    users_count: number;
    tickets_count: number;
}

export default function OrganizationsPage() {
    const { token, isAdmin, user } = useAuth();
    const router = useRouter();
    const [organizations, setOrganizations] = useState<Organization[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' | 'info' } | null>(null);

    const isOwner = user?.role === 'owner';
    const canViewOrganizations = isAdmin() || isOwner;


    useEffect(() => {
        const fetchOrganizations = async () => {
            if (!token) {
                setLoading(false);
                return;
            }

            // Don't fetch if not authorized
            if (!canViewOrganizations) {
                setLoading(false);
                return;
            }

            try {
                const response = await api.get('/api/organizations', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                setOrganizations(response.data.organizations);
            } catch (err: any) {
                if (err.response?.status === 403) {
                    setToast({ message: 'Acceso denegado', type: 'error' });
                    setTimeout(() => router.push('/dashboard'), 1500);
                } else {
                    setError('Failed to load organizations');
                }
                console.error(err);
            } finally {
                setLoading(false);
            }
        };

        fetchOrganizations();
    }, [token, canViewOrganizations, router]);

    if (loading) return <div className="text-center p-8">Loading...</div>;

    return (
        <RoleGuard allowedRoles={['admin', 'owner']}>
            <div>
                {/* Toast notifications */}
                <div className="fixed top-4 right-4 z-50">
                    <AnimatePresence>
                        {toast && (
                            <Toast
                                message={toast.message}
                                type={toast.type}
                                onClose={() => setToast(null)}
                            />
                        )}
                    </AnimatePresence>
                </div>

                <div className="flex justify-between items-center mb-6">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Organizations</h1>
                        <p className="text-gray-500 dark:text-gray-400 mt-1">Manage organizations and their members.</p>
                    </div>
                    {isAdmin() && (
                        <Link
                            href="/dashboard/organizations/create"
                            className="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition-colors"
                        >
                            <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                            </svg>
                            New Organization
                        </Link>
                    )}
                </div>

                {error && <div className="text-red-500 mb-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-md">{error}</div>}

                <div className="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead className="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Domain</th>
                                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Users</th>
                                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tickets</th>
                                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            {organizations.map((org) => (
                                <tr key={org.id} className="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                        {org.id}
                                    </td>
                                    <td className="px-6 py-4 text-sm text-gray-900 dark:text-white font-medium">
                                        <Link href={`/dashboard/organizations/${org.id}`} className="hover:text-indigo-600 dark:hover:text-indigo-400">
                                            {org.name}
                                        </Link>
                                    </td>
                                    <td className="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{org.domain || '-'}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{org.users_count}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{org.tickets_count}</td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className={`px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${org.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'}`}>
                                            {org.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{org.created_at}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm">
                                        <Link href={`/dashboard/organizations/${org.id}`} className="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium mr-4">
                                            View
                                        </Link>
                                        {isAdmin() && (
                                            <Link href={`/dashboard/organizations/${org.id}/edit`} className="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                                Edit
                                            </Link>
                                        )}
                                    </td>
                                </tr>
                            ))}
                            {organizations.length === 0 && (
                                <tr>
                                    <td colSpan={8} className="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No organizations found
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </RoleGuard>
    );
}
