import { Box, Button, Stack, Typography } from "@mui/material";
import OpenInNewIcon from "@mui/icons-material/OpenInNew";
import YouTubeIcon from "@mui/icons-material/YouTube";

const channelUrl = "https://www.youtube.com/channel/UC71iQLKzqkGCgG_emhFWySw";
const playlistUrl = "https://www.youtube.com/playlist?list=PLRms08srxYdk";
const playerUrl =
    "https://www.youtube-nocookie.com/embed/hyQa4o9o7s0?list=PLRms08srxYdk&rel=0";

export default function Videos() {
    return (
        <Box
            component="main"
            sx={{ width: "100%", maxWidth: 1000, mx: "auto" }}
        >
            <Stack
                direction={{ xs: "column", sm: "row" }}
                sx={{
                    mb: 3,
                    gap: 2,
                    justifyContent: "space-between",
                    alignItems: { xs: "flex-start", sm: "flex-end" },
                }}
            >
                <Box>
                    <Typography
                        variant="overline"
                        color="primary.main"
                        sx={{ display: "block" }}
                    >
                        Amused to Death · Media
                    </Typography>
                    <Typography variant="h4" component="h1">
                        Guild Videos
                    </Typography>
                    <Typography color="text.secondary" sx={{ mt: 1 }}>
                        Latest tier footage and moments from the guild.
                    </Typography>
                </Box>
                <Button
                    href={channelUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    variant="outlined"
                    startIcon={<YouTubeIcon />}
                    endIcon={<OpenInNewIcon />}
                >
                    Visit YouTube channel
                </Button>
            </Stack>

            <Box component="section" aria-labelledby="latest-tier-heading">
                <Stack
                    direction={{ xs: "column", sm: "row" }}
                    sx={{
                        mb: 1.5,
                        gap: 1,
                        justifyContent: "space-between",
                        alignItems: { xs: "flex-start", sm: "center" },
                    }}
                >
                    <Typography
                        id="latest-tier-heading"
                        variant="h6"
                        component="h2"
                    >
                        Latest Tier
                    </Typography>
                    <Button
                        href={playlistUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        size="small"
                        endIcon={<OpenInNewIcon />}
                    >
                        Open playlist
                    </Button>
                </Stack>

                <Box
                    sx={{
                        width: "100%",
                        aspectRatio: "16 / 9",
                        overflow: "hidden",
                        bgcolor: "common.black",
                        borderRadius: 1,
                    }}
                >
                    <iframe
                        title="Amused to Death latest tier playlist"
                        src={playerUrl}
                        loading="lazy"
                        referrerPolicy="strict-origin-when-cross-origin"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowFullScreen
                        style={{
                            display: "block",
                            width: "100%",
                            height: "100%",
                            border: 0,
                        }}
                    />
                </Box>
            </Box>
        </Box>
    );
}
