'use client';

import { useState, useEffect } from 'react';
import api from '@/lib/axios';
import Link from 'next/link';
import { useAuth } from '@/contexts/AuthContext';
import { motion, AnimatePresence } from 'framer-motion';
import { useRouter } from 'next/navigation';
import LockUserButton from '@/components/LockUserButton';
import UserStatusBadge from '@/components/UserStatusBadge';
import Toast from '@/components/Toast';
import RoleGuard from '@/components/RoleGuard';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    organization_id: number;
    is_locked?: boolean;
    locked_at?: string | null;
    locked_by?: number | null;
}

interface Role {
    id: number;
    name: string;
}

export default function UserListPage() {
    const { token, isAdmin, canManageUsers, user: currentUser } = useAuth();
    const router = useRouter();
    const [users, setUsers] = useState<User[]>([]);
    const [roles, setRoles] = useState<Role[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [assigningUser, setAssigningUser] = useState<number | null>(null);
    const [successMessage, setSuccessMessage] = useState('');
    const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' | 'info' } | null>(null);


    useEffect(() => {
        const fetchData = async () => {
            if (!token) {
                setLoading(false);
                return;
            }

            // Only fetch if user has permission
            if (!canManageUsers()) {
                setLoading(false);
                return;
            }

            try {
                const [usersRes, rolesRes] = await Promise.all([
                    api.get('/api/users', {
                        headers: { Authorization: `Bearer ${token}` }
                    }),
                    api.get('/api/roles', {
                        headers: { Authorization: `Bearer ${token}` }
                    })
                ]);
                setUsers(usersRes.data.users);
                setRoles(rolesRes.data.roles);
            } catch (err: any) {
                if (err.response?.status === 403 || err.response?.status === 401) {
                    setToast({ message: 'Acceso denegado', type: 'error' });
                    setTimeout(() => router.push('/dashboard'), 2000);
                } else {
                    setError('Failed to load data');
                }
                console.error(err);
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, [token, canManageUsers, router]);

    const handleAssignRole = async (userId: number, roleId: number) => {
        if (!token) return;

        setAssigningUser(userId);
        setError('');
        setSuccessMessage('');

        try {
            await api.post(`/api/users/${userId}/roles`, {
                role_id: roleId
            }, {
                headers: { Authorization: `Bearer ${token}` }
            });

            // Refresh users list
            const response = await api.get('/api/users', {
                headers: { Authorization: `Bearer ${token}` }
            });
            setUsers(response.data.users);
            setSuccessMessage('Role updated successfully');
            setTimeout(() => setSuccessMessage(''), 3000);
        } catch (err: any) {
            setError('Failed to assign role');
            console.error(err);
        } finally {
            setAssigningUser(null);
        }
    };

    const handleLockToggle = async (userId: number) => {
        if (!token) return;

        const user = users.find(u => u.id === userId);
        if (!user) return;

        try {
            const endpoint = user.is_locked ? 'unlock' : 'lock';
            await api.post(`/api/users/${userId}/${endpoint}`, {}, {
                headers: { Authorization: `Bearer ${token}` }
            });

            // Refresh users list
            const response = await api.get('/api/users', {
                headers: { Authorization: `Bearer ${token}` }
            });
            setUsers(response.data.users);
            setSuccessMessage(`User ${user.is_locked ? 'unlocked' : 'locked'} successfully`);
            setTimeout(() => setSuccessMessage(''), 3000);
        } catch (err: any) {
            if (err.response?.status === 403) {
                setToast({ message: 'Acceso denegado - no puedes bloquear/desbloquear tu propia cuenta', type: 'error' });
            } else {
                setError('Failed to update user status');
            }
            console.error(err);
        }
    };

    const getInitials = (name: string) => {
        return name
            .split(' ')
            .map((n) => n[0])
            .join('')
            .toUpperCase()
            .substring(0, 2);
    };

    const getRoleBadgeColor = (role: string) => {
        switch (role.toLowerCase()) {
            case 'owner':
                return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800';
            case 'admin':
                return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800';
            case 'agent':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600';
        }
    };

    const getAvatarColor = (name: string) => {
        const colors = [
            'bg-red-500', 'bg-yellow-500', 'bg-green-500', 'bg-blue-500', 'bg-indigo-500', 'bg-purple-500', 'bg-pink-500'
        ];
        const index = name.length % colors.length;
        return colors[index];
    };

    const handleDeleteUser = async (userId: number) => {
        if (!confirm('Are you sure you want to delete this user?')) return;

        try {
            await api.delete(`/api/users/${userId}`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            // Refresh users list
            const response = await api.get('/api/users', {
                headers: { Authorization: `Bearer ${token}` }
            });
            setUsers(response.data.users);
            setSuccessMessage('User deleted successfully');
            setTimeout(() => setSuccessMessage(''), 3000);
        } catch (err: any) {
            if (err.response?.status === 403) {
                setToast({ message: 'Acceso denegado', type: 'error' });
            } else {
                setError('Failed to delete user');
            }
            console.error(err);
        }
    };

    // Show loading state
    if (loading) return (
        <div className="flex justify-center items-center h-64">
            <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
        </div>
    );

    return (
        <RoleGuard allowedRoles={['admin', 'owner']}>
            <div className="space-y-6">
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

                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Team Members</h1>
                        <p className="text-gray-500 dark:text-gray-400 mt-1">Manage your organization's users and their roles.</p>
                    </div>
                    <Link
                        href="/dashboard/users/invite"
                        className="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                    >
                        <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Invite User
                    </Link>
                </div>

                <AnimatePresence>
                    {error && (
                        <motion.div
                            initial={{ opacity: 0, y: -10 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -10 }}
                            className="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-md"
                        >
                            <div className="flex">
                                <div className="shrink-0">
                                    <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                                    </svg>
                                </div>
                                <div className="ml-3">
                                    <p className="text-sm text-red-700 dark:text-red-200">{error}</p>
                                </div>
                            </div>
                        </motion.div>
                    )}
                    {successMessage && (
                        <motion.div
                            initial={{ opacity: 0, y: -10 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -10 }}
                            className="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 p-4 rounded-md"
                        >
                            <div className="flex">
                                <div className="shrink-0">
                                    <svg className="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                    </svg>
                                </div>
                                <div className="ml-3">
                                    <p className="text-sm text-green-700 dark:text-green-200">{successMessage}</p>
                                </div>
                            </div>
                        </motion.div>
                    )}
                </AnimatePresence>

                <div className="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700 transition-all duration-300 hover:shadow-2xl">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead className="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th scope="col" className="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                                    <th scope="col" className="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                                    <th scope="col" className="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th scope="col" className="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                {users.map((user) => (
                                    <tr key={user.id} className="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center">
                                                <div className={`shrink-0 h-10 w-10 rounded-full ${getAvatarColor(user.name)} flex items-center justify-center text-white font-bold shadow-sm`}>
                                                    {getInitials(user.name)}
                                                </div>
                                                <div className="ml-4">
                                                    <div className="text-sm font-medium text-gray-900 dark:text-white">{user.name}</div>
                                                    <div className="text-sm text-gray-500 dark:text-gray-400">{user.email}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className={`px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border ${getRoleBadgeColor(user.role)}`}>
                                                {user.role.charAt(0).toUpperCase() + user.role.slice(1)}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <UserStatusBadge isLocked={user.is_locked || false} lockedAt={user.locked_at} />
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <div className="flex gap-2 items-center">
                                                <button
                                                    onClick={() => router.push(`/dashboard/users/${user.id}`)}
                                                    className="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium"
                                                >
                                                    View
                                                </button>
                                                <button
                                                    onClick={() => router.push(`/dashboard/users/${user.id}/edit`)}
                                                    className="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                                                >
                                                    Edit
                                                </button>
                                                <LockUserButton
                                                    userId={user.id}
                                                    userName={user.name}
                                                    isLocked={user.is_locked || false}
                                                    onLockToggle={handleLockToggle}
                                                    disabled={user.id === currentUser?.id}
                                                />
                                                <button
                                                    onClick={() => handleDeleteUser(user.id)}
                                                    className="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-medium"
                                                    disabled={user.id === currentUser?.id}
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </RoleGuard>
    );
}
