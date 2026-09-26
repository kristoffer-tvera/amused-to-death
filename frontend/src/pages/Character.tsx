import {
    Box,
    Button,
    Card,
    CardContent,
    Chip,
    CircularProgress,
    FormControlLabel,
    Grid,
    Switch,
    Typography,
} from "@mui/material";
import VisibilityIcon from "@mui/icons-material/Visibility";
import VisibilityOffIcon from "@mui/icons-material/VisibilityOff";
import { DataGrid, type GridColDef } from "@mui/x-data-grid";
import { useEffect, useState } from "react";
import { useLocation, useRoute } from "wouter";
import {
    getAltsForCharacter,
    getAttendanceForCharacter,
    getCharacter,
    getMyCharacters,
    setCharacterRoles,
    setCharacterVisibility,
    updateCharacterFromBNet,
} from "../api/endpoints";
import CharacterCard from "../components/CharacterCard";
import ClassIcon from "../components/ClassIcon";
import ExternalLinks from "../components/ExternalLinks";
import ProtectedRoute from "../components/ProtectedRoute";
import { useAuth } from "../context/AuthContext";
import { getClassColor, getClassName } from "../data/classes";

const raidColumns: GridColDef[] = [
    { field: "name", headerName: "Raid", flex: 1 },
    { field: "bosses", headerName: "Bosses", width: 80 },
    {
        field: "paid",
        headerName: "Paid",
        width: 80,
        valueGetter: (_value, row) => (row.paid ? "Yes" : "No"),
    },
    { field: "added_date", headerName: "Date", width: 140 },
];

