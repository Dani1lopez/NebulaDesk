'use client';

import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { useRouter } from 'next/navigation';
import api from '@/lib/axios';

interface User {
    id: number;
    name: string;
    email: string;
    organization_id: number;
    role?: string;
    avatar?: string;
    email_verified?: boolean;
}

interface AuthContextType {
    user: User | null;
    token: string | null;
    loading: boolean;
    login: (email: string, password: string) => Promise<void>;
    register: (name: string, email: string, password: string) => Promise<void>;
    logout: () => Promise<void>;
    isAuthenticated: boolean;
    isAdmin: () => boolean;
    isOwner: () => boolean;
    isAgent: () => boolean;
    isCustomer: () => boolean;
    canManageUsers: () => boolean;
    isEmailVerified: () => boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
    const [user, setUser] = useState<User | null>(null);
    const [token, setToken] = useState<string | null>(null);
    const [loading, setLoading] = useState(true);
    const router = useRouter();

    useEffect(() => {
        // Load user and token from localStorage on mount
        const storedToken = localStorage.getItem('token');
        if (storedToken) {
            setToken(storedToken);
            fetchUser(storedToken);
        } else {
            setLoading(false);
        }
    }, []);

    const fetchUser = async (authToken: string) => {
        try {
            const response = await api.get('/api/user', {
                headers: { Authorization: `Bearer ${authToken}` }
            });
            setUser(response.data);
        } catch (error) {
            console.error('Failed to fetch user:', error);
            // Token might be invalid, clear it
            localStorage.removeItem('token');
            setToken(null);
            // Redirect to login if token is invalid
            router.push('/login');
        } finally {
            setLoading(false);
        }
    };

    const login = async (email: string, password: string) => {
        try {
            const response = await api.post('/api/login', { email, password });
            const { token: authToken, user: userData } = response.data;

            localStorage.setItem('token', authToken);
            setToken(authToken);
            setUser(userData);

            router.push('/dashboard');
        } catch (error: any) {
            // Handle specific error types
            if (error.response?.status === 423) {
                // Account locked
                throw new Error('Your account has been locked due to multiple failed login attempts. Please contact an administrator.');
            } else if (error.response?.status === 401) {
                // Invalid credentials
                throw new Error('Email or password is incorrect.');
            } else {
                // Generic error
                throw new Error(error.response?.data?.message || 'Login failed');
            }
        }
    };

    const register = async (name: string, email: string, password: string) => {
        try {
            const response = await api.post('/api/register', {
                name,
                email,
                password,
                password_confirmation: password
            });
            const { token: authToken, user: userData } = response.data;

            localStorage.setItem('token', authToken);
            setToken(authToken);
            setUser(userData);

            router.push('/dashboard');
        } catch (error: any) {
            // Handle registration disabled (403)
            if (error.response?.status === 403) {
                const errorCode = error.response?.data?.error;
                if (errorCode === 'registration_disabled') {
                    throw new Error(error.response?.data?.message || 'El registro público está deshabilitado. Inicia sesión o contacta a un administrador.');
                }
            }
            // Generic error
            throw new Error(error.response?.data?.message || 'Registration failed');
        }
    };

    const logout = async () => {
        try {
            const token = localStorage.getItem('token');
            if (token) {
                await api.post(
                    '/api/logout',
                    {},
                    { headers: { Authorization: `Bearer ${token}` } }
                );
            }
        } catch (error) {
            // If the API fails, still proceed with client-side logout
            console.error('Error during logout', error);
        } finally {
            // Always clear client state regardless of API response
            localStorage.removeItem('token');
            setUser(null);
            setToken(null);
            router.push('/login');
        }
    };

    const isAdmin = () => {
        return user?.role === 'admin' || user?.role === 'owner';
    };

    const isOwner = () => {
        return user?.role === 'owner';
    };

    const canManageUsers = () => {
        return isAdmin();
    };

    const isAgent = () => {
        return user?.role === 'agent';
    };

    const isCustomer = () => {
        return user?.role === 'customer';
    };

    const isEmailVerified = () => {
        return user?.email_verified === true;
    };

    return (
        <AuthContext.Provider
            value={{
                user,
                token,
                loading,
                login,
                register,
                logout,
                isAuthenticated: !!token && !!user,
                isAdmin,
                isOwner,
                isAgent,
                isCustomer,
                canManageUsers,
                isEmailVerified,
            }}
        >
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    const context = useContext(AuthContext);
    if (context === undefined) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
}
