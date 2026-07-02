import {
    createContext,
    useContext,
    useEffect,
    useState,
    type ReactNode,
} from "react";
import { getMe } from "../api/endpoints";

const AUTH_STORAGE_KEY = "auth_state_v1";

interface StoredAuthState {
    user: string;
    isAdmin: boolean;
}

interface AuthState {
    user: string | null;
    isAdmin: boolean;
    isAuthenticated: boolean;
    loading: boolean;
    refresh: () => Promise<void>;
}

const AuthContext = createContext<AuthState>({
    user: null,
    isAdmin: false,
    isAuthenticated: false,
    loading: true,
    refresh: async () => {},
});

export function AuthProvider({ children }: { children: ReactNode }) {
    const [user, setUser] = useState<string | null>(() => {
        try {
            const raw = localStorage.getItem(AUTH_STORAGE_KEY);
            if (!raw) return null;
            const parsed = JSON.parse(raw) as StoredAuthState;
            return parsed.user || null;
        } catch {
            return null;
        }
    });
    const [isAdmin, setIsAdmin] = useState(() => {
        try {
            const raw = localStorage.getItem(AUTH_STORAGE_KEY);
            if (!raw) return false;
            const parsed = JSON.parse(raw) as StoredAuthState;
            return !!parsed.isAdmin;
        } catch {
            return false;
        }
    });
    const [loading, setLoading] = useState(true);

    const setStoredAuth = (nextUser: string | null, nextIsAdmin: boolean) => {
        if (!nextUser) {
            localStorage.removeItem(AUTH_STORAGE_KEY);
            return;
        }
        localStorage.setItem(
            AUTH_STORAGE_KEY,
            JSON.stringify({ user: nextUser, isAdmin: nextIsAdmin }),
        );
    };

    const refresh = async () => {
        try {
            const data = await getMe();
            if (data && data.user) {
                setUser(data.user);
                setIsAdmin(data.admin ?? false);
                setStoredAuth(data.user, data.admin ?? false);
            } else {
                setUser(null);
                setIsAdmin(false);
                setStoredAuth(null, false);
            }
        } catch {
            setUser(null);
            setIsAdmin(false);
            setStoredAuth(null, false);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        refresh();
    }, []);

    useEffect(() => {
        const onStorage = (event: StorageEvent) => {
            if (event.key !== AUTH_STORAGE_KEY) return;
            if (!event.newValue) {
                setUser(null);
                setIsAdmin(false);
                return;
            }
            try {
                const parsed = JSON.parse(event.newValue) as StoredAuthState;
                setUser(parsed.user || null);
                setIsAdmin(!!parsed.isAdmin);
            } catch {
                setUser(null);
                setIsAdmin(false);
            }
        };

        window.addEventListener("storage", onStorage);
        return () => window.removeEventListener("storage", onStorage);
    }, []);

    return (
        <AuthContext.Provider
            value={{
                user,
                isAdmin,
                isAuthenticated: !!user,
                loading,
                refresh,
            }}
        >
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    return useContext(AuthContext);
}
