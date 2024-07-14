import React from "react";
import { BnetCharacter, ProfileResponse } from "../types/wow-profile-response";
import { addCharacter, getWowProfile } from "../util/api";
import Accordion from "react-bootstrap/esm/Accordion";
import ListGroup from "react-bootstrap/esm/ListGroup";
import Badge from "react-bootstrap/esm/Badge";
import { Character } from "../types/character";

const Profile: React.FC = () => {
    const [profileResponse, setProfileResponse] =
        React.useState<ProfileResponse>();

    React.useEffect(() => {
        getWowProfile()
            .then((profileResponse) => {
                console.log(profileResponse);
                setProfileResponse(profileResponse);
            })
            .catch((error) => {
                console.error("Error loading profile", error);
            });
    }, []);

    const logout = () => {
        localStorage.removeItem("user");
    };

    const addCharacterHandler = (bnetCharacter: BnetCharacter) => {
        console.log(bnetCharacter);
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
                console.log("Character added", character);
            })
            .catch((error) => {
                console.error("Error adding character", error);
            });
    };

    const sortCharacters = (a: BnetCharacter, b: BnetCharacter) =>
        a.realm.name > b.realm.name ? 1 : -1;

    return (
        <div>
            <h1>Profile Page</h1>
            <h3>My characters</h3>
            <ul>
                <li> Character A</li>
                <li> Character b</li>
                <li> Character c</li>
            </ul>
            {profileResponse && (
                <>
                    <h3>Bnet account data</h3>
                    <Accordion defaultActiveKey="0">
                        {profileResponse.wow_accounts.map((account, index) => (
                            <Accordion.Item
                                eventKey={index.toString()}
                                key={index}
                            >
                                <Accordion.Header>
                                    {account.id} ({account.characters.length}{" "}
                                    characters)
                                </Accordion.Header>
                                <Accordion.Body>
                                    <ListGroup defaultActiveKey="#link1">
                                        {account.characters
                                            .sort(sortCharacters)
                                            .map((character, index) => (
                                                <ListGroup.Item
                                                    key={index}
                                                    action
                                                    onClick={() => {
                                                        addCharacterHandler(
                                                            character
                                                        );
                                                    }}
                                                    className="d-flex justify-content-between align-items-start"
                                                >
                                                    <div className="ms-2 me-auto">
                                                        <div className="fw-bold">
                                                            {character.name}-
                                                            {
                                                                character.realm
                                                                    .slug
                                                            }
                                                        </div>
                                                        {
                                                            character
                                                                .playable_class
                                                                .name
                                                        }
                                                    </div>
                                                    <Badge bg="primary" pill>
                                                        {character.level}
                                                    </Badge>
                                                </ListGroup.Item>
                                            ))}
                                    </ListGroup>
                                </Accordion.Body>
                            </Accordion.Item>
                        ))}
                    </Accordion>
                </>
            )}
            <button onClick={logout}>Logout</button>
        </div>
    );
};

export default Profile;
