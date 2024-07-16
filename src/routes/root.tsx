import React from "react";
import { Outlet } from "react-router-dom";
import Header from "../components/header";
import Footer from "../components/footer";

const RootRoute: React.FC = () => {
    return (
        <>
            <Header />
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
