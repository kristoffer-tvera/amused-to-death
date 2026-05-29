import { createTheme } from "@mui/material/styles";

const theme = createTheme({
    palette: {
        mode: "dark",
        primary: {
            main: "#90caf9",
        },
        secondary: {
            main: "#ce93d8",
        },
        background: {
            default: "#121212",
            paper: "rgba(30, 30, 30, 0.92)",
        },
    },
    components: {
        MuiCard: {
            styleOverrides: {
                root: {
                    backgroundImage: "none",
                },
            },
        },
        MuiAppBar: {
            styleOverrides: {
                root: {
                    backgroundImage: "none",
                },
            },
        },
    },
});

export default theme;
