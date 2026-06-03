import {
    Alert,
    Box,
    Button,
    Card,
    CardContent,
    CircularProgress,
    Divider,
    Link,
    TextField,
    Typography,
} from "@mui/material";
import { useEffect, useState } from "react";
import { useRoute } from "wouter";
import { getApp, processApplication } from "../api/endpoints";
import { useAuth } from "../context/AuthContext";

export default function AppView() {
    const [, params] = useRoute("/app/:id");
    const id = params?.id;
    const searchParams = new URLSearchParams(window.location.search);
    const authToken = searchParams.get("auth") || "";
    const { isAdmin } = useAuth();

    const [app, setApp] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [editing, setEditing] = useState(false);
    const [form, setForm] = useState<Record<string, string>>({});
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        if (id) {
            // setLoading(true);
            getApp(Number(id), authToken || undefined)
                .then((data) => {
                    setApp(data);
                    setForm(data || {});
                })
                .finally(() => setLoading(false));
        }
    }, [id, authToken]);

    const canEdit = isAdmin || (!!app && !!authToken);

    const handleSave = async () => {
        setSaving(true);
        await processApplication({
            id: String(id),
            auth: authToken,
            name: form.name || "",
            server: form.server || "",
            btag: form.btag || "",
            spec: form.spec || "",
            ui: form.ui || "",
            reason: form.reason || "",
            history: form.history || "",
            alts: form.alts || "",
            pepe: "meme",
            return: `/app/${id}`,
        });
        setSaving(false);
        setEditing(false);
        // Reload
        const data = await getApp(Number(id), authToken || undefined);
        setApp(data);
        setForm(data || {});
    };

    if (loading) {
        return (
            <Box sx={{ display: "flex", justifyContent: "center", py: 8 }}>
                <CircularProgress />
            </Box>
        );
    }

    if (!app) {
        return <Alert severity="error">Application not found.</Alert>;
    }

    const encodedName = encodeURIComponent(app.name?.toLowerCase() || "");
    const encodedRealm = encodeURIComponent(app.server?.toLowerCase() || "");

    if (editing && canEdit) {
        return (
            <Card sx={{ maxWidth: 700, mx: "auto", width: "100%" }}>
                <CardContent
                    sx={{ display: "flex", flexDirection: "column", gap: 2 }}
                >
                    <Typography variant="h5">Edit Application</Typography>
                    <TextField
                        label="Name"
                        value={form.name || ""}
                        onChange={(e) =>
                            setForm({ ...form, name: e.target.value })
                        }
                    />
                    <TextField
                        label="Server"
                        value={form.server || ""}
                        onChange={(e) =>
                            setForm({ ...form, server: e.target.value })
                        }
                    />
                    <TextField
                        label="Discord"
                        value={form.btag || ""}
                        onChange={(e) =>
                            setForm({ ...form, btag: e.target.value })
                        }
                    />
                    <TextField
                        label="Spec"
                        value={form.spec || ""}
                        onChange={(e) =>
                            setForm({ ...form, spec: e.target.value })
                        }
                    />
                    <TextField
                        label="UI Screenshot URL"
                        value={form.ui || ""}
                        onChange={(e) =>
                            setForm({ ...form, ui: e.target.value })
                        }
                    />
                    <TextField
                        label="Reason"
                        value={form.reason || ""}
                        onChange={(e) =>
                            setForm({ ...form, reason: e.target.value })
                        }
                        multiline
                        rows={4}
                    />
                    <TextField
                        label="History"
                        value={form.history || ""}
                        onChange={(e) =>
                            setForm({ ...form, history: e.target.value })
                        }
                        multiline
                        rows={4}
                    />
                    <TextField
                        label="Alts"
                        value={form.alts || ""}
                        onChange={(e) =>
                            setForm({ ...form, alts: e.target.value })
                        }
                        multiline
                        rows={3}
                    />
                    <Box sx={{ display: "flex", gap: 2 }}>
                        <Button
                            variant="contained"
                            onClick={handleSave}
                            disabled={saving}
                        >
                            {saving ? "Saving..." : "Save"}
                        </Button>
                        <Button onClick={() => setEditing(false)}>
                            Cancel
                        </Button>
                    </Box>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card sx={{ maxWidth: 700, mx: "auto", width: "100%" }}>
            <CardContent>
                <Box
                    sx={{
                        display: "flex",
                        justifyContent: "space-between",
                        alignItems: "center",
                        mb: 2,
                    }}
                >
                    <Typography variant="h5">
                        {app.name} — {app.server}
                    </Typography>
                    {canEdit && (
                        <Button
                            variant="outlined"
                            size="small"
                            onClick={() => setEditing(true)}
                        >
                            Edit
                        </Button>
                    )}
                </Box>

                <Box sx={{ display: "flex", gap: 2, mb: 2, flexWrap: "wrap" }}>
                    <Link
                        href={`https://www.warcraftlogs.com/character/eu/${encodedRealm}/${encodedName}`}
                        target="_blank"
                        rel="noopener"
                    >
                        WarcraftLogs
                    </Link>
                    <Link
                        href={`https://raider.io/characters/eu/${encodedRealm}/${encodedName}`}
                        target="_blank"
                        rel="noopener"
                    >
                        Raider.io
                    </Link>
                    <Link
                        href={`https://worldofwarcraft.blizzard.com/en-gb/character/eu/${encodedRealm}/${encodedName}`}
                        target="_blank"
                        rel="noopener"
                    >
                        Armory
                    </Link>
                    <Link
                        href={`https://www.wowprogress.com/character/eu/${encodedRealm}/${encodedName}`}
                        target="_blank"
                        rel="noopener"
                    >
                        WoWProgress
                    </Link>
                </Box>

                <Divider sx={{ my: 2 }} />

                <InfoRow label="Discord" value={app.btag} />
                <InfoRow label="Spec" value={app.spec} />
                <InfoRow label="UI Screenshot" value={app.ui} isLink />
                <Divider sx={{ my: 2 }} />
                <InfoRow
                    label="Reason for applying"
                    value={app.reason}
                    multiline
                />
                <InfoRow label="Guild History" value={app.history} multiline />
                <InfoRow label="Alts" value={app.alts} multiline />

                {app.change_date && (
                    <Typography
                        variant="caption"
                        color="text.secondary"
                        sx={{ mt: 2, display: "block" }}
                    >
                        Last updated: {app.change_date}
                    </Typography>
                )}
            </CardContent>
        </Card>
    );
}

function InfoRow({
    label,
    value,
    multiline,
    isLink,
}: {
    label: string;
    value?: string;
    multiline?: boolean;
    isLink?: boolean;
}) {
    if (!value) return null;
    return (
        <Box sx={{ mb: 1.5 }}>
            <Typography variant="caption" color="text.secondary">
                {label}
            </Typography>
            {isLink ? (
                <Box>
                    <Link
                        href={value}
                        target="_blank"
                        rel="noopener"
                        sx={{ wordBreak: "break-all" }}
                    >
                        {value}
                    </Link>
                    {/\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i.test(value) && (
                        <Box
                            component="img"
                            src={value}
                            alt="UI"
                            sx={{
                                mt: 1,
                                maxWidth: "100%",
                                maxHeight: 300,
                                borderRadius: 1,
                            }}
                        />
                    )}
                </Box>
            ) : (
                <Typography
                    variant="body2"
                    sx={{ whiteSpace: multiline ? "pre-wrap" : "normal" }}
                >
                    {value}
                </Typography>
            )}
        </Box>
    );
}
