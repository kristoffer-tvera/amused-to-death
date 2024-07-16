import React from "react";
import ReactDOM from "react-dom/client";
import { createBrowserRouter, RouterProvider } from "react-router-dom";
import "./index.css";
import Applications from "./routes/applications.tsx";
import Auth from "./routes/auth.tsx";
import Characters from "./routes/characters.tsx";
import ErrorPage from "./routes/errorPage.tsx";
import Home from "./routes/home.tsx";
import Profile from "./routes/profile.tsx";
import { default as Raid, default as RaidPage } from "./routes/raidPage.tsx";
import Raids from "./routes/raids.tsx";
import Root from "./routes/root.tsx";

const router = createBrowserRouter([
    {
        path: "/",
        element: <Root />,
        errorElement: <ErrorPage />,
        children: [
            {
                path: "/",
                element: <Home />,
            },
            {
                path: "/auth",
                element: <Auth />,
            },
            {
                path: "/profile",
                element: <Profile />,
            },
            {
                path: "raids",
                children: [
                    {
                        index: true,
                        element: <Raids />,
                    },
                    {
                        path: "new",
                        element: <RaidPage />,
                    },
                    {
                        path: ":raidId",
                        element: <RaidPage />,
                    },
                ],
            },
            {
                path: "raids",
                element: <Raids />,
                children: [
                    {
                        path: "new",
                        element: <Raid />,
                    },
                    {
                        path: ":raidId",
                        element: <Raid />,
                    },
                ],
            },
            {
                path: "/characters",
                element: <Characters />,
            },
            {
                path: "/applications",
                element: <Applications />,
            },
        ],
    },
]);

ReactDOM.createRoot(document.getElementById("root")!).render(
    <React.StrictMode>
        <RouterProvider router={router} />
    </React.StrictMode>
);
