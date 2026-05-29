import { type ReactNode } from "react";
import { useLocation } from "wouter";
import {
    AppBar,
    Toolbar,
    Typography,
    Button,
    Box,
    Container,
    IconButton,
    Drawer,
    List,
    ListItemButton,
    ListItemText,
    useMediaQuery,
    useTheme,
} from "@mui/material";
import MenuIcon from "@mui/icons-material/Menu";
import { useState, useEffect } from "react";
import { useAuth } from "../context/AuthContext";
import { getDiscordLoginUrl } from "../api/endpoints";

const navItems = [
    { label: "Home", path: "/", auth: false, admin: false },
    { label: "Raids", path: "/raids", auth: true, admin: false },
    { label: "Characters", path: "/characters", auth: true, admin: false },
    { label: "Applications", path: "/apps", auth: true, admin: false },
    { label: "Battle.net", path: "/bnet", auth: true, admin: true },
    { label: "Log", path: "/log", auth: true, admin: true },
    { label: "Apply", path: "/apply", auth: false, adminHide: true },
];

function UtcClock() {
    const [time, setTime] = useState(new Date());

    useEffect(() => {
        const interval = setInterval(() => setTime(new Date()), 1000);
        return () => clearInterval(interval);
    }, []);

    const pad = (n: number) => String(n).padStart(2, "0");
    const utc = `${pad(time.getUTCHours())}:${pad(time.getUTCMinutes())}:${pad(time.getUTCSeconds())}`;

    return (
        <Typography
            variant="body2"
            color="text.secondary"
            align="center"
            sx={{ py: 2 }}
        >
            UTC {utc} &mdash; &copy; {time.getUTCFullYear()} Amused to Death
        </Typography>
    );
}

export default function Layout({ children }: { children: ReactNode }) {
    const { user, isAdmin, isAuthenticated } = useAuth();
    const [location, navigate] = useLocation();
    const [drawerOpen, setDrawerOpen] = useState(false);
    const theme = useTheme();
    const isMobile = useMediaQuery(theme.breakpoints.down("md"));

    const visibleNavItems = navItems.filter((item) => {
        if (item.admin && !isAdmin) return false;
        if (item.auth && !isAuthenticated) return false;
        if ((item as { adminHide?: boolean }).adminHide && isAuthenticated)
            return false;
        return true;
    });

    return (
        <Box
            sx={{
                minHeight: "100vh",
                display: "flex",
                flexDirection: "column",
                background:
                    "url(https://i.imgur.com/AndS3U1.png) center / cover fixed",
            }}
        >
            <AppBar position="sticky" sx={{ bgcolor: "rgba(18,18,18,0.95)" }}>
                <Toolbar>
                    {isMobile && (
                        <IconButton
                            color="inherit"
                            edge="start"
                            onClick={() => setDrawerOpen(true)}
                            sx={{ mr: 1 }}
                        >
                            <MenuIcon />
                        </IconButton>
                    )}
                    <Typography
                        variant="h6"
                        sx={{ cursor: "pointer", fontWeight: 700, mr: 3 }}
                        onClick={() => navigate("/")}
                    >
                        Amused to Death
                    </Typography>

                    {!isMobile && (
                        <Box sx={{ display: "flex", gap: 1, flexGrow: 1 }}>
                            {visibleNavItems.map((item) => (
                                <Button
                                    key={item.path}
                                    color="inherit"
                                    onClick={() => navigate(item.path)}
                                    sx={{
                                        textTransform: "none",
                                        borderBottom:
                                            location === item.path
                                                ? "2px solid"
                                                : "2px solid transparent",
                                        borderRadius: 0,
                                    }}
                                >
                                    {item.label}
                                </Button>
                            ))}
                        </Box>
                    )}

                    <Box sx={{ ml: "auto" }}>
                        {isAuthenticated ? (
                            <Typography variant="body2" color="text.secondary">
                                Hello, {user}
                            </Typography>
                        ) : (
                            <Button
                                variant="outlined"
                                size="small"
                                color="inherit"
                                href={getDiscordLoginUrl()}
                            >
                                Login with Discord
                            </Button>
                        )}
                    </Box>
                </Toolbar>
            </AppBar>

            <Drawer open={drawerOpen} onClose={() => setDrawerOpen(false)}>
                <List sx={{ width: 240 }}>
                    {visibleNavItems.map((item) => (
                        <ListItemButton
                            key={item.path}
                            selected={location === item.path}
                            onClick={() => {
                                navigate(item.path);
                                setDrawerOpen(false);
                            }}
                        >
                            <ListItemText primary={item.label} />
                        </ListItemButton>
                    ))}
                </List>
            </Drawer>

            <Container
                maxWidth="lg"
                sx={{
                    flexGrow: 1,
                    py: 3,
                    display: "flex",
                    flexDirection: "column",
                    gap: 3,
                }}
            >
                {children}
            </Container>

            <UtcClock />
        </Box>
    );
}
