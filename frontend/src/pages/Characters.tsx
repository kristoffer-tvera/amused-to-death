import { useEffect, useState } from "react";
import { useLocation } from "wouter";
import {
    Typography,
    Box,
    Card,
    CardContent,
    Grid,
    Chip,
    Tooltip,
    Button,
} from "@mui/material";
import { DataGrid, type GridColDef } from "@mui/x-data-grid";
import AddIcon from "@mui/icons-material/Add";
import { getCharacters } from "../api/endpoints";
import { getClassName, getClassColor } from "../data/classes";
import ClassIcon from "../components/ClassIcon";
import RoleIcons from "../components/RoleIcons";
import ExternalLinks from "../components/ExternalLinks";
import ProtectedRoute from "../components/ProtectedRoute";

const columns: GridColDef[] = [
    { field: "id", headerName: "ID", width: 60 },
    { field: "name", headerName: "Name", flex: 1 },
    { field: "main_name", headerName: "Main", width: 120 },
    {
        field: "class",
        headerName: "Class",
        width: 130,
        valueGetter: (_value, row) => getClassName(row.class),
    },
    { field: "ilvl", headerName: "ilvl", width: 70 },
    { field: "added_date", headerName: "Created", width: 140 },
    { field: "change_date", headerName: "Updated", width: 140 },
];

interface Character {
    id: number;
    name: string;
    class: number;
    ilvl: number;
    realm: string;
    main: number | null;
    raider: number;
    role_tank: number;
    role_heal: number;
    role_dps: number;
    discord: string;
    [key: string]: any;
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

    const mains = characters.filter(
        (c) => !c.main || c.main === -1 || c.main === c.id,
    );
    const raiders = mains.filter((c) => c.raider);
    const socials = mains.filter((c) => !c.raider);
    const altsMap = new Map<number, Character[]>();
    characters.forEach((c) => {
        if (c.main && c.main !== c.id && c.main !== -1) {
            const arr = altsMap.get(c.main) || [];
            arr.push(c);
            altsMap.set(c.main, arr);
        }
    });

    return (
        <ProtectedRoute>
            <Box
                sx={{
                    display: "flex",
                    justifyContent: "space-between",
                    alignItems: "center",
                }}
            >
                <Typography variant="h5">Characters</Typography>
                <Button
                    variant="contained"
                    startIcon={<AddIcon />}
                    onClick={() => navigate("/character/new")}
                >
                    New Character
                </Button>
            </Box>

            <DataGrid
                rows={characters}
                columns={columns}
                loading={loading}
                pageSizeOptions={[25, 50]}
                initialState={{
                    pagination: { paginationModel: { pageSize: 25 } },
                }}
                autoHeight
                disableRowSelectionOnClick
                onRowClick={(params) => navigate(`/character/${params.row.id}`)}
                sx={{ bgcolor: "background.paper", cursor: "pointer" }}
            />

            {raiders.length > 0 && (
                <>
                    <Typography variant="h6" sx={{ mt: 3 }}>
                        Raiders
                    </Typography>
                    <Grid container spacing={2}>
                        {raiders.map((char) => (
                            <Grid size={{ xs: 12, md: 6, lg: 4 }} key={char.id}>
                                <PlayerCard
                                    character={char}
                                    alts={altsMap.get(char.id) || []}
                                    navigate={navigate}
                                />
                            </Grid>
                        ))}
                    </Grid>
                </>
            )}

            {socials.length > 0 && (
                <>
                    <Typography variant="h6" sx={{ mt: 3 }}>
                        Socials
                    </Typography>
                    <Grid container spacing={2}>
                        {socials.map((char) => (
                            <Grid size={{ xs: 12, md: 6, lg: 4 }} key={char.id}>
                                <PlayerCard
                                    character={char}
                                    alts={altsMap.get(char.id) || []}
                                    navigate={navigate}
                                />
                            </Grid>
                        ))}
                    </Grid>
                </>
            )}
        </ProtectedRoute>
    );
}

function PlayerCard({
    character,
    alts,
    navigate,
}: {
    character: Character;
    alts: Character[];
    navigate: (path: string) => void;
}) {
    return (
        <Card
            sx={{
                borderLeft: `4px solid ${getClassColor(character.class)}`,
                cursor: "pointer",
                "&:hover": { bgcolor: "action.hover" },
            }}
            onClick={() => navigate(`/character/${character.id}`)}
        >
            <CardContent sx={{ py: 1.5, "&:last-child": { pb: 1.5 } }}>
                <Box
                    sx={{
                        display: "flex",
                        alignItems: "center",
                        gap: 1,
                        mb: 0.5,
                    }}
                >
                    <ClassIcon classId={character.class} size={20} />
                    <Tooltip title={`ilvl ${character.ilvl}`}>
                        <Typography
                            variant="body1"
                            sx={{
                                fontWeight: 600,
                                color: getClassColor(character.class),
                            }}
                        >
                            {character.name}
                        </Typography>
                    </Tooltip>
                    <RoleIcons
                        tank={!!character.role_tank}
                        healer={!!character.role_heal}
                        dps={!!character.role_dps}
                        size={16}
                    />
                    <Box sx={{ ml: "auto" }}>
                        <ExternalLinks
                            name={character.name}
                            realm={character.realm}
                        />
                    </Box>
                </Box>

                {alts.length > 0 && (
                    <Box sx={{ pl: 3, mt: 0.5 }}>
                        {alts.map((alt) => (
                            <Box
                                key={alt.id}
                                sx={{
                                    display: "flex",
                                    alignItems: "center",
                                    gap: 0.5,
                                    mb: 0.25,
                                }}
                            >
                                <ClassIcon classId={alt.class} size={14} />
                                <Typography
                                    variant="body2"
                                    sx={{
                                        color: getClassColor(alt.class),
                                        fontSize: "0.8rem",
                                    }}
                                >
                                    {alt.name}
                                </Typography>
                                <Chip
                                    label={`${alt.ilvl}`}
                                    size="small"
                                    sx={{ height: 16, fontSize: "0.65rem" }}
                                />
                            </Box>
                        ))}
                    </Box>
                )}
            </CardContent>
        </Card>
    );
}
