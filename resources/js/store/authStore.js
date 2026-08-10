import { create } from 'zustand';
import { persist } from 'zustand/middleware';

const useAuthStore = create(
    persist(
        (set) => ({
            user: null,
            token: null,
            isAuthenticated: false,

            setAuth: (user, token) => {
                localStorage.setItem('auth_token', token);
                set({ user, token, isAuthenticated: true });
            },

            clearAuth: () => {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('auth_user');
                set({ user: null, token: null, isAuthenticated: false });
            },

            updateUser: (user) => set({ user }),
        }),
        {
            name: 'auth_user',
            partialize: (state) => ({ user: state.user, token: state.token, isAuthenticated: state.isAuthenticated }),
        }
    )
);

export default useAuthStore;
