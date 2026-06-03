import { type ReactNode } from "react";
import { Redirect } from "wouter";
import { useAuth } from "../context/AuthContext";
import { Box, CircularProgress } from "@mui/material";

export default function ProtectedRoute({ children }: { children: ReactNode }) {
    const { isAuthenticated, loading } = useAuth();

    if (loading) {
        return (
            <Box sx={{ display: "flex", justifyContent: "center", py: 8 }}>
                <CircularProgress />
            </Box>
        );
    }

    if (!isAuthenticated) {
        return <Redirect to="/" />;
    }

    return <>{children}</>;
}
