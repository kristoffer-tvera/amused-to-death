import React, { useContext, useEffect } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";
import { login } from "../util/api";
import { UserContext } from "../util/userContext";
import { parseJwt } from "../util/jwt";
import { User } from "../types/user";

const Auth: React.FC = () => {
    const { setUser } = useContext(UserContext);
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();
    const code = searchParams.get("code");

    const responseType = "code";
    const scope = "openid wow.profile";
    const state = "69";
    const redirectUri = "http://localhost:5173/auth";
    const clientId = "8183bda55fd54566827c595947b189fe";

    useEffect(() => {
        if (code) {
            login(code)
                .then((response) => {
                    localStorage.setItem("user", response);
                    let parsedJwt = parseJwt<User>(response);
                    setUser(parsedJwt);
                    navigate("/");
                })
                .catch((err) => {
                    console.error(err);
                });
        } else {
            window.location.href = `https://oauth.battle.net/authorize?response_type=${responseType}&scope=${scope}&state=${state}&redirect_uri=${redirectUri}&client_id=${clientId}`;
        }
    }, []);

    return (
        <div>
            <h1>Logging you in</h1>
        </div>
    );
};

export default Auth;
