import React from "react";
import Col from "react-bootstrap/esm/Col";
import Form from "react-bootstrap/esm/Form";
import Row from "react-bootstrap/esm/Row";
import { Raid } from "../types/raid";
import { formatDate, formatGold } from "../util/formatting";

interface ShowRaidProps {
    raid: Raid;
}

const ShowRaid: React.FC<ShowRaidProps> = ({ raid }) => {
    return (
        <Form>
            <Row>
                <Col xs={12} className="mb-3">
                    <h2>
                        {raid.name} - {formatDate(raid.date)}
                    </h2>
                </Col>
                <Col className="mb-3">
                    <h3>
                        {formatGold(raid.gold)}{" "}
                        {raid.paid ? "Paid" : "Not Paid"}
                    </h3>
                </Col>
                <Col className="mb-3">
                    <h4>{raid.comment}</h4>
                </Col>
            </Row>
        </Form>
    );
};

export default ShowRaid;
