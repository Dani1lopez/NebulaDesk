'use client';

import { useState, ChangeEvent } from 'react';
import api from '@/lib/axios';
import { useAuth } from '@/contexts/AuthContext';

interface FileUploadProps {
    ticketId: number;
    onUploadSuccess: () => void;
}

export default function FileUpload({ ticketId, onUploadSuccess }: FileUploadProps) {
    const { token } = useAuth();
    const [file, setFile] = useState<File | null>(null);
    const [uploading, setUploading] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);

    const handleFileChange = (e: ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            const selectedFile = e.target.files[0];

            // Validate file size (max 10MB)
            if (selectedFile.size > 10 * 1024 * 1024) {
                setError('File size must be less than 10MB');
                return;
            }

            setFile(selectedFile);
            setError('');
            setSuccess(false);
        }
    };

    const handleUpload = async () => {
        if (!file || !token) return;

        setUploading(true);
        setError('');
        setSuccess(false);

        try {
            const formData = new FormData();
            formData.append('file', file);

            await api.post(`/api/tickets/${ticketId}/attachments`, formData, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'multipart/form-data',
                }
            });

            setSuccess(true);
            setFile(null);

            // Reset file input
            const fileInput = document.getElementById('file-input') as HTMLInputElement;
            if (fileInput) fileInput.value = '';

            // Notify parent component
            onUploadSuccess();

            // Clear success message after 3 seconds
            setTimeout(() => setSuccess(false), 3000);
        } catch (err: any) {
            setError(err.response?.data?.message || 'Failed to upload file');
        } finally {
            setUploading(false);
        }
    };

    return (
        <div className="border border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700">
            <h5 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Upload File</h5>

            <div className="flex items-center gap-3">
                <input
                    id="file-input"
                    type="file"
                    onChange={handleFileChange}
                    className="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900 dark:file:text-indigo-200"
                    disabled={uploading}
                />
                <button
                    onClick={handleUpload}
                    disabled={!file || uploading}
                    className="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed whitespace-nowrap"
                >
                    {uploading ? 'Uploading...' : 'Upload'}
                </button>
            </div>

            {error && <p className="mt-2 text-sm text-red-600 dark:text-red-400">{error}</p>}
            {success && <p className="mt-2 text-sm text-green-600 dark:text-green-400">File uploaded successfully!</p>}
            {file && (
                <p className="mt-2 text-xs text-gray-600 dark:text-gray-400">
                    Selected: {file.name} ({(file.size / 1024).toFixed(2)} KB)
                </p>
            )}
        </div>
    );
}
