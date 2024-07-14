import React, { useEffect } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";
import { login } from "../util/api";
import { parseJwt } from "../util/jwt";
import { User } from "../types/user";

const Auth: React.FC = () => {
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();
    const code = searchParams.get("code");

    const responseType = "code";
    const scope = "openid";
    const state = "69";
    const redirectUri = "http://localhost:5173/auth";
    const clientId = "8183bda55fd54566827c595947b189fe";

    useEffect(() => {
        if (code) {
            login(code)
                .then((response) => {
                    let parsedJwt = parseJwt<User>(response);
                    window.localStorage.setItem(
                        "user",
                        JSON.stringify(parsedJwt)
                    );
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
            <h1>Auth Screen</h1>
            <p>Code: {code}</p>
        </div>
    );
};

export default Auth;
