import { useEffect, useState } from "react";
import { useLocation } from "wouter";
import {
    Card,
    CardContent,
    Typography,
    Button,
    Box,
    Alert,
    Grid,
} from "@mui/material";
import { useAuth } from "../context/AuthContext";
import {
    getMyCharacters,
    getBattleNetLoginUrl,
    getBattleNetReimportUrl,
    setCharacterVisibility,
    type Character,
} from "../api/endpoints";
import CharacterCard from "../components/CharacterCard";

export default function Home() {
    const { isAuthenticated } = useAuth();
    const [characters, setCharacters] = useState<Character[]>([]);
    const [loading, setLoading] = useState(false);
    const [, navigate] = useLocation();

    useEffect(() => {
        if (isAuthenticated) {
            setLoading(true);
            getMyCharacters()
                .then(setCharacters)
                .finally(() => setLoading(false));
        }
    }, [isAuthenticated]);

    // Optimistically flip visibility, then persist. Revert on failure.
    const toggleVisibility = async (char: Character) => {
        const nextHidden = !char.hidden;
        setCharacters((prev) =>
            prev.map((c) =>
                c.id === char.id ? { ...c, hidden: nextHidden } : c,
            ),
        );
        try {
            await setCharacterVisibility(char.id, nextHidden);
        } catch {
            setCharacters((prev) =>
                prev.map((c) =>
                    c.id === char.id ? { ...c, hidden: char.hidden } : c,
                ),
            );
        }
    };

    if (!isAuthenticated) {
        return (
            <Card>
                <CardContent sx={{ textAlign: "center", py: 6 }}>
                    <Typography variant="h4" gutterBottom>
                        Welcome to Amused to Death
                    </Typography>
                    <Typography
                        variant="body1"
                        color="text.secondary"
                        sx={{ mb: 3 }}
                    >
                        A World of Warcraft guild management portal. Log in with
                        Battle.net to import your characters and get started.
                    </Typography>
                    <Box
                        sx={{
                            display: "flex",
                            gap: 2,
                            justifyContent: "center",
                            flexWrap: "wrap",
                        }}
                    >
                        <Button
                            variant="contained"
                            href={getBattleNetLoginUrl()}
                        >
                            Login with Battle.net
                        </Button>
                        <Button
                            variant="outlined"
                            onClick={() => navigate("/apply")}
                        >
                            Apply to Guild
                        </Button>
                    </Box>
                </CardContent>
            </Card>
        );
    }

    return (
        <>
            <Box
                sx={{
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "space-between",
                    mb: 1,
                    gap: 2,
                    flexWrap: "wrap",
                }}
            >
                <Typography variant="h5">My Characters</Typography>
                <Button
                    variant="outlined"
                    size="small"
                    href={getBattleNetReimportUrl()}
                >
                    Re-import from Battle.net
                </Button>
            </Box>

            {characters.length === 0 && !loading && (
                <Alert severity="info">
                    No characters here yet. Use "Re-import from Battle.net" to add
                    your characters.
                </Alert>
            )}

            <Grid container spacing={2}>
                {characters.map((char) => (
                    <Grid size={{ xs: 12, sm: 6, md: 4 }} key={char.id}>
                        <CharacterCard
                            character={char}
                            onToggleVisibility={() => toggleVisibility(char)}
                            onOpen={() => navigate(`/character/${char.id}`)}
                        />
                    </Grid>
                ))}
            </Grid>
        </>
    );
}
