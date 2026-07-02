import {
    Box,
    Button,
    Card,
    CardContent,
    Checkbox,
    CircularProgress,
    FormControl,
    FormControlLabel,
    InputLabel,
    MenuItem,
    Select,
    TextField,
    Typography,
} from "@mui/material";
import { DataGrid, type GridColDef } from "@mui/x-data-grid";
import { useEffect, useState } from "react";
import { useLocation, useRoute } from "wouter";
import {
    addOrUpdateCharacter,
    getAltsForCharacter,
    getAttendanceForCharacter,
    getCharacter,
    getCharacters,
    hideCharacter,
    updateCharacterFromBNet,
} from "../api/endpoints";
import ProtectedRoute from "../components/ProtectedRoute";
import RealmAutocomplete from "../components/RealmAutocomplete";
import { useAuth } from "../context/AuthContext";
import { wowClasses } from "../data/classes";

const raidColumns: GridColDef[] = [
    { field: "raid_name", headerName: "Raid", flex: 1 },
    { field: "bosses", headerName: "Bosses", width: 80 },
    {
        field: "paid",
        headerName: "Paid",
        width: 80,
        valueGetter: (_value, row) => (row.paid ? "Yes" : "No"),
    },
    { field: "create_date", headerName: "Date", width: 140 },
];

const altColumns: GridColDef[] = [
    { field: "name", headerName: "Name", flex: 1 },
    { field: "class_name", headerName: "Class", width: 120 },
    { field: "ilvl", headerName: "ilvl", width: 70 },
];

