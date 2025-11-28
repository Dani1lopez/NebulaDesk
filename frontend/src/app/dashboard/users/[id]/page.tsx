'use client';

import { useState, useEffect, use } from 'react';
import api from '@/lib/axios';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';
import Toast from '@/components/Toast';
import { AnimatePresence } from 'framer-motion';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    organization_id: number;
}

export default function UserDetailPage({ params }: { params: Promise<{ id: string }> }) {
    const { id } = use(params);
    const { token, canManageUsers, user: currentUser } = useAuth();
    const [user, setUser] = useState<User | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' | 'info' } | null>(null);
    const router = useRouter();

    // RBAC: Check if user can manage users
    useEffect(() => {
        if (!loading && token && !canManageUsers() && currentUser?.id.toString() !== id) {
            setToast({ message: 'Acceso denegado', type: 'error' });
            setTimeout(() => router.push('/dashboard'), 1500);
        }
    }, [loading, token, canManageUsers, currentUser, id, router]);

    useEffect(() => {
        const fetchUser = async () => {
            if (!token) {
                setLoading(false);
                return;
            }

            // Don't fetch if not authorized (unless viewing own profile)
            if (!canManageUsers() && currentUser?.id.toString() !== id) {
                setLoading(false);
                return;
            }

            try {
                const response = await api.get(`/api/users/${id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                setUser(response.data.user);
            } catch (err: any) {
                if (err.response?.status === 403) {
                    setToast({ message: 'Acceso denegado', type: 'error' });
                    setTimeout(() => router.push('/dashboard'), 1500);
                } else {
                    setError('Failed to load user');
                }
                console.error(err);
            } finally {
                setLoading(false);
            }
        };

        fetchUser();
    }, [id, token, canManageUsers, currentUser, router]);

    const handleDelete = async () => {
        if (!token) return;
        if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;

        try {
            await api.delete(`/api/users/${id}`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setToast({ message: 'User deleted successfully', type: 'success' });
            setTimeout(() => router.push('/dashboard/users'), 1500);
        } catch (err: any) {
            if (err.response?.status === 403) {
                setToast({ message: 'Acceso denegado', type: 'error' });
            } else {
                setToast({ message: 'Failed to delete user', type: 'error' });
            }
            console.error('Failed to delete user', err);
        }
    };

    if (loading) return <div className="text-center p-8">Loading...</div>;
    if (!user) return <div className="text-center p-8">User not found</div>;

    // Don't render if not authorized (will redirect)
    if (!canManageUsers() && currentUser?.id !== user.id) return null;

    return (
        <div className="max-w-4xl mx-auto">
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

            <div className="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div className="px-4 py-5 sm:px-6 flex justify-between items-start">
                    <div>
                        <h3 className="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                            {user.name}
                        </h3>
                        <p className="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                            User Details
                        </p>
                    </div>
                    {canManageUsers() && (
                        <div className="flex gap-2">
                            <button
                                onClick={() => router.push(`/dashboard/users/${id}/edit`)}
                                className="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                            >
                                Edit
                            </button>
                            <button
                                onClick={handleDelete}
                                className="px-4 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700"
                                disabled={currentUser?.id === user.id}
                            >
                                Delete
                            </button>
                        </div>
                    )}
                </div>
                <div className="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:p-0">
                    <dl className="sm:divide-y sm:divide-gray-200 dark:divide-gray-700">
                        <div className="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">User ID</dt>
                            <dd className="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{user.id}</dd>
                        </div>
                        <div className="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                            <dd className="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{user.name}</dd>
                        </div>
                        <div className="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                            <dd className="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{user.email}</dd>
                        </div>
                        <div className="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Role</dt>
                            <dd className="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2 capitalize">{user.role}</dd>
                        </div>
                        <div className="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Organization ID</dt>
                            <dd className="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{user.organization_id}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    );
}
