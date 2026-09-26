import { useEffect, useState } from "react";
import { useLocation } from "wouter";
import { Typography, Grid } from "@mui/material";
import { getCharacters } from "../api/endpoints";
import CharacterCard from "../components/CharacterCard";
import ProtectedRoute from "../components/ProtectedRoute";

interface Character {
    id: number;
    name: string;
    class: number;
    ilvl: number;
    realm: string;
    main: number | null;
    role_tank: number | boolean;
    role_heal: number | boolean;
    role_dps: number | boolean;
    [key: string]: unknown;
}

export default function Characters() {
    const [characters, setCharacters] = useState<Character[]>([]);
    const [loading, setLoading] = useState(true);
    const [, navigate] = useLocation();

    useEffect(() => {
        getCharacters()
            .then(setCharacters)
            .finally(() => setLoading(false));
    }, []);

    // Show mains (characters that are their own main or have no main set).
    const mains = characters.filter(
        (c) => !c.main || c.main === -1 || c.main === c.id,
    );

    return (
        <ProtectedRoute>
            <Typography variant="h5" sx={{ mb: 1 }}>
                Characters
            </Typography>

            {loading && (
                <Typography color="text.secondary">Loading...</Typography>
            )}

            <Grid container spacing={2}>
                {mains.map((char) => (
                    <Grid size={{ xs: 12, sm: 6, md: 4 }} key={char.id}>
                        <CharacterCard
                            character={char}
                            onOpen={() => navigate(`/character/${char.id}`)}
                        />
                    </Grid>
                ))}
            </Grid>
        </ProtectedRoute>
    );
}
