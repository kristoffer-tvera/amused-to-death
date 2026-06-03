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
    Link,
} from "@mui/material";
import {
    getBNetTokenStatus,
    getBNetTokenUrl,
    getCharacters,
    updateCharacterFromBNet,
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
    const [tokenStatus, setTokenStatus] = useState<any>(null);
    const [characters, setCharacters] = useState<any[]>([]);
    const [updating, setUpdating] = useState(false);
    const [results, setResults] = useState<UpdateResult[]>([]);
    const [progress, setProgress] = useState(0);

    useEffect(() => {
        getBNetTokenStatus().then(setTokenStatus);
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
            // 1s delay
            await new Promise((r) => setTimeout(r, 1000));
        }
        setUpdating(false);
    };

    const hasToken = tokenStatus && tokenStatus.remaining > 0;

    return (
        <AdminRoute>
            <Typography variant="h5">Battle.net Integration</Typography>

            <Card>
                <CardContent>
                    <Typography variant="h6" gutterBottom>
                        Token Status
                    </Typography>
                    {tokenStatus ? (
                        hasToken ? (
                            <Alert severity="success">
                                Token active — {tokenStatus.remaining}s
                                remaining
                            </Alert>
                        ) : (
                            <Alert severity="warning">
                                No valid token.{" "}
                                <Link href={getBNetTokenUrl()}>
                                    Click here to get a new token
                                </Link>
                            </Alert>
                        )
                    ) : (
                        <Typography color="text.secondary">
                            Loading...
                        </Typography>
                    )}
                </CardContent>
            </Card>

            {hasToken && (
                <Card>
                    <CardContent>
                        <Box
                            sx={{
                                display: "flex",
                                alignItems: "center",
                                gap: 2,
                                mb: 2,
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
                        </Box>

                        {updating && (
                            <LinearProgress
                                variant="determinate"
                                value={progress}
                                sx={{ mb: 2 }}
                            />
                        )}

                        {results.length > 0 && (
                            <List
                                dense
                                sx={{ maxHeight: 400, overflow: "auto" }}
                            >
                                {results.map((r) => (
                                    <ListItem key={r.id}>
                                        <ListItemText
                                            primary={r.name}
                                            secondary={
                                                r.success
                                                    ? `ilvl: ${r.ilvl}`
                                                    : r.error
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
            )}
        </AdminRoute>
    );
}
