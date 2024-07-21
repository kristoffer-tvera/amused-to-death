import React from "react";
import Table from "react-bootstrap/esm/Table";
import { DatatableWrapper } from "react-bs-datatable/lib/esm/components/DatatableWrapper";
import { TableBody } from "react-bs-datatable/lib/esm/components/TableBody";
import { TableHeader } from "react-bs-datatable/lib/esm/components/TableHeader";
import { TableColumnType } from "react-bs-datatable/lib/esm/helpers/types";
import { Character } from "../types/character";
import { getCharacters } from "../util/api";
import { formatDate } from "../util/formatting";

const Characters: React.FC = () => {
    const [characters, setCharacters] = React.useState<Character[]>([]);

    React.useEffect(() => {
        getCharacters()
            .then((characters) => {
                setCharacters(characters);
            })
            .catch((error) => {
                console.error("Error loading characters", error);
            });
    }, []);

    const headers: TableColumnType<Character>[] = [
        { title: "Name", prop: "name", isSortable: true, isFilterable: true },
        { title: "Realm", prop: "realm", isSortable: true, isFilterable: true },
        { title: "Class", prop: "class", isSortable: true, isFilterable: true },
        {
            title: "Updated",
            prop: "changedDate",
            isSortable: true,
            isFilterable: true,
        },
        {
            title: "Added",
            prop: "addedDate",
            isSortable: true,
            isFilterable: true,
        },
    ];

    const capitalizeFirstLetter = (word: string) => {
        return word.charAt(0).toUpperCase() + word.slice(1);
    };
    return (
        <div className="w-100">
            <h1>Characters</h1>
            <DatatableWrapper
                body={characters.map((char) => {
                    return {
                        ...char,
                        realm: capitalizeFirstLetter(char.realm),
                        addedDate: formatDate(char.addedDate),
                        changedDate: formatDate(char.changedDate),
                    };
                })}
                headers={headers}
            >
                <Table>
                    <TableHeader />
                    <TableBody />
                </Table>
            </DatatableWrapper>
        </div>
    );
};

export default Characters;
