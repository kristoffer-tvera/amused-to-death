import React, { useEffect } from "react";
import { useNavigate, useParams } from "react-router-dom";
import EditRaid from "../components/editRaid";
import ShowRaid from "../components/showRaid";
import { Raid } from "../types/raid";
import { addRaid, getRaid } from "../util/api";

const RaidPage: React.FC = () => {
    const [raid, setRaid] = React.useState<Raid>();
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
        addRaid(raid)
            .then((newRaidId) => {
                navigate(`/raids/${newRaidId}`);
            })
            .catch((error) => {
                console.error("Error adding raid", error);
            });
    };

    return (
        <div className="w-100">
            <h1>Raid Screen: {raidId}</h1>
            {raid && raid.id === 0 && (
                <EditRaid raid={raid} onSave={handleAddRaid} />
            )}
            {raid && raid.id !== 0 && <ShowRaid raid={raid} />}
        </div>
    );
};

export default RaidPage;