export default function Character() {
    const [, params] = useRoute("/character/:id");
    const id = params?.id;
    const isNew = id === "new";
    const { isAdmin } = useAuth();
    const [, navigate] = useLocation();

    const [character, setCharacter] = useState<any>(null);
    const [allCharacters, setAllCharacters] = useState<any[]>([]);
    const [raids, setRaids] = useState<any[]>([]);
    const [alts, setAlts] = useState<any[]>([]);
    const [loading, setLoading] = useState(!isNew);
    const [saving, setSaving] = useState(false);
    const [form, setForm] = useState<Record<string, any>>({
        name: "",
        realm: "",
        class: 0,
        main: -1,
        role_tank: false,
        role_heal: false,
        role_dps: false,
        raider: false,
        vip: false,
        discord: "",
    });

    useEffect(() => {
        getCharacters().then((chars) => setAllCharacters(chars || []));
    }, []);

    useEffect(() => {
        if (!isNew && id) {
            setLoading(true);
            Promise.all([
                getCharacter(Number(id)),
                getAttendanceForCharacter(Number(id)),
                getAltsForCharacter(Number(id)),
            ])
                .then(([char, att, al]) => {
                    setCharacter(char);
                    setRaids(att || []);
                    setAlts(al || []);
                    if (char) {
                        setForm({
                            name: char.name || "",
                            realm: char.realm || "",
                            class: char.class || 0,
                            main: char.main || -1,
                            role_tank: !!char.role_tank,
                            role_heal: !!char.role_heal,
                            role_dps: !!char.role_dps,
                            raider: !!char.raider,
                            vip: !!char.vip,
                            discord: char.discord || "",
                        });
                    }
                })
                .finally(() => setLoading(false));
        }
    }, [id, isNew]);

    const handleSave = async () => {
        setSaving(true);
        const data: Record<string, string> = {
            name: form.name,
            realm: form.realm,
            class: String(form.class),
            main: String(form.main),
            role_tank: form.role_tank ? "1" : "0",
            role_heal: form.role_heal ? "1" : "0",
            role_dps: form.role_dps ? "1" : "0",
            raider: form.raider ? "1" : "0",
            vip: form.vip ? "1" : "0",
            discord: form.discord,
            return: "/characters",
        };
        if (!isNew) data.id = String(id);
        await addOrUpdateCharacter(data);
        setSaving(false);
        navigate("/characters");
    };

    const handleHide = async () => {
        if (!id) return;
        await hideCharacter(Number(id));
        navigate("/characters");
    };

    const handleBNetUpdate = async () => {
        if (!id) return;
        const result = await updateCharacterFromBNet(Number(id));
        if (result && result.ilvl) {
            setForm((prev) => ({ ...prev }));
            // Reload character
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

    return (
        <ProtectedRoute>
            <Card>
                <CardContent
                    sx={{ display: "flex", flexDirection: "column", gap: 2 }}
                >
                    <Typography variant="h5">
                        {isNew
                            ? "New Character"
                            : `Edit: ${character?.name || ""}`}
                    </Typography>

                    {isAdmin && (
                        <TextField
                            label="Owner (Discord)"
                            value={form.discord}
                            onChange={(e) =>
                                setForm({ ...form, discord: e.target.value })
                            }
                        />
                    )}

                    <TextField
                        label="Name"
                        value={form.name}
                        onChange={(e) =>
                            setForm({ ...form, name: e.target.value })
                        }
                        required
                    />

                    <RealmAutocomplete
                        value={form.realm}
                        onChange={(v) => setForm({ ...form, realm: v })}
                    />

                    <FormControl>
                        <InputLabel>Class</InputLabel>
                        <Select
                            value={form.class}
                            label="Class"
                            onChange={(e) =>
                                setForm({ ...form, class: e.target.value })
                            }
                        >
                            {wowClasses.map((c) => (
                                <MenuItem key={c.id} value={c.id}>
                                    {c.name}
                                </MenuItem>
                            ))}
                        </Select>
                    </FormControl>

                    <FormControl>
                        <InputLabel>Main</InputLabel>
                        <Select
                            value={form.main}
                            label="Main"
                            onChange={(e) =>
                                setForm({
                                    ...form,
                                    main: Number(e.target.value),
                                })
                            }
                        >
                            <MenuItem value={-1}>
                                None (Main Character)
                            </MenuItem>
                            {allCharacters
                                .filter(
                                    (c) => !id || Number(c.id) !== Number(id),
                                )
                                .map((c) => (
                                    <MenuItem key={c.id} value={c.id}>
                                        {c.name}
                                        {c.realm ? ` - ${c.realm}` : ""}
                                    </MenuItem>
                                ))}
                        </Select>
                    </FormControl>

                    <Box sx={{ display: "flex", gap: 2, flexWrap: "wrap" }}>
                        <FormControlLabel
                            control={
                                <Checkbox
                                    checked={form.role_tank}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            role_tank: e.target.checked,
                                        })
                                    }
                                />
                            }
                            label="Tank"
                        />
                        <FormControlLabel
                            control={
                                <Checkbox
                                    checked={form.role_heal}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            role_heal: e.target.checked,
                                        })
                                    }
                                />
                            }
                            label="Healer"
                        />
                        <FormControlLabel
                            control={
                                <Checkbox
                                    checked={form.role_dps}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            role_dps: e.target.checked,
                                        })
                                    }
                                />
                            }
                            label="DPS"
                        />
                    </Box>

                    {isAdmin && (
                        <Box sx={{ display: "flex", gap: 2, flexWrap: "wrap" }}>
                            <FormControlLabel
                                control={
                                    <Checkbox
                                        checked={form.raider}
                                        onChange={(e) =>
                                            setForm({
                                                ...form,
                                                raider: e.target.checked,
                                            })
                                        }
                                    />
                                }
                                label="Raider"
                            />
                            <FormControlLabel
                                control={
                                    <Checkbox
                                        checked={form.vip}
                                        onChange={(e) =>
                                            setForm({
                                                ...form,
                                                vip: e.target.checked,
                                            })
                                        }
                                    />
                                }
                                label="VIP"
                            />
                        </Box>
                    )}

                    <Box sx={{ display: "flex", gap: 2, flexWrap: "wrap" }}>
                        <Button
                            variant="contained"
                            onClick={handleSave}
                            disabled={saving}
                        >
                            {saving ? "Saving..." : "Save"}
                        </Button>
                        {!isNew && isAdmin && (
                            <Button
                                variant="outlined"
                                color="error"
                                onClick={handleHide}
                            >
                                Hide Character
                            </Button>
                        )}
                        {!isNew && (
                            <Button
                                variant="outlined"
                                onClick={handleBNetUpdate}
                            >
                                Update from Battle.net
                            </Button>
                        )}
                    </Box>

                    {character && (
                        <Typography variant="caption" color="text.secondary">
                            Created: {character.create_date} | Updated:{" "}
                            {character.change_date}
                        </Typography>
                    )}
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
                    <Typography variant="h6" sx={{ mt: 3 }}>
                        Alts
                    </Typography>
                    <DataGrid
                        rows={alts}
                        columns={altColumns}
                        autoHeight
                        disableRowSelectionOnClick
                        pageSizeOptions={[10]}
                        onRowClick={(params) =>
                            navigate(`/character/${params.row.id}`)
                        }
                        sx={{ bgcolor: "background.paper", cursor: "pointer" }}
                    />
                </>
            )}
        </ProtectedRoute>
    );
}
