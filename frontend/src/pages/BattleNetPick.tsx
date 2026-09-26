import { useEffect, useState } from "react";
import { useLocation } from "wouter";
import {
    Alert,
    Box,
    Button,
    Card,
    CardContent,
    Checkbox,
    Chip,
    CircularProgress,
    Divider,
    FormControlLabel,
    Radio,
    RadioGroup,
    Typography,
} from "@mui/material";
import ClassIcon from "../components/ClassIcon";
import { useAuth } from "../context/AuthContext";
import {
    getBattleNetLoginUrl,
    getBNetLoginCharacters,
    importBNetCharacters,
    type BNetCharacter,
    type BNetCharacterList,
} from "../api/endpoints";

// Stable key for a character within this picker.
const keyOf = (c: { name: string; realm: string }) => `${c.name}@${c.realm}`;

export default function BattleNetPick() {
    const [, navigate] = useLocation();
    const { refresh } = useAuth();

    const [data, setData] = useState<BNetCharacterList | null>(null);
    const [loading, setLoading] = useState(true);
    const [noLogin, setNoLogin] = useState(false);

    // Selection state: set of selected keys + which key is the main.
    const [selected, setSelected] = useState<Set<string>>(new Set());
    const [mainKey, setMainKey] = useState<string>("");
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState("");

    useEffect(() => {
        getBNetLoginCharacters()
            .then((res) => {
                if (!res) {
                    setNoLogin(true);
                    return;
                }
                setData(res);
                // Pre-select every guild character; default main to the first.
                const guildChars = res.characters.filter((c) => c.in_guild);
                const preselected = new Set(guildChars.map(keyOf));
                setSelected(preselected);
                if (guildChars.length > 0) setMainKey(keyOf(guildChars[0]));
            })
            .finally(() => setLoading(false));
    }, []);

    const toggle = (c: BNetCharacter) => {
        const k = keyOf(c);
        setSelected((prev) => {
            const next = new Set(prev);
            if (next.has(k)) {
                next.delete(k);
                if (mainKey === k) setMainKey("");
            } else {
                next.add(k);
                if (!mainKey) setMainKey(k);
            }
            return next;
        });
    };

    const handleImport = async () => {
        if (!data) return;
        setError("");

        const picks = data.characters
            .filter((c) => selected.has(keyOf(c)))
            .map((c) => ({ name: c.name, realm: c.realm }));

        if (picks.length === 0) {
            setError("Select at least one character.");
            return;
        }
        const mainIndex = picks.findIndex(
            (p) => keyOf(p) === mainKey,
        );
        if (mainIndex < 0) {
            setError("Choose which character is your main.");
            return;
        }

        setSubmitting(true);
        try {
            const res = await importBNetCharacters(picks, mainIndex);
            if (res.ok) {
                await refresh();
                navigate("/");
                return;
            }
            const body = await res.json().catch(() => null);
            setError(
                body?.error ??
                    "Import failed. Please try again.",
            );
        } catch {
            setError("Network error. Please try again.");
        } finally {
            setSubmitting(false);
        }
    };

    if (loading) {
        return (
            <Box sx={{ display: "flex", justifyContent: "center", py: 8 }}>
                <CircularProgress />
            </Box>
        );
    }

    if (noLogin) {
        return (
            <Card sx={{ maxWidth: 600, mx: "auto" }}>
                <CardContent sx={{ textAlign: "center", py: 6 }}>
                    <Typography variant="h5" gutterBottom>
                        No login in progress
                    </Typography>
                    <Typography color="text.secondary" sx={{ mb: 3 }}>
                        Start by logging in with Battle.net.
                    </Typography>
                    <Button variant="contained" href={getBattleNetLoginUrl()}>
                        Login with Battle.net
                    </Button>
                </CardContent>
            </Card>
        );
    }

    if (!data) return null;

    const hasGuildSelected = data.characters.some(
        (c) => selected.has(keyOf(c)) && c.in_guild,
    );

    return (
        <Card sx={{ maxWidth: 720, mx: "auto", width: "100%" }}>
            <CardContent>
                <Typography variant="h5" gutterBottom>
                    Choose your characters
                </Typography>
                <Typography color="text.secondary" sx={{ mb: 1 }}>
                    Logged in as <strong>{data.battle_tag}</strong>. Showing your
                    level {data.max_level} characters. Pick the ones to add to the
                    site and choose your main.
                </Typography>

                {!data.has_guild_character && (
                    <Alert severity="error" sx={{ my: 2 }}>
                        None of your max-level characters are in Amused to Death.
                        You need to be a guild member to continue.
                    </Alert>
                )}

                {error && (
                    <Alert severity="error" sx={{ my: 2 }}>
                        {error}
                    </Alert>
                )}

                <Divider sx={{ my: 2 }} />

                <RadioGroup
                    value={mainKey}
                    onChange={(e) => setMainKey(e.target.value)}
                >
                    {data.characters.map((c) => {
                        const k = keyOf(c);
                        const isSelected = selected.has(k);
                        return (
                            <Box
                                key={k}
                                sx={{
                                    display: "flex",
                                    alignItems: "center",
                                    gap: 1,
                                    py: 0.5,
                                    px: 1,
                                    my: 1,
                                    borderRadius: 1,
                                    bgcolor: isSelected
                                        ? "action.selected"
                                        : "transparent",
                                }}
                            >
                                <Checkbox
                                    checked={isSelected}
                                    onChange={() => toggle(c)}
                                />
                                <ClassIcon classId={c.class_id} size={22} />
                                <Typography sx={{ flex: 1 }}>
                                    {c.name}{" "}
                                    <Typography
                                        component="span"
                                        color="text.secondary"
                                    >
                                        — {c.realm}
                                    </Typography>
                                </Typography>
                                {c.in_guild ? (
                                    <Chip
                                        label="Amused to Death"
                                        color="success"
                                        size="small"
                                    />
                                ) : (
                                    <Chip
                                        label={c.guild ?? "No guild"}
                                        size="small"
                                        variant="outlined"
                                    />
                                )}
                                <FormControlLabel
                                    value={k}
                                    control={<Radio disabled={!isSelected} />}
                                    label="Main"
                                    sx={{ ml: 1, mr: 0 }}
                                />
                            </Box>
                        );
                    })}
                </RadioGroup>

                <Divider sx={{ my: 2 }} />

                <Box sx={{ display: "flex", gap: 2, alignItems: "center" }}>
                    <Button
                        variant="contained"
                        onClick={handleImport}
                        disabled={submitting || !hasGuildSelected}
                    >
                        {submitting ? "Importing..." : "Import & Continue"}
                    </Button>
                    {!hasGuildSelected && (
                        <Typography variant="caption" color="text.secondary">
                            Select at least one Amused to Death character.
                        </Typography>
                    )}
                </Box>
            </CardContent>
        </Card>
    );
}
