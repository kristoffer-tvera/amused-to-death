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
                <h2>Name</h2>
                <p>{application.name}</p>
            </Col>
            <Col xs={12} className="mb-3">
                <h2>Class</h2>
                <p>{application.class}</p>
            </Col>
            <Col xs={12} className="mb-3">
                <h2>Spec</h2>
                <p>{application.spec}</p>
            </Col>
            <Col xs={12} className="mb-3">
                <h2>Realm</h2>
                <p>{application.realm}</p>
            </Col>
            <Col xs={12} className="mb-3">
                <h2>Interface URL</h2>
                <p>{application.interfaceUrl}</p>
            </Col>
            <Col xs={12} className="mb-3">
                <h2>Logs URL</h2>
                <p>{application.logsUrl}</p>
            </Col>
            <Col xs={12} className="mb-3">
                <h2>Comment</h2>
                <p>{application.comment}</p>
            </Col>
            <Col xs={12} className="mb-3">
                <h2>Alts</h2>
                <p>{application.alts}</p>
            </Col>
            <Col xs={12} className="mb-3">
                <h2>Added Date</h2>
                <p>{application.addedDate}</p>
            </Col>
            <Col xs={12} className="mb-3">
                <h2>Changed Date</h2>
                <p>{application.changedDate}</p>
            </Col>
        </Row>
    );
};

export default ShowApplication;
