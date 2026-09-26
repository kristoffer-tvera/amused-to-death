import { useEffect, useState } from "react";
import {
    Card,
    CardContent,
    Typography,
    Button,
    Box,
    List,
    ListItem,
    ListItemText,
    LinearProgress,
    Alert,
} from "@mui/material";
import {
    getCharacters,
    updateCharacterFromBNet,
    purgeNonGuildCharacters,
    refreshAllFromBNet,
} from "../api/endpoints";
import AdminRoute from "../components/AdminRoute";

interface UpdateResult {
    id: number;
    name: string;
    success: boolean;
    ilvl?: number;
    error?: string;
}

export default function BattleNet() {
    const [characters, setCharacters] = useState<any[]>([]);
    const [updating, setUpdating] = useState(false);
    const [results, setResults] = useState<UpdateResult[]>([]);
    const [progress, setProgress] = useState(0);
    const [purging, setPurging] = useState(false);
    const [purgeMessage, setPurgeMessage] = useState<string>("");
    const [refreshingAll, setRefreshingAll] = useState(false);
    const [refreshAllMessage, setRefreshAllMessage] = useState<string>("");

    useEffect(() => {
        getCharacters().then(setCharacters);
    }, []);

    const handleUpdateAll = async () => {
        setUpdating(true);
        setResults([]);
        setProgress(0);

        for (let i = 0; i < characters.length; i++) {
            const char = characters[i];
            try {
                const res = await updateCharacterFromBNet(char.id);
                setResults((prev) => [
                    ...prev,
                    {
                        id: char.id,
                        name: char.name,
                        success: true,
                        ilvl: res?.ilvl,
                    },
                ]);
            } catch (e) {
                setResults((prev) => [
                    ...prev,
                    {
                        id: char.id,
                        name: char.name,
                        success: false,
                        error: String(e),
                    },
                ]);
            }
            setProgress(((i + 1) / characters.length) * 100);
            await new Promise((r) => setTimeout(r, 1000));
        }
        setUpdating(false);
    };

    const handleRefreshAll = async () => {
        setRefreshingAll(true);
        setRefreshAllMessage("");
        try {
            const res = await refreshAllFromBNet();
            if ("refreshed" in res) {
                if (res.systemic_failure) {
                    setRefreshAllMessage(
                        "No characters could be refreshed — likely a Battle.net outage. Nothing was hidden.",
                    );
                } else {
                    setRefreshAllMessage(
                        `Refreshed ${res.refreshed}, hid ${res.hidden} unreachable character(s).`,
                    );
                }
                getCharacters().then(setCharacters);
            } else {
                setRefreshAllMessage(res.error);
            }
        } catch {
            setRefreshAllMessage("Refresh failed. Please try again.");
        } finally {
            setRefreshingAll(false);
        }
    };

    const handlePurge = async () => {
        setPurging(true);
        setPurgeMessage("");
        try {
            const res = await purgeNonGuildCharacters();
            if ("hidden" in res) {
                setPurgeMessage(
                    `Hid ${res.hidden} character(s) no longer in the guild.`,
                );
                getCharacters().then(setCharacters);
            } else {
                setPurgeMessage(res.error);
            }
        } catch {
            setPurgeMessage("Purge failed. Please try again.");
        } finally {
            setPurging(false);
        }
    };

    return (
        <AdminRoute>
            <Typography variant="h5">Battle.net Integration</Typography>

            <Card>
                <CardContent>
                    <Box
                        sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 2,
                            mb: 2,
                            flexWrap: "wrap",
                        }}
                    >
                        <Typography variant="h6">
                            Update All Characters
                        </Typography>
                        <Button
                            variant="contained"
                            onClick={handleUpdateAll}
                            disabled={updating}
                        >
                            {updating
                                ? "Updating..."
                                : `Update ${characters.length} Characters`}
                        </Button>
                        <Button
                            variant="outlined"
                            color="warning"
                            onClick={handleRefreshAll}
                            disabled={refreshingAll}
                        >
                            {refreshingAll
                                ? "Refreshing..."
                                : "Refresh all & hide duds"}
                        </Button>
                    </Box>

                    {refreshAllMessage && (
                        <Alert severity="info" sx={{ mb: 2 }}>
                            {refreshAllMessage}
                        </Alert>
                    )}

                    {updating && (
                        <LinearProgress
                            variant="determinate"
                            value={progress}
                            sx={{ mb: 2 }}
                        />
                    )}

                    {results.length > 0 && (
                        <List dense sx={{ maxHeight: 400, overflow: "auto" }}>
                            {results.map((r) => (
                                <ListItem key={r.id}>
                                    <ListItemText
                                        primary={r.name}
                                        secondary={
                                            r.success ? `ilvl: ${r.ilvl}` : r.error
                                        }
                                        sx={{
                                            color: r.success
                                                ? "success.main"
                                                : "error.main",
                                        }}
                                    />
                                </ListItem>
                            ))}
                        </List>
                    )}
                </CardContent>
            </Card>

            <Card>
                <CardContent>
                    <Box
                        sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 2,
                            mb: 1,
                            flexWrap: "wrap",
                        }}
                    >
                        <Typography variant="h6">Guild Sync</Typography>
                        <Button
                            variant="outlined"
                            color="warning"
                            onClick={handlePurge}
                            disabled={purging}
                        >
                            {purging
                                ? "Checking roster..."
                                : "Hide characters no longer in guild"}
                        </Button>
                    </Box>
                    <Typography variant="body2" color="text.secondary">
                        Fetches the current guild roster and hides any guild
                        characters who have left. Characters are hidden, never
                        deleted, so raid history stays intact.
                    </Typography>
                    {purgeMessage && (
                        <Alert severity="info" sx={{ mt: 2 }}>
                            {purgeMessage}
                        </Alert>
                    )}
                </CardContent>
            </Card>
        </AdminRoute>
    );
}
