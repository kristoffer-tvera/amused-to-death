import { useState } from "react";
import { Card, CardContent, Typography, Button, Alert } from "@mui/material";
import AdminRoute from "../components/AdminRoute";

export default function Debug() {
    const [message, setMessage] = useState("");

    const handleDestroy = async () => {
        try {
            const res = await fetch(
                "/backend/actions/data.php?action=destroy_session",
                {
                    credentials: "same-origin",
                },
            );
            if (res.ok) {
                setMessage("Session destroyed. Refresh the page.");
            }
        } catch {
            setMessage("Failed to destroy session.");
        }
    };

    return (
        <AdminRoute>
            <Typography variant="h5">Debug</Typography>

            {message && (
                <Alert severity="info" onClose={() => setMessage("")}>
                    {message}
                </Alert>
            )}

            <Card>
                <CardContent>
                    <Typography variant="h6" gutterBottom>
                        Session Info
                    </Typography>
                    <Typography
                        variant="body2"
                        color="text.secondary"
                        gutterBottom
                    >
                        Session data is managed server-side. Use the button
                        below to destroy the current session.
                    </Typography>
                    <Button
                        variant="outlined"
                        color="error"
                        onClick={handleDestroy}
                        sx={{ mt: 1 }}
                    >
                        Destroy Session
                    </Button>
                </CardContent>
            </Card>
        </AdminRoute>
    );
}
