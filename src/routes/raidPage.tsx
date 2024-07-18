import React, { useEffect } from "react";
import { useNavigate, useParams } from "react-router-dom";
import EditRaid from "../components/editRaid";
import ShowRaid from "../components/showRaid";
import { Raid } from "../types/raid";
import { addRaid, getRaid, updateRaid } from "../util/api";
import Button from "react-bootstrap/esm/Button";

const RaidPage: React.FC = () => {
    const [raid, setRaid] = React.useState<Raid>();
    const [editRaid, setEditRaid] = React.useState(false);
    const { raidId } = useParams<{ raidId: string }>();
    const navigate = useNavigate();

    useEffect(() => {
        const id = Number(raidId);
        if (isNaN(id)) {
            setRaid({
                id: 0,
                name: "",
                date: new Date().toISOString(),
                gold: 0,
                paid: false,
                comment: "",
            });
            setEditRaid(true);
            return;
        }

        getRaid(id)
            .then((raid) => {
                setRaid(raid);
            })
            .catch((error) => {
                console.error("Error loading raid", error);
            });
    }, [raidId]);

    const handleAddRaid = (raid: Raid) => {
        if (raid.id === 0) {
            addRaid(raid)
                .then((newRaidId) => {
                    navigate(`/raids/${newRaidId}`);
                })
                .catch((error) => {
                    console.error("Error adding raid", error);
                });
            return;
        } else {
            updateRaid(raid)
                .then(() => {
                    setRaid(raid);
                    setEditRaid(false);
                })
                .catch((error) => {
                    console.error("Error adding raid", error);
                });
        }
    };

    return (
        <div className="w-100">
            <Button
                variant="outline-primary"
                onClick={() => {
                    setEditRaid(!editRaid);
                }}
            >
                update
            </Button>
            <h1>Raid Screen: {raidId}</h1>
            {editRaid && <EditRaid raid={raid!} onSave={handleAddRaid} />}

            {raid?.id && !editRaid && <ShowRaid raid={raid} />}
        </div>
    );
};

export default RaidPage;
