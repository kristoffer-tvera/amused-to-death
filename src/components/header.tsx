import React from "react";
import Container from "react-bootstrap/Container";
import Nav from "react-bootstrap/Nav";
import Navbar from "react-bootstrap/Navbar";
import { User } from "../types/user";

interface HeaderProps {
    user?: User;
}

const Header: React.FC<HeaderProps> = (props) => {
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
                    {props.user ? (
                        <Navbar.Text>
                            Signed in as:{" "}
                            <Nav.Link
                                href="/profile"
                                className="d-inline-block"
                            >
                                {props.user.unique_name}
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
