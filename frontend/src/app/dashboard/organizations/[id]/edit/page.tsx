'use client';

import { useState, useEffect, use } from 'react';
import api from '@/lib/axios';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';

export default function EditOrganizationPage({ params }: { params: Promise<{ id: string }> }) {
    const { id } = use(params);
    const { token } = useAuth();
    const [name, setName] = useState('');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');
    const router = useRouter();

    useEffect(() => {
        const fetchOrganization = async () => {
            if (!token) {
                setLoading(false);
                return;
            }

            try {
                const response = await api.get(`/api/organizations/${id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                setName(response.data.organization.name);
            } catch (err: any) {
                setError('Failed to load organization');
                console.error(err);
            } finally {
                setLoading(false);
            }
        };

        fetchOrganization();
    }, [id, token]);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');
        setSuccess('');

        try {
            await api.put(`/api/organizations/${id}`, { name }, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setSuccess('Organization updated successfully!');
            setTimeout(() => router.push(`/dashboard/organizations/${id}`), 1500);
        } catch (err: any) {
            setError(err.response?.data?.message || 'Failed to update organization');
        }
    };

    if (loading) return <div className="text-center p-8">Loading...</div>;

    return (
        <div className="max-w-md mx-auto bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
            <h1 className="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Edit Organization</h1>
            {error && <div className="mb-4 text-red-500 text-sm">{error}</div>}
            {success && <div className="mb-4 text-green-500 text-sm">{success}</div>}
            <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Organization Name</label>
                    <input
                        type="text"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                        className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        required
                    />
                </div>
                <div className="flex gap-4">
                    <button
                        type="submit"
                        className="flex-1 flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Update Organization
                    </button>
                    <button
                        type="button"
                        onClick={() => router.back()}
                        className="flex-1 flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-white dark:border-gray-600"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    );
}
