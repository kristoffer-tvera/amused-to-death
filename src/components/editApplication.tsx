import React from "react";
import Col from "react-bootstrap/esm/Col";
import Row from "react-bootstrap/esm/Row";
import { Application } from "../types/application";
import FloatingLabel from "react-bootstrap/esm/FloatingLabel";
import Form from "react-bootstrap/esm/Form";
import Button from "react-bootstrap/esm/Button";

interface ShowApplicationProps {
    application: Application;
    onSave: (updatedApplication: Application) => void;
}

const EditApplications: React.FC<ShowApplicationProps> = ({
    application,
    onSave,
}) => {
    const [tempApplication, setTempApplication] =
        React.useState<Application>(application);

    /*
        Form for the following properties:
        name: string;
        class: string;
        spec: string;
        realm: string;
        interfaceUrl: string;
        logsUrl: string;
        comment: string;
        alts: string;
        */

    const handleSave = () => {
        const updatedApplication: Application = {
            ...tempApplication,
        };
        onSave(updatedApplication);
    };

    return (
        <Row>
            <Form.Group
                as={Col}
                xs={12}
                className="mb-3"
                controlId="exampleForm.ControlInput1"
            >
                <FloatingLabel controlId="name" label="Name" className="mb-3">
                    <Form.Control
                        type="text"
                        placeholder=""
                        required
                        defaultValue={tempApplication.name}
                        onBlur={(e) =>
                            setTempApplication({
                                ...tempApplication,
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
                <FloatingLabel controlId="class" label="Class" className="mb-3">
                    <Form.Control
                        type="text"
                        placeholder=""
                        required
                        defaultValue={tempApplication.class}
                        onBlur={(e) =>
                            setTempApplication({
                                ...tempApplication,
                                class: e.target.value,
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
                <FloatingLabel controlId="spec" label="Spec" className="mb-3">
                    <Form.Control
                        type="text"
                        placeholder=""
                        required
                        defaultValue={tempApplication.spec}
                        onBlur={(e) =>
                            setTempApplication({
                                ...tempApplication,
                                spec: e.target.value,
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
                <FloatingLabel controlId="realm" label="Realm" className="mb-3">
                    <Form.Control
                        type="text"
                        placeholder=""
                        required
                        defaultValue={tempApplication.realm}
                        onBlur={(e) =>
                            setTempApplication({
                                ...tempApplication,
                                realm: e.target.value,
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
                    controlId="interfaceUrl"
                    label="Interface URL"
                    className="mb-3"
                >
                    <Form.Control
                        type="text"
                        placeholder=""
                        required
                        defaultValue={tempApplication.interfaceUrl}
                        onBlur={(e) =>
                            setTempApplication({
                                ...tempApplication,
                                interfaceUrl: e.target.value,
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
                    controlId="logsUrl"
                    label="Logs URL"
                    className="mb-3"
                >
                    <Form.Control
                        type="text"
                        placeholder=""
                        required
                        defaultValue={tempApplication.logsUrl}
                        onBlur={(e) =>
                            setTempApplication({
                                ...tempApplication,
                                logsUrl: e.target.value,
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
                    controlId="comment"
                    label="Comment"
                    className="mb-3"
                >
                    <Form.Control
                        type="text"
                        placeholder=""
                        required
                        defaultValue={tempApplication.comment}
                        onBlur={(e) =>
                            setTempApplication({
                                ...tempApplication,
                                comment: e.target.value,
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
                <FloatingLabel controlId="alts" label="Alts" className="mb-3">
                    <Form.Control
                        type="text"
                        placeholder=""
                        required
                        defaultValue={tempApplication.alts}
                        onBlur={(e) =>
                            setTempApplication({
                                ...tempApplication,
                                alts: e.target.value,
                            })
                        }
                    />
                </FloatingLabel>
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
    );
};

export default EditApplications;
