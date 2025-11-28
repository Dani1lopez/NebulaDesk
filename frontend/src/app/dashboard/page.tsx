'use client';

import { useState, useEffect } from 'react';
import api from '@/lib/axios';
import { useAuth } from '@/contexts/AuthContext';
import { useRouter } from 'next/navigation';

export default function DashboardPage() {
    const { user, token } = useAuth();
    const router = useRouter();
    const [metrics, setMetrics] = useState({
        total_tickets: 0,
        open_tickets: 0,
        closed_tickets: 0,
        pending_actions: 0,
    });
    const [loading, setLoading] = useState(true);
    const [hasAccess, setHasAccess] = useState(true);

    useEffect(() => {
        // Redirect to create organization if user doesn't have one
        if (user && !user.organization_id) {
            router.push('/dashboard/create-organization');
            return;
        }

        const fetchMetrics = async () => {
            if (!user?.organization_id || !token) {
                setLoading(false);
                return;
            }

            try {
                // Backend now infers organization_id from the authenticated user
                const response = await api.get('/api/dashboard/metrics', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                setMetrics(response.data);
            } catch (err: any) {
                console.error('Failed to fetch metrics', err);
                if (err.response?.status === 403) {
                    setHasAccess(false);
                }
            } finally {
                setLoading(false);
            }
        };

        fetchMetrics();
    }, [user, token, router]);

    if (loading) return <div>Loading...</div>;

    if (!user?.organization_id) {
        return <div>Redirecting...</div>;
    }

    return (
        <div>
            <h1 className="text-3xl font-bold text-gray-800 dark:text-white mb-6">Dashboard</h1>
            
            {!hasAccess ? (
                <div className="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
                    <p className="text-yellow-800 dark:text-yellow-200">
                        You do not have permission to view dashboard metrics.
                    </p>
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div className="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Total Tickets</h3>
                        <p className="text-3xl font-bold text-gray-900 dark:text-white">{metrics.total_tickets}</p>
                    </div>
                    <div className="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Open Tickets</h3>
                        <p className="text-3xl font-bold text-green-600 dark:text-green-400">{metrics.open_tickets}</p>
                    </div>
                    <div className="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Closed Tickets</h3>
                        <p className="text-3xl font-bold text-blue-600 dark:text-blue-400">{metrics.closed_tickets}</p>
                    </div>
                    <div className="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Pending Actions</h3>
                        <p className="text-3xl font-bold text-orange-600 dark:text-orange-400">{metrics.pending_actions}</p>
                    </div>
                </div>
            )}
        </div>
    );
}
