import { useEffect, useState } from "react";
import { useLocation } from "wouter";
import {
    Card,
    CardContent,
    Typography,
    Button,
    Box,
    Alert,
} from "@mui/material";
import { DataGrid, type GridColDef } from "@mui/x-data-grid";
import { useAuth } from "../context/AuthContext";
import { getMyCharacters, getDiscordLoginUrl } from "../api/endpoints";
import { getClassName } from "../data/classes";

const columns: GridColDef[] = [
    { field: "id", headerName: "ID", width: 70 },
    { field: "name", headerName: "Name", flex: 1 },
    { field: "main_name", headerName: "Main", flex: 1 },
    {
        field: "class",
        headerName: "Class",
        width: 130,
        valueGetter: (_value, row) => getClassName(row.class),
    },
    { field: "ilvl", headerName: "ilvl", width: 80 },
    { field: "realm", headerName: "Server", width: 140 },
    { field: "change_date", headerName: "Updated", width: 160 },
];

export default function Home() {
    const { isAuthenticated } = useAuth();
    const [characters, setCharacters] = useState<any[]>([]);
    const [loading, setLoading] = useState(false);
    const [, navigate] = useLocation();

    useEffect(() => {
        if (isAuthenticated) {
            setLoading(true);
            getMyCharacters()
                .then(setCharacters)
                .finally(() => setLoading(false));
        }
    }, [isAuthenticated]);

    if (!isAuthenticated) {
        return (
            <Card>
                <CardContent sx={{ textAlign: "center", py: 6 }}>
                    <Typography variant="h4" gutterBottom>
                        Welcome to Amused to Death
                    </Typography>
                    <Typography
                        variant="body1"
                        color="text.secondary"
                        sx={{ mb: 3 }}
                    >
                        A World of Warcraft guild management portal. Log in with
                        Discord to view your characters, raids, and more.
                    </Typography>
                    <Box
                        sx={{
                            display: "flex",
                            gap: 2,
                            justifyContent: "center",
                            flexWrap: "wrap",
                        }}
                    >
                        <Button variant="contained" href={getDiscordLoginUrl()}>
                            Login with Discord
                        </Button>
                        <Button
                            variant="outlined"
                            onClick={() => navigate("/apply")}
                        >
                            Apply to Guild
                        </Button>
                    </Box>
                </CardContent>
            </Card>
        );
    }

    return (
        <>
            <Typography variant="h5">My Characters</Typography>
            {characters.length === 0 && !loading && (
                <Alert severity="info">
                    You don't have any characters yet. Go to Characters to add
                    one.
                </Alert>
            )}
            <DataGrid
                rows={characters}
                columns={columns}
                loading={loading}
                pageSizeOptions={[25, 50]}
                initialState={{
                    pagination: { paginationModel: { pageSize: 25 } },
                }}
                autoHeight
                disableRowSelectionOnClick
                onRowClick={(params) => navigate(`/character/${params.row.id}`)}
                sx={{ bgcolor: "background.paper", cursor: "pointer" }}
            />
        </>
    );
}
