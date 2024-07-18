import React from "react";
import Col from "react-bootstrap/esm/Col";
import Row from "react-bootstrap/esm/Row";
import { Application } from "../types/application";

interface ShowApplicationProps {
    application: Application;
}

const ShowApplication: React.FC<ShowApplicationProps> = ({ application }) => {
    return (
        <Row>
            <Col xs={12} className="mb-3">
                <h2>{application.id}</h2>
            </Col>
        </Row>
    );
};

export default ShowApplication;
