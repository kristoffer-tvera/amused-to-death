import { useEffect, useState } from "react";
import { Typography } from "@mui/material";
import { DataGrid, type GridColDef } from "@mui/x-data-grid";
import { getLog } from "../api/endpoints";
import AdminRoute from "../components/AdminRoute";

const columns: GridColDef[] = [
    { field: "user", headerName: "User", width: 150 },
    { field: "date", headerName: "Date/Time", width: 180 },
    { field: "query", headerName: "Command", flex: 1 },
];

export default function Log() {
    const [log, setLog] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        getLog()
            .then(setLog)
            .finally(() => setLoading(false));
    }, []);

    return (
        <AdminRoute>
            <Typography variant="h5">Activity Log</Typography>
            <DataGrid
                rows={log}
                columns={columns}
                loading={loading}
                pageSizeOptions={[25, 50, 100]}
                initialState={{
                    pagination: { paginationModel: { pageSize: 25 } },
                    sorting: { sortModel: [{ field: "date", sort: "desc" }] },
                }}
                autoHeight
                disableRowSelectionOnClick
                sx={{ bgcolor: "background.paper" }}
            />
        </AdminRoute>
    );
}
