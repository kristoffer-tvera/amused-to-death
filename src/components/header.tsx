import React from "react";
import Container from "react-bootstrap/Container";
import Navbar from "react-bootstrap/Navbar";
import Nav from "react-bootstrap/Nav";
import { User } from "../types/user";

const Header: React.FC = () => {
    const [user, setUser] = React.useState<User>();

    React.useEffect(() => {
        let user = window.localStorage.getItem("user");
        if (user) {
            let json = JSON.parse(user) as User;
            console.log(json);
            setUser(json);
        }
    }, []);

    return (
        <Navbar className="bg-body-tertiary">
            <Container>
                <Navbar.Brand href="/">Amused to Death</Navbar.Brand>
                <Nav className="me-auto">
                    <Nav.Link href="/raids">Raids</Nav.Link>
                    <Nav.Link href="/characters">Characters</Nav.Link>
                    <Nav.Link href="/aplications">Applications</Nav.Link>
                </Nav>
                <Navbar.Toggle />
                <Navbar.Collapse className="justify-content-end">
                    {user ? (
                        <Navbar.Text>
                            Signed in as:{" "}
                            <a href="#login">{user.unique_name}</a>
                        </Navbar.Text>
                    ) : (
                        <Nav>
                            <Nav.Link href="/auth">Login</Nav.Link>
                        </Nav>
                    )}
                </Navbar.Collapse>
            </Container>
        </Navbar>
    );
};

export default Header;
