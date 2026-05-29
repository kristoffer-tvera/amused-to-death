import { Box, Tooltip } from "@mui/material";
import ShieldIcon from "@mui/icons-material/Shield";
import HealingIcon from "@mui/icons-material/Healing";
import FlashOnIcon from "@mui/icons-material/FlashOn";

interface RoleIconsProps {
    tank?: boolean;
    healer?: boolean;
    dps?: boolean;
    size?: number;
}

export default function RoleIcons({
    tank,
    healer,
    dps,
    size = 18,
}: RoleIconsProps) {
    return (
        <Box sx={{ display: "inline-flex", gap: 0.5, alignItems: "center" }}>
            {tank && (
                <Tooltip title="Tank">
                    <ShieldIcon sx={{ fontSize: size, color: "#4fc3f7" }} />
                </Tooltip>
            )}
            {healer && (
                <Tooltip title="Healer">
                    <HealingIcon sx={{ fontSize: size, color: "#81c784" }} />
                </Tooltip>
            )}
            {dps && (
                <Tooltip title="DPS">
                    <FlashOnIcon sx={{ fontSize: size, color: "#ffb74d" }} />
                </Tooltip>
            )}
        </Box>
    );
}
