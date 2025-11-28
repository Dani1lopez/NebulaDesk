'use client';

import { useState, useEffect } from 'react';
import api from '@/lib/axios';

export default function AuditLogsPage() {
    const [logs, setLogs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        const fetchLogs = async () => {
            try {
                const token = localStorage.getItem('token');
                const response = await api.get('/api/audit-logs', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                setLogs(response.data.audit_logs || []);
            } catch (err: any) {
                if (err.response?.status === 403) {
                    setError('You do not have permission to view audit logs.');
                } else {
                    setError('Failed to load audit logs');
                }
                console.error(err);
            } finally {
                setLoading(false);
            }
        };

        fetchLogs();
    }, []);

    if (loading) return <div className="p-8">Loading audit logs...</div>;
    if (error) return <div className="p-8 text-red-500">{error}</div>;

    return (
        <div className="p-8">
            <h1 className="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Audit Logs</h1>
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead className="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ID</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">User ID</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Action</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Entity</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Entity ID</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Created At</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        {logs.length === 0 ? (
                            <tr>
                                <td colSpan={6} className="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No audit logs found
                                </td>
                            </tr>
                        ) : (
                            logs.map((log: any) => (
                                <tr key={log.id}>
                                    <td className="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{log.id}</td>
                                    <td className="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{log.user_id}</td>
                                    <td className="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{log.action}</td>
                                    <td className="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{log.entity_type}</td>
                                    <td className="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{log.entity_id}</td>
                                    <td className="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{log.created_at}</td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
