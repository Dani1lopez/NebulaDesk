'use client';

import { useState, useEffect } from 'react';
import api from '@/lib/axios';

export default function ProfilePage() {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [loading, setLoading] = useState(true);
    const [success, setSuccess] = useState('');
    const [error, setError] = useState('');
    const [avatar, setAvatar] = useState<File | null>(null);
    const [currentAvatar, setCurrentAvatar] = useState('');

    useEffect(() => {
        const fetchUser = async () => {
            try {
                const token = localStorage.getItem('token');
                const response = await api.get('/api/user', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                setName(response.data.name);
                setEmail(response.data.email);
                setCurrentAvatar(response.data.avatar);
            } catch (err: any) {
                setError('Failed to load profile');
                console.error(err);
            } finally {
                setLoading(false);
            }
        };

        fetchUser();
    }, []);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');
        setSuccess('');

        try {
            const token = localStorage.getItem('token');
            const formData = new FormData();
            formData.append('name', name);
            formData.append('email', email);
            if (avatar) {
                formData.append('avatar', avatar);
            }
            // Use _method PUT trick if Laravel has issues with PUT + FormData, but usually POST with _method=PUT is safer for file uploads
            // However, let's try direct PUT first, or switch to POST with _method
            // Laravel often struggles with PUT and multipart/form-data.
            // Let's use POST with _method=PUT
            formData.append('_method', 'PUT');

            const response = await api.post('/api/user/profile', formData, {
                headers: { 
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'multipart/form-data'
                }
            });
            
            setSuccess('Profile updated successfully');
            setCurrentAvatar(response.data.user.avatar);
            setAvatar(null);
        } catch (err: any) {
            setError(err.response?.data?.message || 'Failed to update profile');
        }
    };

    return (
        <div className="max-w-md mx-auto bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
            <h1 className="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Your Profile</h1>
            {error && <div className="mb-4 text-red-500 text-sm">{error}</div>}
            {success && <div className="mb-4 text-green-500 text-sm">{success}</div>}
            
            <div className="mb-6 flex flex-col items-center">
                <div className="w-24 h-24 rounded-full overflow-hidden bg-gray-200 mb-2">
                    {currentAvatar ? (
                        <img src={`http://localhost:8000${currentAvatar}`} alt="Profile" className="w-full h-full object-cover" />
                    ) : (
                        <div className="w-full h-full flex items-center justify-center text-gray-400">
                            <svg className="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                                <path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd" />
                            </svg>
                        </div>
                    )}
                </div>
                <input 
                    type="file" 
                    accept="image/*"
                    onChange={(e) => setAvatar(e.target.files ? e.target.files[0] : null)}
                    className="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                />
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
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
                <button
                    type="submit"
                    className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Update Profile
                </button>
            </form>
        </div>
    );
}
