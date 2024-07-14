import React from "react";

const Footer: React.FC = () => {
    return (
        <footer className="text-center">
            <p>&copy; {new Date().getFullYear()} Amused to Death.</p>
        </footer>
    );
};

export default Footer;
