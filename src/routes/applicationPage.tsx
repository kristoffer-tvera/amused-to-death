import React, { useContext, useEffect, useState } from "react";
import { useNavigate, useParams, useSearchParams } from "react-router-dom";
import { addApplication, getApplication, updateApplication } from "../util/api";
import { Application } from "../types/application";
import ShowApplication from "../components/showApplication";
import EditApplications from "../components/editApplication";
import { ToastContext } from "../util/toastContext";

const ApplicationPage: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const [application, setApplication] = useState<Application>();
    const navigate = useNavigate();
    const [searchParams] = useSearchParams();
    const [state, setState] = useState<"new" | "existing" | "loading">(
        "loading"
    );
    const { setToasts } = useContext(ToastContext);

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
                    setApplication({
                        ...application,
                        changeKey: searchParams.get("auth") ?? "",
                    });
                    setState("existing");
                }
            });
        }
    }, [id]);

    const handleSave = (updatedApplication: Application) => {
        if (updatedApplication.id === 0) {
            addApplication(updatedApplication)
                .then((newApplication) => {
                    console.log("New application:", newApplication);
                    setToasts((toasts) => [
                        ...toasts,
                        {
                            id: Date.now(),
                            title: "Application added",
                            message: `Application ${newApplication} has been added`,
                        },
                    ]);
                    navigate(
                        `/applications/${newApplication.id}?auth=${application?.changeKey}`
                    );
                })
                .catch((error) => {
                    console.error("Error adding application", error);
                });
        } else {
            updateApplication(updatedApplication)
                .then(() => {
                    setToasts((toasts) => [
                        ...toasts,
                        {
                            id: Date.now(),
                            title: "Application updated",
                            message: `Application ${updatedApplication.id} has been updated`,
                        },
                    ]);
                })
                .catch((error) => {
                    console.error("Error updating application", error);
                });
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
