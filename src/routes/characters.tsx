import React from "react";
import { Character } from "../types/character";
import { getCharacters } from "../util/api";
import { DatatableWrapper } from "react-bs-datatable/lib/esm/components/DatatableWrapper";
import { TableHeader } from "react-bs-datatable/lib/esm/components/TableHeader";
import { TableBody } from "react-bs-datatable/lib/esm/components/TableBody";
import Table from "react-bootstrap/esm/Table";
import { TableColumnType } from "react-bs-datatable/lib/esm/helpers/types";

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

    interface StoryColumnType {
        name: string;
        realm: string;
        class: string;
    }

    const headers: TableColumnType<StoryColumnType>[] = [
        { title: "Name", prop: "name", isSortable: true, isFilterable: true },
        { title: "Realm", prop: "realm", isSortable: true, isFilterable: true },
        { title: "Class", prop: "class", isSortable: true, isFilterable: true },
    ];

    const capitalizeFirstLetter = (word: string) => {
        return word.charAt(0).toUpperCase() + word.slice(1);
    };
    return (
        <div>
            <h1>Characters</h1>
            <DatatableWrapper
                body={characters.map((char) => {
                    return {
                        name: char.name,
                        realm: capitalizeFirstLetter(char.realm),
                        class: char.class,
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
