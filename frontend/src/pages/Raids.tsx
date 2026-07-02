import AddIcon from "@mui/icons-material/Add";
import { Box, Button, Typography } from "@mui/material";
import { DataGrid, type GridColDef } from "@mui/x-data-grid";
import { useEffect, useState } from "react";
import { useLocation } from "wouter";
import { getRaids } from "../api/endpoints";
import ProtectedRoute from "../components/ProtectedRoute";
import { useAuth } from "../context/AuthContext";

const columns: GridColDef[] = [
    { field: "id", headerName: "ID", width: 60 },
    { field: "name", headerName: "Name", flex: 1 },
    {
        field: "gold",
        headerName: "Gold",
        width: 100,
        valueFormatter: (value) =>
            value ? Number(value).toLocaleString() : "0",
    },
    { field: "added_date", headerName: "Created", width: 160 },
    { field: "change_date", headerName: "Updated", width: 160 },
];

export default function Raids() {
    const [raids, setRaids] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [, navigate] = useLocation();
    const { isAdmin } = useAuth();

    useEffect(() => {
        getRaids()
            .then(setRaids)
            .finally(() => setLoading(false));
    }, []);

    return (
        <ProtectedRoute>
            <Box
                sx={{
                    display: "flex",
                    justifyContent: "space-between",
                    alignItems: "center",
                }}
            >
                <Typography variant="h5">Raids</Typography>
                {isAdmin && (
                    <Button
                        variant="contained"
                        startIcon={<AddIcon />}
                        onClick={() => navigate("/raid/new")}
                    >
                        New Raid
                    </Button>
                )}
            </Box>
            <DataGrid
                rows={raids}
                columns={columns}
                loading={loading}
                pageSizeOptions={[25, 50]}
                initialState={{
                    pagination: { paginationModel: { pageSize: 25 } },
                    sorting: {
                        sortModel: [{ field: "create_date", sort: "desc" }],
                    },
                }}
                autoHeight
                disableRowSelectionOnClick
                onRowClick={(params) => navigate(`/raid/${params.row.id}`)}
                sx={{ bgcolor: "background.paper", cursor: "pointer" }}
            />
        </ProtectedRoute>
    );
}
