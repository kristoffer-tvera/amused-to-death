import { Box, Tooltip, Link } from "@mui/material";

interface ExternalLinksProps {
    name: string;
    realm: string;
}

export default function ExternalLinks({ name, realm }: ExternalLinksProps) {
    const encodedName = encodeURIComponent(name.toLowerCase());
    const encodedRealm = encodeURIComponent(realm.toLowerCase());

    const links = [
        {
            label: "WarcraftLogs",
            url: `https://www.warcraftlogs.com/character/eu/${encodedRealm}/${encodedName}`,
            color: "#f48c06",
        },
        {
            label: "Raider.io",
            url: `https://raider.io/characters/eu/${encodedRealm}/${encodedName}`,
            color: "#e63946",
        },
        {
            label: "Armory",
            url: `https://worldofwarcraft.blizzard.com/en-gb/character/eu/${encodedRealm}/${encodedName}`,
            color: "#00b4d8",
        },
    ];

    return (
        <Box sx={{ display: "inline-flex", gap: 1 }}>
            {links.map((link) => (
                <Tooltip key={link.label} title={link.label}>
                    <Link
                        href={link.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        sx={{
                            fontSize: "0.7rem",
                            color: link.color,
                            textDecoration: "none",
                            "&:hover": { textDecoration: "underline" },
                        }}
                    >
                        {link.label.charAt(0)}
                    </Link>
                </Tooltip>
            ))}
        </Box>
    );
}
