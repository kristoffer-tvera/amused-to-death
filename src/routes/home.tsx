import React from "react";
import { Link } from "react-router-dom";

const Home: React.FC = () => {
    return (
        <div className="textbox">
            <h1>Welcome to Amused to Death!</h1>
            <p>
                If you want to apply to become a raider, please{" "}
                <Link to="/applications/new">click here</Link>.
            </p>
            <p>
                If you want to join our Discord,{" "}
                <Link to="https://discord.gg/urtqPvR">click here</Link>.
            </p>
        </div>
    );
};

export default Home;
