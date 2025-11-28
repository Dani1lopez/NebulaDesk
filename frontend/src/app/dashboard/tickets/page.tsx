'use client';

import { useState, useEffect } from 'react';
import api from '@/lib/axios';
import Link from 'next/link';
import { useAuth } from '@/contexts/AuthContext';
import TicketFilters from '@/components/TicketFilters';

interface Ticket {
    id: number;
    subject: string;
    status: string;
    priority: string;
    created_at: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

export default function TicketListPage() {
    const { user, token, isAuthenticated } = useAuth();
    const [tickets, setTickets] = useState<Ticket[]>([]);
    const [users, setUsers] = useState<User[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    // Filter states
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('');
    const [priority, setPriority] = useState('');
    const [assignedTo, setAssignedTo] = useState('');

    // Redirect to login if not authenticated
    useEffect(() => {
        if (!isAuthenticated && !loading) {
            window.location.href = '/login';
        }
    }, [isAuthenticated, loading]);

    useEffect(() => {
        const fetchUsers = async () => {
            if (!token) return;

            try {
                const response = await api.get('/api/organizations/users', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                setUsers(response.data.users || []);
            } catch (err: any) {
                console.error('Failed to load users', err);
                // If 403 (Forbidden) or 500, just leave users empty. 
                // Customers might get 403 if they try to list users, which is expected.
                if (err.response?.status === 403) {
                    console.log('User not authorized to list organization users');
                }
            }
        };

        fetchUsers();
    }, [token]);

    useEffect(() => {
        const fetchTickets = async () => {
            if (!token) {
                setLoading(false);
                return;
            }

            try {
                // Build query parameters (no organization_id needed, backend infers from user)
                const params = new URLSearchParams();

                if (search) params.append('search', search);
                if (status) params.append('status', status);
                if (priority) params.append('priority', priority);
                if (assignedTo && assignedTo !== 'unassigned') params.append('assignee_id', assignedTo);

                const response = await api.get(`/api/tickets?${params.toString()}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                setTickets(response.data.tickets);
            } catch (err: any) {
                if (err.response?.status === 403) {
                    setError('Access denied. You do not have permission to view tickets.');
                } else {
                    setError('Failed to load tickets');
                }
                console.error(err);
            } finally {
                setLoading(false);
            }
        };

        fetchTickets();
    }, [token, search, status, priority, assignedTo]);

    const handleClearFilters = () => {
        setSearch('');
        setStatus('');
        setPriority('');
        setAssignedTo('');
    };

    if (loading) return <div className="text-center p-8">Loading tickets...</div>;

    return (
        <div>
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-3xl font-bold text-gray-800 dark:text-white">Tickets</h1>
                <Link
                    href="/dashboard/tickets/create"
                    className="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                >
                    New Ticket
                </Link>
            </div>

            {error && <div className="text-red-500 mb-4">{error}</div>}

            <TicketFilters
                search={search}
                status={status}
                priority={priority}
                assignedTo={assignedTo}
                users={users}
                onSearchChange={setSearch}
                onStatusChange={setStatus}
                onPriorityChange={setPriority}
                onAssignedToChange={setAssignedTo}
                onClearFilters={handleClearFilters}
            />

            <div className="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead className="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subject</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Priority</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        {tickets.map((ticket) => (
                            <tr
                                key={ticket.id}
                                className="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors"
                            >
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                    <Link href={`/dashboard/tickets/${ticket.id}`}>
                                        #{ticket.id}
                                    </Link>
                                </td>
                                <td className="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    <Link href={`/dashboard/tickets/${ticket.id}`}>
                                        {ticket.subject}
                                    </Link>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 uppercase">{ticket.status}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 uppercase">{ticket.priority}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{ticket.created_at}</td>
                            </tr>
                        ))}
                        {tickets.length === 0 && (
                            <tr>
                                <td colSpan={5} className="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No tickets found
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
