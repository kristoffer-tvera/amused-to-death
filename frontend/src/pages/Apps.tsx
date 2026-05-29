import { useEffect, useState } from "react";
import { useLocation } from "wouter";
import { Typography } from "@mui/material";
import { DataGrid, type GridColDef } from "@mui/x-data-grid";
import { getApps } from "../api/endpoints";
import ProtectedRoute from "../components/ProtectedRoute";

const columns: GridColDef[] = [
    { field: "id", headerName: "ID", width: 60 },
    { field: "name", headerName: "Name", flex: 1 },
    { field: "server", headerName: "Server", width: 150 },
    { field: "spec", headerName: "Spec", width: 160 },
    { field: "change_date", headerName: "Updated", width: 170 },
];

export default function Apps() {
    const [apps, setApps] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [, navigate] = useLocation();

    useEffect(() => {
        getApps()
            .then(setApps)
            .finally(() => setLoading(false));
    }, []);

    return (
        <ProtectedRoute>
            <Typography variant="h5">Applications</Typography>
            <DataGrid
                rows={apps}
                columns={columns}
                loading={loading}
                pageSizeOptions={[25, 50]}
                initialState={{
                    pagination: { paginationModel: { pageSize: 25 } },
                    sorting: {
                        sortModel: [{ field: "change_date", sort: "desc" }],
                    },
                }}
                autoHeight
                disableRowSelectionOnClick
                onRowClick={(params) => navigate(`/app/${params.row.id}`)}
                sx={{ bgcolor: "background.paper", cursor: "pointer" }}
            />
        </ProtectedRoute>
    );
}
