'use client';

import { useState, useEffect, use } from 'react';
import api from '@/lib/axios';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';

interface Ticket {
    id: number;
    subject: string;
    description: string;
    priority: string;
    status: string;
}

export default function EditTicketPage({ params }: { params: Promise<{ id: string }> }) {
    const { id } = use(params);
    const { token } = useAuth();
    const [ticket, setTicket] = useState<Ticket | null>(null);
    const [subject, setSubject] = useState('');
    const [description, setDescription] = useState('');
    const [priority, setPriority] = useState('medium');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);
    const router = useRouter();

    useEffect(() => {
        const fetchTicket = async () => {
            if (!token) {
                setLoading(false);
                return;
            }

            try {
                const response = await api.get(`/api/tickets/${id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                const ticketData = response.data.ticket;
                setTicket(ticketData);
                setSubject(ticketData.subject);
                setDescription(ticketData.description);
                setPriority(ticketData.priority);
            } catch (err: any) {
                if (err.response?.status === 403) {
                    setError('Access denied. You do not have permission to edit this ticket.');
                    setTimeout(() => router.push(`/dashboard/tickets/${id}`), 2000);
                } else {
                    setError('Failed to load ticket');
                }
                console.error(err);
            } finally {
                setLoading(false);
            }
        };

        fetchTicket();
    }, [id, token]);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');
        setSuccess(false);
        setLoading(true);

        if (!token) {
            setError('You must be logged in');
            setLoading(false);
            return;
        }

        try {
            await api.put(`/api/tickets/${id}`, {
                subject,
                description,
                priority,
            }, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setSuccess(true);
            setTimeout(() => router.push(`/dashboard/tickets/${id}`), 1500);
        } catch (err: any) {
            setError(err.response?.data?.message || 'Failed to update ticket');
            setLoading(false);
        }
    };

    if (loading) return <div className="text-center p-8">Loading...</div>;
    if (!ticket) return <div className="text-center p-8">Ticket not found</div>;

    return (
        <div className="max-w-2xl mx-auto bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
            <h1 className="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Edit Ticket #{id}</h1>
            {error && <div className="mb-4 text-red-500 text-sm">{error}</div>}
            {success && <div className="mb-4 text-green-500 text-sm">Ticket updated successfully! Redirecting...</div>}
            <form onSubmit={handleSubmit} className="space-y-6">
                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject</label>
                    <input
                        type="text"
                        value={subject}
                        onChange={(e) => setSubject(e.target.value)}
                        className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        required
                    />
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                    <select
                        value={priority}
                        onChange={(e) => setPriority(e.target.value)}
                        className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea
                        value={description}
                        onChange={(e) => setDescription(e.target.value)}
                        rows={4}
                        className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        required
                    />
                </div>
                <div className="flex gap-4">
                    <button
                        type="submit"
                        disabled={loading}
                        className={`flex-1 flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 ${loading ? 'opacity-50 cursor-not-allowed' : ''}`}
                    >
                        {loading ? 'Updating...' : 'Update Ticket'}
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
    );
}
