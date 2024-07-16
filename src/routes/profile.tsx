import React, { useEffect, useState } from "react";
import Accordion from "react-bootstrap/esm/Accordion";
import Badge from "react-bootstrap/esm/Badge";
import ListGroup from "react-bootstrap/esm/ListGroup";
import { Character } from "../types/character";
import {
    BnetCharacter,
    BnetWowaccount,
    ProfileResponse,
} from "../types/wow-profile-response";
import {
    addCharacter,
    deleteCharacter,
    getMyCharacters,
    getWowProfile,
} from "../util/api";

const Profile: React.FC = () => {
    const [myCharacters, setMyCharacters] = useState<Character[]>([]);
    const [profileResponse, setProfileResponse] = useState<ProfileResponse>();

    useEffect(() => {
        getWowProfile()
            .then((profileResponse) => {
                setProfileResponse(profileResponse);
            })
            .catch((error) => {
                console.error("Error loading profile", error);
            });

        getMyCharacters()
            .then((myCharacters) => {
                setMyCharacters(myCharacters);
            })
            .catch((error) => {
                console.error("Error loading my characters", error);
            });
    }, []);

    const logout = () => {
        localStorage.removeItem("user");
        window.location.href = "/";
    };

    const addCharacterHandler = (bnetCharacter: BnetCharacter) => {
        let character: Character = {
            id: bnetCharacter.id,
            name: bnetCharacter.name,
            realm: bnetCharacter.realm.slug,
            class: bnetCharacter.playable_class.name,
            level: bnetCharacter.level,
            itemLevel: 0,
            ownerId: 0,
        };

        addCharacter(character)
            .then((character) => {
                let newCharacters = [...myCharacters, character];
                setMyCharacters(newCharacters);
            })
            .catch((error) => {
                console.error("Error adding character", error);
            });
    };

    const removeCharacterHandler = (character: Character) => {
        deleteCharacter(character.id)
            .then(() => {
                let newCharacters = myCharacters.filter(
                    (c) => c.id !== character.id
                );
                setMyCharacters(newCharacters);
            })
            .catch((error) => {
                console.error("Error deleting character", error);
            });
    };

    const sortCharacters = (a: BnetCharacter, b: BnetCharacter) =>
        a.realm.name > b.realm.name ? 1 : -1;

    const sortRealmGroupsBySize = (a: RealmGroup, b: RealmGroup) =>
        a.characters.length < b.characters.length ? 1 : -1;

    interface RealmGroup {
        realm: string;
        characters: BnetCharacter[];
    }
    const groupByRealm = (characters: BnetCharacter[]): RealmGroup[] => {
        let realmGroup: RealmGroup[] = [];
        characters.sort(sortCharacters).forEach((character) => {
            let realm = realmGroup.find(
                (rg) => rg.realm === character.realm.name
            );
            if (realm) {
                realm.characters.push(character);
            } else {
                realmGroup.push({
                    realm: character.realm.name,
                    characters: [character],
                });
            }
        });
        return realmGroup;
    };

    const realmGroupHeader = (realmGroup: RealmGroup): string => {
        return `${realmGroup.realm} (${realmGroup.characters.length} characters) `;
    };

    const renderMyCharacterRow = (character: Character, index: number) => {
        return (
            <ListGroup.Item
                key={index}
                action
                onClick={() => {
                    removeCharacterHandler(character);
                }}
                className="d-flex justify-content-between align-items-start"
            >
                <div className="ms-2 me-auto">
                    <div className="fw-bold">
                        {character.name}-{character.realm}
                    </div>
                </div>
                <Badge bg="primary" pill>
                    {character.level}
                </Badge>
            </ListGroup.Item>
        );
    };

    const renderAccountRow = (account: BnetWowaccount, index: number) => {
        return (
            <Accordion.Item eventKey={index.toString()} key={index}>
                <Accordion.Header>
                    {account.id} ({account.characters.length} characters)
                </Accordion.Header>
                <Accordion.Body>
                    {groupByRealm(account.characters)
                        .sort(sortRealmGroupsBySize)
                        .map((realmGroup, index) => {
                            return renderRealmGroup(realmGroup, index);
                        })}
                </Accordion.Body>
            </Accordion.Item>
        );
    };

    const renderRealmGroup = (realmGroup: RealmGroup, index: number) => {
        return (
            <Accordion key={index}>
                <Accordion.Item eventKey={index.toString()}>
                    <Accordion.Header>
                        {realmGroupHeader(realmGroup)}
                    </Accordion.Header>
                    <Accordion.Body>
                        <ListGroup>
                            {realmGroup.characters.map((character, index) => {
                                return renderCharacterRow(character, index);
                            })}
                        </ListGroup>
                    </Accordion.Body>
                </Accordion.Item>
            </Accordion>
        );
    };

    const renderCharacterRow = (character: BnetCharacter, index: number) => {
        return (
            <ListGroup.Item
                key={index}
                action
                onClick={() => addCharacterHandler(character)}
            >
                {character.name}
                <Badge bg="primary" pill className="mx-2">
                    {character.level}
                </Badge>
            </ListGroup.Item>
        );
    };

    return (
        <div className="w-100">
            <h1>Profile Page</h1>
            <h3>My characters</h3>
            <ListGroup defaultActiveKey="#link1">
                {myCharacters.map((character, index) => {
                    return renderMyCharacterRow(character, index);
                })}
            </ListGroup>
            {profileResponse && (
                <>
                    <h3>Bnet account data</h3>
                    <Accordion defaultActiveKey="0">
                        {profileResponse.wow_accounts.map((account, index) => {
                            return renderAccountRow(account, index);
                        })}
                    </Accordion>
                </>
            )}
            <button onClick={logout}>Logout</button>
        </div>
    );
};

export default Profile;
