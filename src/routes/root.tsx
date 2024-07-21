import React, { useEffect, useState } from "react";
import { Outlet } from "react-router-dom";
import Header from "../components/header";
import Footer from "../components/footer";
import { User } from "../types/user";
import { parseJwt } from "../util/jwt";
import ToastContainer from "react-bootstrap/esm/ToastContainer";
import Toast from "react-bootstrap/esm/Toast";
import { ToastProps } from "../types/toastProps";
import { ToastContext } from "../util/toastContext";
import { UserContext } from "../util/userContext";

const RootRoute: React.FC = () => {
    const [toasts, setToasts] = useState<ToastProps[]>([]);
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
                localStorage.removeItem("user");
                window.location.href = "/";
            }
        }
    }, [user]);

    return (
        <ToastContext.Provider value={{ toasts, setToasts }}>
            <UserContext.Provider value={{ user, setUser }}>
                <Header user={user} />
                <ToastContainer
                    className="position-fixed p-4"
                    position="bottom-center"
                >
                    {toasts.map((toast) => {
                        return (
                            <Toast
                                key={toast.id}
                                onClose={() =>
                                    setToasts(
                                        toasts.filter((t) => t.id !== toast.id)
                                    )
                                }
                                delay={5000}
                                autohide
                            >
                                <Toast.Header>
                                    <strong className="me-auto">
                                        {toast.title}
                                    </strong>
                                    <small className="text-muted">
                                        just now
                                    </small>
                                </Toast.Header>
                                <Toast.Body>{toast.message}</Toast.Body>
                            </Toast>
                        );
                    })}
                </ToastContainer>
                <main className="container">
                    {/* <video autoPlay muted loop>
                    <source src="src/assets/video/hero.mp4" type="video/mp4" />
                    Your browser does not support the video tag.
                </video> */}

                    <Outlet />
                </main>
                <Footer />
            </UserContext.Provider>
        </ToastContext.Provider>
    );
};

export default RootRoute;
