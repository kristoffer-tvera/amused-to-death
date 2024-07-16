import React, { useEffect } from "react";
import Button from "react-bootstrap/esm/Button";
import Table from "react-bootstrap/esm/Table";
import { DatatableWrapper } from "react-bs-datatable/lib/esm/components/DatatableWrapper";
import { TableBody } from "react-bs-datatable/lib/esm/components/TableBody";
import { TableHeader } from "react-bs-datatable/lib/esm/components/TableHeader";
import { TableColumnType } from "react-bs-datatable/lib/esm/helpers/types";
import { useNavigate } from "react-router-dom";
import { Raid } from "../types/raid";
import { getRaids } from "../util/api";
import { formatDate } from "../util/formatting";

const Raids: React.FC = () => {
    const navigate = useNavigate();
    const [raids, setRaids] = React.useState<Raid[]>([]);

    useEffect(() => {
        getRaids()
            .then((raids) => {
                setRaids(raids);
            })
            .catch((error) => {
                console.error("Error loading raids", error);
            });
    }, []);

    interface RaidColumnType {
        id: number;
        name: string;
        date: string;
    }

    const headers: TableColumnType<RaidColumnType>[] = [
        {
            title: "Id",
            prop: "id",
            isSortable: false,
            isFilterable: false,
            thProps: { style: { display: "none" } },
            cellProps: { style: { display: "none" } },
        },
        { title: "Name", prop: "name", isSortable: true, isFilterable: true },
        { title: "Date", prop: "date", isSortable: true, isFilterable: true },
    ];

    return (
        <div className="w-100">
            <h1>
                Raids{" "}
                <Button variant="outline-primary" href="/raids/new">
                    Add
                </Button>
            </h1>
            <DatatableWrapper
                body={raids.map((raid) => {
                    return {
                        id: raid.id,
                        name: raid.name,
                        date: formatDate(raid.date),
                    };
                })}
                headers={headers}
            >
                <Table>
                    <TableHeader />
                    <TableBody
                        onRowClick={(row: RaidColumnType) => {
                            navigate("/raids/" + row.id);
                        }}
                    />
                </Table>
            </DatatableWrapper>
        </div>
    );
};

export default Raids;
