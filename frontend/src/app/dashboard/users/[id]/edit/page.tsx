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
}

export default function EditUserPage({ params }: { params: Promise<{ id: string }> }) {
    const { id } = use(params);
    const { token, canManageUsers, user: currentUser } = useAuth();
    const [user, setUser] = useState<User | null>(null);
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [role, setRole] = useState('agent');
    const [password, setPassword] = useState('');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);
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

            // Don't fetch if not authorized (unless editing own profile)
            if (!canManageUsers() && currentUser?.id.toString() !== id) {
                setLoading(false);
                return;
            }

            try {
                const response = await api.get(`/api/users/${id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                const userData = response.data.user;
                setUser(userData);
                setName(userData.name);
                setEmail(userData.email);
                setRole(userData.role);
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

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');
        setSuccess(false);

        if (!token) {
            setError('You must be logged in');
            return;
        }

        try {
            const updateData: any = { name, email, role };
            if (password) {
                updateData.password = password;
            }

            await api.put(`/api/users/${id}`, updateData, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setSuccess(true);
            setToast({ message: 'User updated successfully', type: 'success' });
            setTimeout(() => router.push(`/dashboard/users/${id}`), 1500);
        } catch (err: any) {
            if (err.response?.status === 403) {
                setToast({ message: 'Acceso denegado', type: 'error' });
            } else {
                setError(err.response?.data?.message || 'Failed to update user');
            }
        }
    };

    if (loading) return <div className="text-center p-8">Loading...</div>;
    if (!user) return <div className="text-center p-8">User not found</div>;

    // Don't render if not authorized (will redirect)
    if (!canManageUsers() && currentUser?.id !== user.id) return null;

    return (
        <div className="max-w-2xl mx-auto">
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

            <div className="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
                <h1 className="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Edit User</h1>
                {error && <div className="mb-4 text-red-500 text-sm">{error}</div>}
                {success && <div className="mb-4 text-green-500 text-sm">User updated successfully! Redirecting...</div>}
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                        <input
                            type="text"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            required
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input
                            type="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            required
                        />
                    </div>
                    {canManageUsers() && (
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                            <select
                                value={role}
                                onChange={(e) => setRole(e.target.value)}
                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                                <option value="agent">Agent</option>
                                <option value="admin">Admin</option>
                                <option value="owner">Owner</option>
                                <option value="customer">Customer</option>
                            </select>
                        </div>
                    )}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Password (leave blank to keep current)
                        </label>
                        <input
                            type="password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Enter new password"
                        />
                    </div>
                    <div className="flex gap-4">
                        <button
                            type="submit"
                            className="flex-1 flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Update User
                        </button>
                        <button
                            type="button"
                            onClick={() => router.back()}
                            className="flex-1 flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
