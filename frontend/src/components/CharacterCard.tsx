import { Box, Card, CardContent, IconButton, Tooltip, Typography } from "@mui/material";
import VisibilityIcon from "@mui/icons-material/Visibility";
import VisibilityOffIcon from "@mui/icons-material/VisibilityOff";
import { getClassColor } from "../data/classes";
import ClassIcon from "./ClassIcon";
import RoleIcons from "./RoleIcons";
import ExternalLinks from "./ExternalLinks";

export interface CharacterCardData {
    id: number;
    name: string;
    class: number;
    ilvl: number;
    realm: string;
    hidden?: boolean;
    role_tank?: number | boolean;
    role_heal?: number | boolean;
    role_dps?: number | boolean;
}

interface CharacterCardProps {
    character: CharacterCardData;
    /** Called when the card body is clicked (e.g. navigate to the character). */
    onOpen?: () => void;
    /** When provided, renders an eye icon that toggles visibility. */
    onToggleVisibility?: () => void;
}

/**
 * A compact, class-coloured character card used across the site (Home, Characters,
 * alts). Optionally shows a visibility (eye) toggle; hidden characters are dimmed.
 */
export default function CharacterCard({
    character,
    onOpen,
    onToggleVisibility,
}: CharacterCardProps) {
    return (
        <Card
            sx={{
                borderLeft: `4px solid ${getClassColor(character.class)}`,
                cursor: onOpen ? "pointer" : "default",
                opacity: character.hidden ? 0.5 : 1,
                "&:hover": onOpen ? { bgcolor: "action.hover" } : undefined,
            }}
            onClick={onOpen}
        >
            <CardContent sx={{ py: 1.5, "&:last-child": { pb: 1.5 } }}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                    <ClassIcon classId={character.class} size={22} />
                    <Box sx={{ minWidth: 0 }}>
                        <Typography
                            variant="body1"
                            noWrap
                            sx={{
                                fontWeight: 600,
                                color: getClassColor(character.class),
                            }}
                        >
                            {character.name}
                        </Typography>
                        <Typography variant="caption" color="text.secondary">
                            {character.realm} · ilvl {character.ilvl}
                        </Typography>
                    </Box>

                    <Box
                        sx={{
                            ml: "auto",
                            display: "flex",
                            alignItems: "center",
                            gap: 0.5,
                        }}
                        onClick={(e) => e.stopPropagation()}
                    >
                        <RoleIcons
                            tank={!!character.role_tank}
                            healer={!!character.role_heal}
                            dps={!!character.role_dps}
                            size={16}
                        />
                        <ExternalLinks
                            name={character.name}
                            realm={character.realm}
                        />
                        {onToggleVisibility && (
                            <Tooltip
                                title={
                                    character.hidden
                                        ? "Hidden — click to show"
                                        : "Visible — click to hide"
                                }
                            >
                                <IconButton
                                    size="small"
                                    onClick={onToggleVisibility}
                                >
                                    {character.hidden ? (
                                        <VisibilityOffIcon fontSize="small" />
                                    ) : (
                                        <VisibilityIcon fontSize="small" />
                                    )}
                                </IconButton>
                            </Tooltip>
                        )}
                    </Box>
                </Box>
            </CardContent>
        </Card>
    );
}
