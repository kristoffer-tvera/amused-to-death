import React from "react";
import ReactDOM from "react-dom/client";
import { createBrowserRouter, RouterProvider } from "react-router-dom";
import "./index.css";
import ErrorPage from "./routes/errorPage.tsx";
import Root from "./routes/root.tsx";
import Profile from "./routes/profile.tsx";
import Home from "./routes/home.tsx";
import Raids from "./routes/raids.tsx";
import Characters from "./routes/characters.tsx";
import Applications from "./routes/applications.tsx";
import Auth from "./routes/auth.tsx";

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
                path: "/raids",
                element: <Raids />,
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