export default function Character() {
    const [, params] = useRoute("/character/:id");
    const id = params?.id;
    const { isAdmin } = useAuth();
    const [, navigate] = useLocation();

    const [character, setCharacter] = useState<any>(null);
    const [raids, setRaids] = useState<any[]>([]);
    const [alts, setAlts] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [isMine, setIsMine] = useState(false);

    // Local role state so the toggles feel instant; persisted on change.
    const [roles, setRoles] = useState({
        role_tank: false,
        role_heal: false,
        role_dps: false,
    });

    useEffect(() => {
        if (!id) return;
        setLoading(true);
        Promise.all([
            getCharacter(Number(id)),
            getAttendanceForCharacter(Number(id)),
            getAltsForCharacter(Number(id)),
            getMyCharacters().catch(() => []),
        ])
            .then(([char, att, al, mine]) => {
                setCharacter(char);
                setRaids(att || []);
                setAlts(al || []);
                if (char) {
                    setRoles({
                        role_tank: !!char.role_tank,
                        role_heal: !!char.role_heal,
                        role_dps: !!char.role_dps,
                    });
                }
                setIsMine(
                    Array.isArray(mine) &&
                        mine.some((c: any) => c.id === Number(id)),
                );
            })
            .finally(() => setLoading(false));
    }, [id]);

    const canEditRoles = isMine || isAdmin;

    const toggleRole = async (role: keyof typeof roles) => {
        const next = { ...roles, [role]: !roles[role] };
        setRoles(next);
        try {
            await setCharacterRoles(Number(id), next);
        } catch {
            setRoles(roles); // revert on failure
        }
    };

    const toggleVisibility = async () => {
        if (!character) return;
        const next = !character.hidden;
        setCharacter({ ...character, hidden: next });
        try {
            await setCharacterVisibility(Number(id), next);
        } catch {
            setCharacter({ ...character, hidden: character.hidden });
        }
    };

    const handleBNetUpdate = async () => {
        if (!id) return;
        const result = await updateCharacterFromBNet(Number(id));
        if (result && typeof result.ilvl === "number") {
            const char = await getCharacter(Number(id));
            setCharacter(char);
        }
    };

    if (loading) {
        return (
            <ProtectedRoute>
                <Box sx={{ display: "flex", justifyContent: "center", py: 8 }}>
                    <CircularProgress />
                </Box>
            </ProtectedRoute>
        );
    }

    if (!character) {
        return (
            <ProtectedRoute>
                <Typography sx={{ py: 4 }}>Character not found.</Typography>
            </ProtectedRoute>
        );
    }

    return (
        <ProtectedRoute>
            <Card
                sx={{
                    borderLeft: `4px solid ${getClassColor(character.class)}`,
                }}
            >
                <CardContent
                    sx={{ display: "flex", flexDirection: "column", gap: 2 }}
                >
                    {/* Header: identity (read-only, Blizzard-sourced) */}
                    <Box sx={{ display: "flex", alignItems: "center", gap: 1.5 }}>
                        <ClassIcon classId={character.class} size={32} />
                        <Box sx={{ flex: 1, minWidth: 0 }}>
                            <Typography
                                variant="h5"
                                sx={{ color: getClassColor(character.class) }}
                            >
                                {character.name}
                            </Typography>
                            <Typography
                                variant="body2"
                                color="text.secondary"
                            >
                                {getClassName(character.class)} ·{" "}
                                {character.realm} · ilvl {character.ilvl}
                            </Typography>
                        </Box>
                        {character.hidden && (
                            <Chip label="Hidden" size="small" />
                        )}
                        <ExternalLinks
                            name={character.name}
                            realm={character.realm}
                        />
                    </Box>

                    {/* Roles — the only editable data, for owner or admin */}
                    <Box>
                        <Typography variant="subtitle2" gutterBottom>
                            Roles
                        </Typography>
                        <Box sx={{ display: "flex", gap: 2, flexWrap: "wrap" }}>
                            <FormControlLabel
                                control={
                                    <Switch
                                        checked={roles.role_tank}
                                        disabled={!canEditRoles}
                                        onChange={() => toggleRole("role_tank")}
                                    />
                                }
                                label="Tank"
                            />
                            <FormControlLabel
                                control={
                                    <Switch
                                        checked={roles.role_heal}
                                        disabled={!canEditRoles}
                                        onChange={() => toggleRole("role_heal")}
                                    />
                                }
                                label="Healer"
                            />
                            <FormControlLabel
                                control={
                                    <Switch
                                        checked={roles.role_dps}
                                        disabled={!canEditRoles}
                                        onChange={() => toggleRole("role_dps")}
                                    />
                                }
                                label="DPS"
                            />
                        </Box>
                    </Box>

                    {/* Owner/admin actions */}
                    {(isMine || isAdmin) && (
                        <Box sx={{ display: "flex", gap: 2, flexWrap: "wrap" }}>
                            <Button
                                variant="outlined"
                                startIcon={
                                    character.hidden ? (
                                        <VisibilityIcon />
                                    ) : (
                                        <VisibilityOffIcon />
                                    )
                                }
                                onClick={toggleVisibility}
                            >
                                {character.hidden ? "Show" : "Hide"}
                            </Button>
                            {isAdmin && (
                                <Button
                                    variant="outlined"
                                    onClick={handleBNetUpdate}
                                >
                                    Refresh from Battle.net
                                </Button>
                            )}
                        </Box>
                    )}

                    <Typography variant="caption" color="text.secondary">
                        Character data is synced from Battle.net. Change your main
                        via "Re-import from Battle.net" on the home page.
                    </Typography>
                </CardContent>
            </Card>

            {raids.length > 0 && (
                <>
                    <Typography variant="h6" sx={{ mt: 3 }}>
                        Raids Attended
                    </Typography>
                    <DataGrid
                        rows={raids}
                        columns={raidColumns}
                        pageSizeOptions={[10, 25]}
                        initialState={{
                            pagination: { paginationModel: { pageSize: 10 } },
                        }}
                        autoHeight
                        disableRowSelectionOnClick
                        getRowId={(row) => `${row.raidId}-${row.characterId}`}
                        onRowClick={(params) =>
                            navigate(`/raid/${params.row.raidId}`)
                        }
                        sx={{ bgcolor: "background.paper", cursor: "pointer" }}
                    />
                </>
            )}

            {alts.length > 0 && (
                <>
                    <Typography variant="h6" sx={{ mt: 3, mb: 1 }}>
                        Alts
                    </Typography>
                    <Grid container spacing={2}>
                        {alts.map((alt) => (
                            <Grid size={{ xs: 12, sm: 6, md: 4 }} key={alt.id}>
                                <CharacterCard
                                    character={alt}
                                    onOpen={() =>
                                        navigate(`/character/${alt.id}`)
                                    }
                                />
                            </Grid>
                        ))}
                    </Grid>
                </>
            )}
        </ProtectedRoute>
    );
}
