import React, { useEffect, useState } from "react";
import { Outlet } from "react-router-dom";
import Header from "../components/header";
import Footer from "../components/footer";
import { User } from "../types/user";
import { parseJwt } from "../util/jwt";

const RootRoute: React.FC = () => {
    const [user, setUser] = useState<User>();

    useEffect(() => {
        let jwtString = localStorage.getItem("user");
        if (jwtString) {
            let parsedJwt = parseJwt<User>(jwtString);
            setUser(parsedJwt);
        }
    }, []);

    useEffect(() => {
        if (user) {
            let exp = new Date(user.exp * 1000);
            if (exp < new Date()) {
                console.log("Token expired");
                localStorage.removeItem("user");
                window.location.href = "/";
            }
        }
    }, [user]);

    return (
        <>
            <Header user={user} />
            <main className="container">
                {/* <video autoPlay muted loop>
                    <source src="src/assets/video/hero.mp4" type="video/mp4" />
                    Your browser does not support the video tag.
                </video> */}
                <Outlet />
            </main>
            <Footer />
        </>
    );
};

export default RootRoute;
