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
    localIp: string | null;
    isHomeNetwork: boolean;
    login: (email: string, password: string) => Promise<void>;
    logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider = ({ children }: { children: ReactNode }) => {
    const [user, setUser] = useState<User | null>(null);
    const [localIp, setLocalIp] = useState(null);
    const [isHomeNetwork, setIsHomeNetwork] = useState(false);
    const [loading, setLoading] = useState<boolean>(true);

    useEffect(() => {

        const fetchUser = () => {
            fetch(`${Constants.MAIN_API_URL}/user`,
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

                    setLocalIp(data.networkInfo.localIp);
                    setIsHomeNetwork(data.networkInfo.isHomeNetwork);
                })
                .catch(() => setUser(null))
                .finally(() => setLoading(false));
        }

        fetchUser();

    }, []);

    const login = async (name: string, password: string) => {
        const response = await fetch(`${Constants.MAIN_API_URL}/login`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
            },
            credentials: "include",
            body: JSON.stringify({ name, password }),
        });

        if (response.ok) {
            const data = await response.json();
            const userData: User = data.user;
            setUser(userData);
        } else {
            throw new Error("Login failed");
        }
    };

    const logout = async () => {
        await fetch(`${Constants.MAIN_API_URL}/logout`, {
            method: "POST",
            credentials: "include",
            headers: {
                "Content-Type": "application/json",
            },
        });

        setUser(null);
    };

    return (
        <AuthContext.Provider value={{ user, localIp, isHomeNetwork, loading, login, logout }}>
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