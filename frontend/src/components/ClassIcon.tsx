import { Box, Tooltip } from "@mui/material";
import { getClassById } from "../data/classes";

interface ClassIconProps {
    classId: number;
    size?: number;
}

export default function ClassIcon({ classId, size = 24 }: ClassIconProps) {
    const cls = getClassById(classId);
    return (
        <Tooltip title={cls.name}>
            <Box
                component="img"
                src={`/images/classes/${classId}.png`}
                alt={cls.name}
                sx={{ width: size, height: size, borderRadius: "4px" }}
                onError={(e) => {
                    (e.target as HTMLImageElement).style.display = "none";
                }}
            />
        </Tooltip>
    );
}
