'use client';

import { ChangeEvent } from 'react';

interface TicketFiltersProps {
    search: string;
    status: string;
    priority: string;
    assignedTo: string;
    users: Array<{ id: number; name: string }>;
    onSearchChange: (value: string) => void;
    onStatusChange: (value: string) => void;
    onPriorityChange: (value: string) => void;
    onAssignedToChange: (value: string) => void;
    onClearFilters: () => void;
}

export default function TicketFilters({
    search,
    status,
    priority,
    assignedTo,
    users,
    onSearchChange,
    onStatusChange,
    onPriorityChange,
    onAssignedToChange,
    onClearFilters
}: TicketFiltersProps) {
    return (
        <div className="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-4">
            <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                {/* Search Input */}
                <div className="md:col-span-2">
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Search
                    </label>
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => onSearchChange(e.target.value)}
                        placeholder="Search by subject or description..."
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm"
                    />
                </div>

                {/* Status Filter */}
                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Status
                    </label>
                    <select
                        value={status}
                        onChange={(e) => onStatusChange(e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm"
                    >
                        <option value="">All Statuses</option>
                        <option value="open">Open</option>
                        <option value="in-progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>

                {/* Priority Filter */}
                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Priority
                    </label>
                    <select
                        value={priority}
                        onChange={(e) => onPriorityChange(e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm"
                    >
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>

                {/* Assigned To Filter */}
                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Assigned To
                    </label>
                    <select
                        value={assignedTo}
                        onChange={(e) => onAssignedToChange(e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm"
                    >
                        <option value="">All Agents</option>
                        <option value="unassigned">Unassigned</option>
                        {users.map((user) => (
                            <option key={user.id} value={user.id.toString()}>
                                {user.name}
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            {/* Clear Filters Button */}
            {(search || status || priority || assignedTo) && (
                <div className="mt-3 flex justify-end">
                    <button
                        onClick={onClearFilters}
                        className="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md"
                    >
                        Clear Filters
                    </button>
                </div>
            )}
        </div>
    );
}
