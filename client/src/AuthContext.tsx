import { createContext, useContext, useEffect, useState, ReactNode } from "react";
import Constants from './Constants';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface AuthContextType {
    user: User | null;
    loading: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider = ({ children }: { children: ReactNode }) => {
    const [user, setUser] = useState<User | null>(null);
    const [loading, setLoading] = useState<boolean>(true);

    useEffect(() => {

        const fetchUser = () => {
            fetch(`${Constants.MAIN_API_URL}/api/user`,
                {
                    headers: { "Content-Type": "application/json" },
                    method: "GET",
                    credentials: "include"
                })
                .then((res) => res.json())
                .then((data) => {
                    if (data.user) {
                        setUser(data.user);
                    }
                })
                .catch(() => setUser(null))
                .finally(() => setLoading(false));
        }

        fetchUser();

    }, []);

    return (
        <AuthContext.Provider value={{ user, loading }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => {
    const context = useContext(AuthContext);

    if (!context) {
        throw new Error("useAuth must be used within an AuthProvider");
    }

    return context;
};

export default AuthContext;