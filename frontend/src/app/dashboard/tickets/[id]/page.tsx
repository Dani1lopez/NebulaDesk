'use client';

import { useState, useEffect, use } from 'react';
import api from '@/lib/axios';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';
import FileUpload from '@/components/FileUpload';

interface Ticket {
    id: number;
    subject: string;
    description: string;
    status: string;
    priority: string;
    created_at: string;
    assignee_id?: number;
}

interface Comment {
    id: number;
    content: string;
    user_id: number;
    created_at: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface Attachment {
    id: number;
    filename: string;
    size: number;
    mimetype: string;
    created_at: string;
}

export default function TicketDetailPage({ params }: { params: Promise<{ id: string }> }) {
    const { id } = use(params);
    const { token, user, isCustomer, isAdmin, isOwner, isAgent } = useAuth();
    const [ticket, setTicket] = useState<Ticket | null>(null);
    const [comments, setComments] = useState<Comment[]>([]);
    const [attachments, setAttachments] = useState<Attachment[]>([]);
    const [newComment, setNewComment] = useState('');
    const [users, setUsers] = useState<User[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const router = useRouter();

    const fetchAttachments = async () => {
        if (!token) return;

        try {
            const response = await api.get(`/api/tickets/${id}/attachments`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setAttachments(response.data.attachments);
        } catch (err) {
            console.error('Failed to load attachments', err);
        }
    };

    useEffect(() => {
        const fetchData = async () => {
            if (!token) {
                setLoading(false);
                return;
            }

            try {
                const [ticketRes, commentsRes, usersRes] = await Promise.all([
                    api.get(`/api/tickets/${id}`, {
                        headers: { Authorization: `Bearer ${token}` }
                    }),
                    api.get(`/api/tickets/${id}/comments`, {
                        headers: { Authorization: `Bearer ${token}` }
                    }),
                    api.get('/api/organizations/users', {
                        headers: { Authorization: `Bearer ${token}` }
                    })
                ]);
                setTicket(ticketRes.data.ticket);
                setComments(commentsRes.data.comments);
                setUsers(usersRes.data.users);

                // Fetch attachments
                await fetchAttachments();
            } catch (err: any) {
                if (err.response?.status === 403) {
                    setError('Access denied. You do not have permission to view this ticket.');
                    setTimeout(() => router.push('/dashboard/tickets'), 2000);
                } else if (err.response?.status === 404) {
                    setError('Ticket not found.');
                    setTimeout(() => router.push('/dashboard/tickets'), 2000);
                } else {
                    setError('Failed to load ticket data');
                }
                console.error(err);
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, [id, token, router]);

    const handleCommentSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!newComment.trim() || !token) return;

        try {
            const response = await api.post(`/api/tickets/${id}/comments`, { content: newComment }, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setComments([...comments, response.data.comment]);
            setNewComment('');
        } catch (err) {
            console.error('Failed to post comment', err);
        }
    };

    const handleAssignTicket = async (userId: number) => {
        if (!token) return;

        try {
            await api.put(`/api/tickets/${id}/assign`, {
                assignee_id: userId
            }, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setTicket(prev => prev ? { ...prev, assignee_id: userId } : null);
        } catch (err) {
            console.error('Failed to assign ticket', err);
        }
    };

    const handleStatusChange = async (status: string) => {
        if (!token) return;

        try {
            await api.put(`/api/tickets/${id}/status`, {
                status
            }, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setTicket(prev => prev ? { ...prev, status } : null);
        } catch (err) {
            console.error('Failed to update status', err);
        }
    };

    const handleDownload = async (attachmentId: number, filename: string) => {
        if (!token) return;

        try {
            const response = await api.get(`/api/attachments/${attachmentId}/download`, {
                headers: { Authorization: `Bearer ${token}` },
                responseType: 'blob'
            });

            // Create download link
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            link.remove();
        } catch (err) {
            console.error('Failed to download file', err);
        }
    };

    const handleDelete = async () => {
        if (!token) return;
        if (!confirm('Are you sure you want to delete this ticket? This action cannot be undone.')) return;

        try {
            await api.delete(`/api/tickets/${id}`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            router.push('/dashboard/tickets');
        } catch (err) {
            console.error('Failed to delete ticket', err);
            alert('Failed to delete ticket');
        }
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'open': return 'bg-green-100 text-green-800';
            case 'in-progress': return 'bg-blue-100 text-blue-800';
            case 'resolved': return 'bg-purple-100 text-purple-800';
            case 'closed': return 'bg-gray-100 text-gray-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };


    if (loading) return <div>Loading...</div>;
    if (error) return <div className="text-center p-8 text-red-500">{error}</div>;
    if (!ticket) return <div>Ticket not found</div>;

    const assignedUser = users.find(u => u.id === ticket.assignee_id);
    
    // Determine if user can edit this ticket
    const canEdit = isAdmin() || isOwner() || isAgent() || (isCustomer() && ticket.assignee_id === user?.id);
    const canDelete = isAdmin() || isOwner() || isAgent();
    const canAssign = isAdmin() || isOwner() || isAgent();
    const canUpdateStatus = isAdmin() || isOwner() || isAgent();

    return (
        <div className="max-w-4xl mx-auto space-y-6">
            <div className="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div className="px-4 py-5 sm:px-6 flex justify-between items-start">
                    <div>
                        <h3 className="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                            #{ticket.id} - {ticket.subject}
                        </h3>
                        <p className="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                            {ticket.created_at}
                        </p>
                    </div>
                    <div className="flex gap-2">
                        {canEdit && (
                            <button
                                onClick={() => router.push(`/dashboard/tickets/${id}/edit`)}
                                className="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                            >
                                Edit
                            </button>
                        )}
                        {canDelete && (
                            <button
                                onClick={handleDelete}
                                className="px-4 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700"
                            >
                                Delete
                            </button>
                        )}
                    </div>
                </div>
                <div className="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:p-0">
                    <dl className="sm:divide-y sm:divide-gray-200 dark:divide-gray-700">
                        <div className="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                            <dd className="mt-1 sm:mt-0 sm:col-span-2">
                                {canUpdateStatus ? (
                                    <select
                                        value={ticket.status}
                                        onChange={(e) => handleStatusChange(e.target.value)}
                                        className={`px-3 py-1 rounded-full text-xs font-semibold ${getStatusColor(ticket.status)} border-0 focus:outline-none focus:ring-2 focus:ring-indigo-500`}
                                    >
                                        <option value="open">Open</option>
                                        <option value="in-progress">In Progress</option>
                                        <option value="resolved">Resolved</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                ) : (
                                    <span className={`px-3 py-1 rounded-full text-xs font-semibold ${getStatusColor(ticket.status)}`}>
                                        {ticket.status}
                                    </span>
                                )}
                            </dd>
                        </div>
                        <div className="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Priority</dt>
                            <dd className="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2 uppercase">{ticket.priority}</dd>
                        </div>
                        <div className="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned To</dt>
                            <dd className="mt-1 sm:mt-0 sm:col-span-2">
                                {canAssign ? (
                                    <>
                                        <select
                                            value={ticket.assignee_id || ''}
                                            onChange={(e) => handleAssignTicket(parseInt(e.target.value))}
                                            className="px-3 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        >
                                            <option value="">Unassigned</option>
                                            {users.map((user) => (
                                                <option key={user.id} value={user.id}>
                                                    {user.name} ({user.email})
                                                </option>
                                            ))}
                                        </select>
                                        {assignedUser && (
                                            <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                Currently: {assignedUser.name}
                                            </p>
                                        )}
                                    </>
                                ) : (
                                    <span className="text-sm text-gray-900 dark:text-white">
                                        {assignedUser ? `${assignedUser.name} (${assignedUser.email})` : 'Unassigned'}
                                    </span>
                                )}
                            </dd>
                        </div>
                        <div className="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                            <dd className="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                                {ticket.description}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {/* Attachments Section */}
            <div className="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <h4 className="text-lg font-medium text-gray-900 dark:text-white mb-4">Attachments</h4>

                <FileUpload ticketId={parseInt(id)} onUploadSuccess={fetchAttachments} />

                {attachments.length > 0 && (
                    <div className="mt-4 space-y-2">
                        {attachments.map((attachment) => (
                            <div key={attachment.id} className="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div>
                                    <p className="text-sm font-medium text-gray-900 dark:text-white">{attachment.filename}</p>
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        {(attachment.size / 1024).toFixed(2)} KB • {attachment.created_at}
                                    </p>
                                </div>
                                <button
                                    onClick={() => handleDownload(attachment.id, attachment.filename)}
                                    className="px-3 py-1 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700"
                                >
                                    Download
                                </button>
                            </div>
                        ))}
                    </div>
                )}
                {attachments.length === 0 && (
                    <p className="mt-4 text-sm text-gray-500 dark:text-gray-400">No attachments yet.</p>
                )}
            </div>

            {/* Comments Section */}
            <div className="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <h4 className="text-lg font-medium text-gray-900 dark:text-white mb-4">Comments</h4>
                <div className="space-y-4 mb-6">
                    {comments.map((comment) => (
                        <div key={comment.id} className="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <p className="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                User #{comment.user_id} - {comment.created_at}
                            </p>
                            <p className="text-gray-900 dark:text-white">{comment.content}</p>
                        </div>
                    ))}
                    {comments.length === 0 && <p className="text-gray-500">No comments yet.</p>}
                </div>

                <form onSubmit={handleCommentSubmit}>
                    <textarea
                        value={newComment}
                        onChange={(e) => setNewComment(e.target.value)}
                        rows={3}
                        className="shadow-sm block w-full focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2"
                        placeholder="Add a comment..."
                        required
                    />
                    <div className="mt-3 flex justify-end">
                        <button
                            type="submit"
                            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Post Comment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
