import {
    createContext,
    useContext,
    useEffect,
    useState,
    type ReactNode,
} from "react";
import { getMe } from "../api/endpoints";

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
    const [user, setUser] = useState<string | null>(null);
    const [isAdmin, setIsAdmin] = useState(false);
    const [loading, setLoading] = useState(true);

    const refresh = async () => {
        try {
            const data = await getMe();
            if (data && data.user) {
                setUser(data.user);
                setIsAdmin(data.admin ?? false);
            } else {
                setUser(null);
                setIsAdmin(false);
            }
        } catch {
            setUser(null);
            setIsAdmin(false);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        refresh();
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
