'use client';

import { useState, useEffect, use } from 'react';
import api from '@/lib/axios';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';

interface Organization {
    id: number;
    name: string;
    created_at: string;
}

export default function OrganizationDetailPage({ params }: { params: Promise<{ id: string }> }) {
    const { id } = use(params);
    const { token } = useAuth();
    const [organization, setOrganization] = useState<Organization | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
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
                setOrganization(response.data.organization);
            } catch (err: any) {
                setError('Failed to load organization');
                console.error(err);
            } finally {
                setLoading(false);
            }
        };

        fetchOrganization();
    }, [id, token]);

    const handleDelete = async () => {
        if (!token) return;
        if (!confirm('Are you sure you want to delete this organization? This action cannot be undone.')) return;

        try {
            await api.delete(`/api/organizations/${id}`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            router.push('/dashboard/organizations');
        } catch (err) {
            console.error('Failed to delete organization', err);
            alert('Failed to delete organization');
        }
    };

    if (loading) return <div className="text-center p-8">Loading...</div>;
    if (!organization) return <div className="text-center p-8">Organization not found</div>;

    return (
        <div className="max-w-4xl mx-auto">
            <div className="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div className="px-4 py-5 sm:px-6 flex justify-between items-start">
                    <div>
                        <h3 className="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                            {organization.name}
                        </h3>
                        <p className="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                            Organization Details
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <button
                            onClick={() => router.push(`/dashboard/organizations/${id}/edit`)}
                            className="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                        >
                            Edit
                        </button>
                        <button
                            onClick={handleDelete}
                            className="px-4 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700"
                        >
                            Delete
                        </button>
                    </div>
                </div>
                <div className="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:p-0">
                    <dl className="sm:divide-y sm:divide-gray-200 dark:divide-gray-700">
                        <div className="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Organization ID</dt>
                            <dd className="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{organization.id}</dd>
                        </div>
                        <div className="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                            <dd className="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{organization.name}</dd>
                        </div>
                        <div className="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                            <dd className="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{organization.created_at}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    );
}
