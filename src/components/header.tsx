import React from "react";
import Container from "react-bootstrap/Container";
import Nav from "react-bootstrap/Nav";
import Navbar from "react-bootstrap/Navbar";
import { User } from "../types/user";
import { parseJwt } from "../util/jwt";

const Header: React.FC = () => {
    const [user, setUser] = React.useState<User>();

    React.useEffect(() => {
        let jwtString = localStorage.getItem("user");
        if (jwtString) {
            let parsedJwt = parseJwt<User>(jwtString);
            setUser(parsedJwt);
        }
    }, []);

    return (
        <Navbar className="bg-body-tertiary">
            <Container>
                <Navbar.Brand href="/">Amused to Death</Navbar.Brand>
                <Nav className="me-auto">
                    <Nav.Link href="/raids">Raids</Nav.Link>
                    <Nav.Link href="/characters">Characters</Nav.Link>
                    <Nav.Link href="/applications">Applications</Nav.Link>
                </Nav>
                <Navbar.Toggle />
                <Navbar.Collapse className="justify-content-end">
                    {user ? (
                        <Navbar.Text>
                            Signed in as:{" "}
                            <Nav.Link
                                href="/profile"
                                className="d-inline-block"
                            >
                                {user.unique_name}
                            </Nav.Link>
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
