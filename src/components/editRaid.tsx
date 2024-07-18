import React, { useEffect, useState } from "react";
import Form from "react-bootstrap/esm/Form";
import { Raid } from "../types/raid";
import FloatingLabel from "react-bootstrap/esm/FloatingLabel";
import Row from "react-bootstrap/esm/Row";
import Col from "react-bootstrap/esm/Col";
import { Button } from "react-bootstrap";

interface EditRaidProps {
    raid: Raid;
    onSave: (updatedRaid: Raid) => void;
}

const EditRaid: React.FC<EditRaidProps> = ({ raid, onSave }) => {
    const [tempRaid, setTempRaid] = useState<Raid>(raid);

    useEffect(() => {
        setTempRaid(raid);
    }, [raid]);

    const handleSave = () => {
        const updatedRaid: Raid = {
            ...tempRaid,
        };
        onSave(updatedRaid);
    };

    return (
        <Form>
            <Row>
                <Form.Group
                    as={Col}
                    xs={12}
                    className="mb-3"
                    controlId="exampleForm.ControlInput1"
                >
                    <FloatingLabel
                        controlId="name"
                        label="Name"
                        className="mb-3"
                    >
                        <Form.Control
                            type="text"
                            placeholder=""
                            required
                            defaultValue={raid.name}
                            onBlur={(e) =>
                                setTempRaid({
                                    ...tempRaid,
                                    name: e.target.value,
                                })
                            }
                        />
                    </FloatingLabel>
                </Form.Group>
                <Form.Group
                    as={Col}
                    className="mb-3"
                    controlId="exampleForm.ControlInput1"
                >
                    <FloatingLabel
                        controlId="date"
                        label="Date"
                        className="mb-3"
                    >
                        <Form.Control
                            type="date"
                            placeholder=""
                            required
                            defaultValue={
                                raid.date ? raid.date.substring(0, 10) : ""
                            }
                            onBlur={(e) =>
                                setTempRaid({
                                    ...tempRaid,
                                    date: e.target.value,
                                })
                            }
                        />
                    </FloatingLabel>
                </Form.Group>
                <Form.Group
                    as={Col}
                    className="mb-3"
                    controlId="exampleForm.ControlInput1"
                >
                    <FloatingLabel
                        controlId="gold"
                        label="Gold"
                        className="mb-3"
                    >
                        <Form.Control
                            type="text"
                            placeholder=""
                            required
                            defaultValue={raid.gold}
                            onBlur={(e) =>
                                setTempRaid({
                                    ...tempRaid,
                                    gold: parseInt(e.target.value),
                                })
                            }
                        />
                    </FloatingLabel>
                </Form.Group>
                <Form.Group
                    as={Col}
                    className="mb-3"
                    controlId="exampleForm.ControlInput1"
                >
                    <Form.Check
                        type={"checkbox"}
                        id={`default-`}
                        label={`Paid `}
                        defaultChecked={raid.paid}
                        onChange={(e) =>
                            setTempRaid({
                                ...tempRaid,
                                paid: e.target.checked,
                            })
                        }
                    />
                </Form.Group>
                <Form.Group
                    as={Col}
                    xs={12}
                    className="mb-3"
                    controlId="exampleForm.ControlTextarea1"
                >
                    <Form.Label>Comment</Form.Label>
                    <Form.Control
                        as="textarea"
                        rows={3}
                        defaultValue={raid.comment}
                        onBlur={(e) =>
                            setTempRaid({
                                ...tempRaid,
                                comment: e.target.value,
                            })
                        }
                    />
                </Form.Group>
                <Form.Group
                    as={Col}
                    xs={12}
                    className="mb-3"
                    controlId="exampleForm.ControlTextarea1"
                >
                    <Button onClick={handleSave} className="w-100">
                        Save
                    </Button>
                </Form.Group>
            </Row>
        </Form>
    );
};

export default EditRaid;
