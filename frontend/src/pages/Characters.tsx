import { useEffect, useState } from "react";
import { useLocation } from "wouter";
import { Typography, Grid, Divider } from "@mui/material";
import {
    getCharacters,
    getHiddenCharacters,
    setCharacterVisibility,
    type Character,
} from "../api/endpoints";
import { useAuth } from "../context/AuthContext";
import CharacterCard from "../components/CharacterCard";
import ProtectedRoute from "../components/ProtectedRoute";

// Fetches the visible list plus, for admins, the hidden list. Non-admins can't
// hit /characters/hidden (403), so it's only requested when isAdmin.
async function fetchAll(
    isAdmin: boolean,
): Promise<{ visible: Character[]; hidden: Character[] }> {
    const [visible, hidden] = await Promise.all([
        getCharacters(),
        isAdmin ? getHiddenCharacters() : Promise.resolve([] as Character[]),
    ]);
    return { visible, hidden };
}

export default function Characters() {
    const { isAdmin } = useAuth();
    const [characters, setCharacters] = useState<Character[]>([]);
    // Hidden characters are admin-only; empty for everyone else.
    const [hidden, setHidden] = useState<Character[]>([]);
    const [loading, setLoading] = useState(true);
    const [, navigate] = useLocation();

    // `loading` starts true (see useState) so we never set it synchronously in
    // the effect — matching the rest of the app and satisfying the react-hooks
    // rule against cascading renders. We only flip it off when data arrives.
    useEffect(() => {
        let ignore = false;
        fetchAll(isAdmin)
            .then(({ visible, hidden }) => {
                if (ignore) return;
                setCharacters(visible);
                setHidden(hidden);
            })
            .finally(() => {
                if (!ignore) setLoading(false);
            });
        return () => {
            ignore = true;
        };
    }, [isAdmin]);

    // Un-hide a character, then refresh both lists so it moves from the Hidden
    // group into the main list.
    const handleUnhide = async (id: number) => {
        await setCharacterVisibility(id, false);
        const { visible, hidden } = await fetchAll(isAdmin);
        setCharacters(visible);
        setHidden(hidden);
    };

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

            {/* Admin-only: characters that have been hidden (roster sync,
                manual hides, ownerless leftovers). Shown as their own group so
                an admin can bring any of them back with the eye toggle. */}
            {isAdmin && hidden.length > 0 && (
                <>
                    <Divider sx={{ my: 3 }} />
                    <Typography variant="h6" sx={{ mb: 1 }}>
                        Hidden characters
                    </Typography>
                    <Typography color="text.secondary" sx={{ mb: 2 }}>
                        Only admins can see these. Click the eye icon to make a
                        character visible again.
                    </Typography>
                    <Grid container spacing={2}>
                        {hidden.map((char) => (
                            <Grid size={{ xs: 12, sm: 6, md: 4 }} key={char.id}>
                                <CharacterCard
                                    character={{ ...char, hidden: true }}
                                    onOpen={() =>
                                        navigate(`/character/${char.id}`)
                                    }
                                    onToggleVisibility={() =>
                                        handleUnhide(char.id)
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
