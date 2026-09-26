import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";

// In dev, the SPA runs on Vite (http://localhost:5173) and the .NET API runs on
// http://localhost:5191 (the "http" launch profile). Proxy /api to the backend
// so same-origin cookies (a2d_session) work without CORS or cross-site issues.
export default defineConfig({
    plugins: [react()],
    server: {
        proxy: {
            "/api": {
                target: "http://localhost:5191",
                changeOrigin: false,
            },
        },
    },
});
