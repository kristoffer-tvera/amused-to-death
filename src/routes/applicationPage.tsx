import React, { useEffect } from "react";
import { useParams } from "react-router-dom";
import { addApplication, getApplication } from "../util/api";
import { Application } from "../types/application";
import ShowApplication from "../components/showApplication";
import EditApplications from "../components/editApplication";

const ApplicationPage: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const [application, setApplication] = React.useState<Application>();
    const [state, setState] = React.useState<"new" | "existing" | "loading">(
        "loading"
    );

    useEffect(() => {
        if (!id) {
            setApplication({
                id: 0,
                name: "",
                comment: "",
                alts: "",
                class: "",
                spec: "",
                realm: "",
                interfaceUrl: "",
                logsUrl: "",
                changeKey: "",
            });
            setState("new");
        } else {
            getApplication(Number(id)).then((application) => {
                if (application) {
                    setApplication(application);
                    setState("existing");
                }
            });
        }
    }, [id]);

    const handleSave = (updatedApplication: Application) => {
        if (updatedApplication.id === 0) {
            addApplication(updatedApplication)
                .then((newApplicationId) => {
                    console.log("New application id", newApplicationId);
                })
                .catch((error) => {
                    console.error("Error adding application", error);
                });
        } else {
            // Update the application
        }
    };

    return (
        <div>
            <h1>Application: {state}</h1>
            {state === "loading" && <p>Loading...</p>}
            {state === "new" && application && (
                <EditApplications
                    application={application}
                    onSave={handleSave}
                />
            )}
            {state === "existing" && application && (
                <ShowApplication application={application} />
            )}
        </div>
    );
};

export default ApplicationPage;
