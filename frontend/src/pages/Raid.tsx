import {
    Alert,
    Box,
    Button,
    Card,
    CardContent,
    Checkbox,
    CircularProgress,
    Dialog,
    DialogActions,
    DialogContent,
    DialogTitle,
    Divider,
    FormControlLabel,
    Grid,
    Snackbar,
    TextField,
    Typography,
} from "@mui/material";
import { DataGrid, type GridColDef } from "@mui/x-data-grid";
import { useEffect, useState } from "react";
import { useLocation, useRoute } from "wouter";
import {
    addAllRaiders,
    addAttendance,
    addOrUpdateRaid,
    deleteAttendance,
    getAttendanceForRaid,
    getCharacters,
    getMyCharacters,
    getRaid,
    removeAttendeesWithNoBosses,
    setAllPaid,
    updateAttendance,
} from "../api/endpoints";
import ClassIcon from "../components/ClassIcon";
import ProtectedRoute from "../components/ProtectedRoute";
import { useAuth } from "../context/AuthContext";
import { getClassColor } from "../data/classes";

export default function Raid() {
    const [, params] = useRoute("/raid/:id");
    const id = params?.id;
    const isNew = id === "new";
    const { isAdmin } = useAuth();
    const [, navigate] = useLocation();

    const [raid, setRaid] = useState<any>(null);
    const [attendance, setAttendance] = useState<any[]>([]);
    const [loading, setLoading] = useState(!isNew);
    const [saving, setSaving] = useState(false);
    const [toast, setToast] = useState("");
    const [form, setForm] = useState({
        name: "",
        gold: "",
        comment: "",
        paid: false,
    });
    const [myCharacters, setMyCharacters] = useState<any[]>([]);
    const [allCharacters, setAllCharacters] = useState<any[]>([]);
    const [bulkDialog, setBulkDialog] = useState(false);
    const [bulkText, setBulkText] = useState("");
    const [rosterDialog, setRosterDialog] = useState(false);
    const [rosterText, setRosterText] = useState("");
    const [importing, setImporting] = useState(false);

    useEffect(() => {
        if (!isNew && id) {
            setLoading(true);
            Promise.all([getRaid(Number(id)), getAttendanceForRaid(Number(id))])
                .then(([r, att]) => {
                    setRaid(r);
                    setAttendance(att || []);
                    if (r) {
                        setForm({
                            name: r.name || "",
                            gold: r.gold ? String(r.gold) : "",
                            comment: r.comment || "",
                            paid: !!r.paid,
                        });
                    }
                })
                .finally(() => setLoading(false));
        }
        getMyCharacters().then(setMyCharacters);
        // Admins need the full character list to resolve pasted roster names
        // (from the /a2d raidlist addon output) to character ids.
        if (isAdmin) {
            getCharacters().then(setAllCharacters);
        }
    }, [id, isNew, isAdmin]);

    const reload = async () => {
        if (!id || isNew) return;
        const [r, att] = await Promise.all([
            getRaid(Number(id)),
            getAttendanceForRaid(Number(id)),
        ]);
        setRaid(r);
        setAttendance(att || []);
    };

    const handleSave = async () => {
        setSaving(true);
        const data: Record<string, string> = {
            name: form.name,
            gold: form.gold,
            comment: form.comment,
            paid: form.paid ? "1" : "0",
            return: "/raids",
        };
        if (!isNew) data.id = String(id);
        await addOrUpdateRaid(data);
        setSaving(false);
        if (isNew) navigate("/raids");
        else {
            setToast("Saved");
            reload();
        }
    };

    const handleAddAllRaiders = async () => {
        await addAllRaiders(Number(id));
        setToast("All raiders added");
        reload();
    };

    const handleRemoveZeroBosses = async () => {
        await removeAttendeesWithNoBosses(Number(id));
        setToast("Removed attendees with 0 bosses");
        reload();
    };

    const handleSetAllPaid = async () => {
        await setAllPaid(Number(id));
        setToast("All marked as paid");
        reload();
    };

    const handleDeleteAttendance = async (characterId: number) => {
        await deleteAttendance(characterId, Number(id));
        setToast("Removed");
        reload();
    };

    const handleAddMyCharacter = async (characterId: number) => {
        await addAttendance({
            character: String(characterId),
            raid: String(id),
            bosses: "0",
            return: "",
        });
        setToast("Added");
        reload();
    };

    // Roster import: takes the comma-separated names produced by the addon's
    // `/a2d raidlist` command and signs each matching character up for the raid.
    // Names already in the raid are skipped; unknown names are reported. Boss
    // counts are left at 0 (fill them in afterwards via Bulk Update).
    const handleImportRoster = async () => {
        setImporting(true);
        try {
            // Accept commas, newlines, or semicolons as separators; trim blanks.
            const names = rosterText
                .split(/[,\n;]+/)
                .map((n) => n.trim())
                .filter(Boolean);

            const seen = new Set<string>();
            let added = 0;
            const unknown: string[] = [];

            for (const name of names) {
                const key = name.toLowerCase();
                if (seen.has(key)) continue;
                seen.add(key);

                // Already signed up for this raid? Skip.
                if (
                    attendance.some(
                        (a) => a.character_name?.toLowerCase() === key,
                    )
                ) {
                    continue;
                }

                const match = allCharacters.find(
                    (c) => c.name?.toLowerCase() === key,
                );
                if (!match) {
                    unknown.push(name);
                    continue;
                }

                await addAttendance({
                    character: String(match.id),
                    raid: String(id),
                    bosses: "0",
                    return: "",
                });
                added += 1;
            }

            setRosterDialog(false);
            setRosterText("");
            await reload();
            setToast(
                unknown.length > 0
                    ? `Added ${added}. Unknown: ${unknown.join(", ")}`
                    : `Added ${added} character${added === 1 ? "" : "s"}`,
            );
        } finally {
            setImporting(false);
        }
    };

    const totalBosses = attendance.reduce(
        (sum, a) => sum + (Number(a.bosses) || 0),
        0,
    );
    const gold = Number(form.gold) || 0;

    // Payment string for the addon's `/a2d mail` command: "Name=Gold;Name=Gold;".
    // Each attendee's cut = floor(gold / totalBosses * bosses) — the same figure
    // shown in the "Cut" column. Alt cuts are folded onto their main so each
    // player is mailed once (the addon mails one recipient per name). Only
    // positive cuts are included.
    const paymentString = (() => {
        if (!gold || !totalBosses) return "";

        // Map every attendee's character id to its name, so an alt can resolve
        // its main's name when the main is also in the roster.
        const nameById = new Map<number, string>();
        for (const a of attendance) {
            nameById.set(a.characterId, a.character_name);
        }

        const cutByName = new Map<string, number>();
        for (const a of attendance) {
            const cut = Math.floor(
                (gold / totalBosses) * (Number(a.bosses) || 0),
            );
            if (cut <= 0) continue;

            // Fold onto the main's name when known; otherwise the alt's own name.
            const mainId =
                a.character_main && a.character_main !== a.characterId
                    ? a.character_main
                    : a.characterId;
            const payeeName = nameById.get(mainId) ?? a.character_name;

            cutByName.set(payeeName, (cutByName.get(payeeName) ?? 0) + cut);
        }

        return (
            [...cutByName.entries()]
                .map(([name, cut]) => `${name}=${cut}`)
                .join(";") + ";"
        );
    })();

    const attendanceColumns: GridColDef[] = [
        { field: "character_name", headerName: "Character", flex: 1 },
        { field: "added_date", headerName: "Signed Up", width: 140 },
        {
            field: "cut",
            headerName: "Cut",
            width: 100,
            valueGetter: (_value, row) => {
                if (!gold || !totalBosses) return 0;
                return Math.floor(
                    (gold / totalBosses) * (Number(row.bosses) || 0),
                );
            },
            valueFormatter: (value) =>
                value ? Number(value).toLocaleString() + "g" : "0g",
        },
        { field: "bosses", headerName: "Bosses", width: 80 },
        {
            field: "paid",
            headerName: "Paid",
            width: 70,
            valueGetter: (_value, row) => (row.paid ? "Yes" : "No"),
        },
        {
            field: "actions",
            headerName: "",
            width: 140,
            sortable: false,
            renderCell: (params) => (
                <Box sx={{ display: "flex", gap: 0.5 }}>
                    <Button
                        size="small"
                        onClick={() =>
                            handleDeleteAttendance(params.row.characterId)
                        }
                    >
                        Remove
                    </Button>
                </Box>
            ),
        },
    ];

    if (loading) {
        return (
            <ProtectedRoute>
                <Box sx={{ display: "flex", justifyContent: "center", py: 8 }}>
                    <CircularProgress />
                </Box>
            </ProtectedRoute>
        );
    }

    const myCharsNotInRaid = myCharacters.filter(
        (c) => !attendance.some((a) => a.characterId === c.id),
    );

    return (
        <ProtectedRoute>
            <Card>
                <CardContent
                    sx={{ display: "flex", flexDirection: "column", gap: 2 }}
                >
                    <Typography variant="h5">
                        {isNew ? "New Raid" : `Raid: ${raid?.name || ""}`}
                    </Typography>

                    <TextField
                        label="Name"
                        value={form.name}
                        onChange={(e) =>
                            setForm({ ...form, name: e.target.value })
                        }
                        required
                        disabled={!isAdmin && !isNew}
                    />
                    <TextField
                        label="Gold"
                        type="number"
                        value={form.gold}
                        onChange={(e) =>
                            setForm({ ...form, gold: e.target.value })
                        }
                        disabled={!isAdmin && !isNew}
                    />
                    <TextField
                        label="Comment"
                        value={form.comment}
                        onChange={(e) =>
                            setForm({ ...form, comment: e.target.value })
                        }
                        multiline
                        rows={2}
                        disabled={!isAdmin && !isNew}
                    />
                    <FormControlLabel
                        control={
                            <Checkbox
                                checked={form.paid}
                                onChange={(e) =>
                                    setForm({ ...form, paid: e.target.checked })
                                }
                                disabled={!isAdmin && !isNew}
                            />
                        }
                        label="Paid by community"
                    />

                    {(isAdmin || isNew) && (
                        <Box sx={{ display: "flex", gap: 2, flexWrap: "wrap" }}>
                            <Button
                                variant="contained"
                                onClick={handleSave}
                                disabled={saving}
                            >
                                {saving ? "Saving..." : "Save"}
                            </Button>
                        </Box>
                    )}

                    {isAdmin && !isNew && (
                        <>
                            <Divider sx={{ my: 1 }} />
                            <Typography variant="subtitle2">
                                Admin Tools
                            </Typography>
                            <Box
                                sx={{
                                    display: "flex",
                                    gap: 1,
                                    flexWrap: "wrap",
                                }}
                            >
                                <Button
                                    variant="outlined"
                                    size="small"
                                    onClick={handleAddAllRaiders}
                                >
                                    Add All Raiders
                                </Button>
                                <Button
                                    variant="outlined"
                                    size="small"
                                    onClick={handleRemoveZeroBosses}
                                >
                                    Remove 0-Boss Attendees
                                </Button>
                                <Button
                                    variant="outlined"
                                    size="small"
                                    onClick={handleSetAllPaid}
                                >
                                    Mark All Paid
                                </Button>
                                <Button
                                    variant="outlined"
                                    size="small"
                                    onClick={() => setBulkDialog(true)}
                                >
                                    Bulk Update
                                </Button>
                                <Button
                                    variant="outlined"
                                    size="small"
                                    onClick={() => {
                                        setRosterText("");
                                        setRosterDialog(true);
                                    }}
                                >
                                    Import Roster
                                </Button>
                            </Box>
                        </>
                    )}
                </CardContent>
            </Card>

            {isAdmin && !isNew && (
                <Card sx={{ mt: 2 }}>
                    <CardContent>
                        <Typography variant="subtitle2" gutterBottom>
                            Payment String (for the Amused2Death addon)
                        </Typography>
                        <Typography
                            variant="body2"
                            color="text.secondary"
                            sx={{ mb: 1 }}
                        >
                            Copy this and paste it into the game with
                            <code> /a2d mail</code>. Each player's cut is folded
                            onto their main. Requires a gold pot and at least one
                            boss.
                        </Typography>
                        <TextField
                            multiline
                            fullWidth
                            minRows={2}
                            maxRows={6}
                            value={
                                paymentString ||
                                "No payable cuts yet (set gold and boss counts)."
                            }
                            slotProps={{ input: { readOnly: true } }}
                            sx={{ mb: 1, fontFamily: "monospace" }}
                        />
                        <Button
                            variant="outlined"
                            size="small"
                            disabled={!paymentString}
                            onClick={async () => {
                                try {
                                    await navigator.clipboard.writeText(
                                        paymentString,
                                    );
                                    setToast("Payment string copied");
                                } catch {
                                    setToast("Copy failed — select and copy manually");
                                }
                            }}
                        >
                            Copy
                        </Button>
                    </CardContent>
                </Card>
            )}

            {!isNew && myCharsNotInRaid.length > 0 && (
                <Card sx={{ mt: 2 }}>
                    <CardContent>
                        <Typography variant="subtitle2" gutterBottom>
                            Add Your Character
                        </Typography>
                        <Box sx={{ display: "flex", gap: 1, flexWrap: "wrap" }}>
                            {myCharsNotInRaid.map((c) => (
                                <Button
                                    key={c.id}
                                    variant="outlined"
                                    size="small"
                                    onClick={() => handleAddMyCharacter(c.id)}
                                >
                                    {c.name}
                                </Button>
                            ))}
                        </Box>
                    </CardContent>
                </Card>
            )}

            {!isNew && attendance.length > 0 && (
                <>
                    <Typography variant="h6" sx={{ mt: 3 }}>
                        Attendance ({attendance.length})
                    </Typography>
                    <DataGrid
                        rows={attendance}
                        columns={attendanceColumns}
                        pageSizeOptions={[25, 50]}
                        initialState={{
                            pagination: { paginationModel: { pageSize: 25 } },
                        }}
                        autoHeight
                        disableRowSelectionOnClick
                        getRowId={(row) => `${row.raidId}-${row.characterId}`}
                        sx={{ bgcolor: "background.paper" }}
                    />
                </>
            )}

            {/* Player cards */}
            {!isNew && attendance.length > 0 && (
                <>
                    <Typography variant="h6" sx={{ mt: 3 }}>
                        Players
                    </Typography>
                    <Grid container spacing={1}>
                        {attendance
                            .filter(
                                (a) =>
                                    a.character_main === null ||
                                    a.character_main === a.characterId,
                            )
                            .map((a) => (
                                <Grid
                                    size={{ xs: 6, sm: 4, md: 3 }}
                                    key={a.characterId}
                                >
                                    <Card
                                        sx={{
                                            borderLeft: `3px solid ${getClassColor(a.character_class)}`,
                                            py: 0.5,
                                            px: 1,
                                        }}
                                    >
                                        <Box
                                            sx={{
                                                display: "flex",
                                                alignItems: "center",
                                                gap: 0.5,
                                            }}
                                        >
                                            <ClassIcon
                                                classId={a.character_class}
                                                size={16}
                                            />
                                            <Typography
                                                variant="body2"
                                                sx={{
                                                    color: getClassColor(
                                                        a.character_class,
                                                    ),
                                                }}
                                            >
                                                {a.character_name}
                                            </Typography>
                                        </Box>
                                    </Card>
                                </Grid>
                            ))}
                    </Grid>
                </>
            )}

            {/* Bulk update dialog */}
            <Dialog
                open={bulkDialog}
                onClose={() => setBulkDialog(false)}
                maxWidth="sm"
                fullWidth
            >
                <DialogTitle>Bulk Update Attendance</DialogTitle>
                <DialogContent>
                    <Typography
                        variant="body2"
                        color="text.secondary"
                        gutterBottom
                    >
                        Enter character names and boss count, one per line:
                        "CharacterName,BossCount"
                    </Typography>
                    <TextField
                        multiline
                        rows={8}
                        fullWidth
                        value={bulkText}
                        onChange={(e) => setBulkText(e.target.value)}
                        placeholder={"Charactername,5\nAnotherChar,3"}
                        sx={{ mt: 1 }}
                    />
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setBulkDialog(false)}>Cancel</Button>
                    <Button
                        variant="contained"
                        onClick={async () => {
                            const lines = bulkText
                                .split("\n")
                                .filter((l) => l.trim());
                            for (const line of lines) {
                                const [name, bosses] = line
                                    .split(",")
                                    .map((s) => s.trim());
                                const match = attendance.find(
                                    (a) =>
                                        a.character_name?.toLowerCase() ===
                                        name?.toLowerCase(),
                                );
                                if (match && bosses) {
                                    await updateAttendance({
                                        characterId: String(match.characterId),
                                        raidId: String(id),
                                        bosses,
                                        paid: match.paid ? "1" : "0",
                                    });
                                }
                            }
                            setBulkDialog(false);
                            setBulkText("");
                            setToast("Bulk update complete");
                            reload();
                        }}
                    >
                        Apply
                    </Button>
                </DialogActions>
            </Dialog>

            {/* Roster import dialog (from the addon's /a2d raidlist output) */}
            <Dialog
                open={rosterDialog}
                onClose={() => setRosterDialog(false)}
                maxWidth="sm"
                fullWidth
            >
                <DialogTitle>Import Raid Roster</DialogTitle>
                <DialogContent>
                    <Typography
                        variant="body2"
                        color="text.secondary"
                        gutterBottom
                    >
                        Paste the comma-separated names from the game's
                        <code> /a2d raidlist</code> command. Matching characters
                        are signed up for this raid with 0 bosses; set boss counts
                        afterwards with Bulk Update. Names already in the raid are
                        skipped, and unknown names are reported.
                    </Typography>
                    <TextField
                        multiline
                        rows={6}
                        fullWidth
                        value={rosterText}
                        onChange={(e) => setRosterText(e.target.value)}
                        placeholder={"Reen,Dumble,Boven,Vivon"}
                        sx={{ mt: 1 }}
                    />
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setRosterDialog(false)}>
                        Cancel
                    </Button>
                    <Button
                        variant="contained"
                        onClick={handleImportRoster}
                        disabled={importing || !rosterText.trim()}
                    >
                        {importing ? "Importing..." : "Import"}
                    </Button>
                </DialogActions>
            </Dialog>

            <Snackbar
                open={!!toast}
                autoHideDuration={3000}
                onClose={() => setToast("")}
                anchorOrigin={{ vertical: "bottom", horizontal: "center" }}
            >
                <Alert severity="success" onClose={() => setToast("")}>
                    {toast}
                </Alert>
            </Snackbar>
        </ProtectedRoute>
    );
}
