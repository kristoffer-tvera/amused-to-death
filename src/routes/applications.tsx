import React, { useEffect } from "react";
import Table from "react-bootstrap/esm/Table";
import { DatatableWrapper } from "react-bs-datatable/lib/esm/components/DatatableWrapper";
import { TableBody } from "react-bs-datatable/lib/esm/components/TableBody";
import { TableHeader } from "react-bs-datatable/lib/esm/components/TableHeader";
import { TableColumnType } from "react-bs-datatable/lib/esm/helpers/types";
import { useNavigate } from "react-router-dom";
import { Application } from "../types/application";
import { getApplications } from "../util/api";
import { formatDate } from "../util/formatting";

const Applications: React.FC = () => {
    const navigate = useNavigate();
    const [applications, setApplications] = React.useState<Application[]>([]);

    useEffect(() => {
        getApplications()
            .then((applications) => {
                setApplications(applications);
            })
            .catch((error) => {
                console.error("Error loading applications", error);
            });
    }, []);

    const headers: TableColumnType<Application>[] = [
        {
            title: "Id",
            prop: "id",
            isSortable: false,
            isFilterable: false,
            thProps: { style: { display: "none" } },
            cellProps: { style: { display: "none" } },
        },
        { title: "Name", prop: "name", isSortable: true, isFilterable: true },
        { title: "Class", prop: "class", isSortable: true, isFilterable: true },
        {
            title: "Added",
            prop: "addedDate",
            isSortable: true,
            isFilterable: true,
        },
    ];

    return (
        <div className="w-100">
            <h1>Applications</h1>
            <DatatableWrapper
                body={applications.map((app) => {
                    return {
                        ...app,
                        addedDate: formatDate(app.addedDate),
                    };
                })}
                headers={headers}
            >
                <Table>
                    <TableHeader />
                    <TableBody
                        onRowClick={(row: Application) => {
                            navigate("/applications/" + row.id);
                        }}
                    />
                </Table>
            </DatatableWrapper>
        </div>
    );
};

export default Applications;
