import { Application } from "../types/application";
import { Character } from "../types/character";
import { Raid } from "../types/raid";

export const getCharacters = async (): Promise<Character[]> => {
    let request = await fetch(
        `${import.meta.env.VITE_API_BASE_PATH}/characters`,
        {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${localStorage.getItem("user")}`,
            },
        }
    );
    let response = await request.json();
    return response;
};

export const getMyCharacters = async (): Promise<Character[]> => {
    let request = await fetch(
        `${import.meta.env.VITE_API_BASE_PATH}/characters/my`,
        {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${localStorage.getItem("user")}`,
            },
        }
    );
    let response = await request.json();
    return response;
};

export const deleteCharacter = async (id: number): Promise<void> => {
    await fetch(`${import.meta.env.VITE_API_BASE_PATH}/characters/${id}`, {
        method: "DELETE",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${localStorage.getItem("user")}`,
        },
    });
};

export const addCharacter = async (
    character: Character
): Promise<Character> => {
    let request = await fetch(
        `${import.meta.env.VITE_API_BASE_PATH}/characters`,
        {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${localStorage.getItem("user")}`,
            },
            body: JSON.stringify(character),
        }
    );
    let response = await request.json();
    return response;
};

export const getRaids = async (): Promise<Raid[]> => {
    let request = await fetch(`${import.meta.env.VITE_API_BASE_PATH}/raids`, {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${localStorage.getItem("user")}`,
        },
    });
    let response = await request.json();
    return response;
};

export const getRaid = async (id: number): Promise<Raid> => {
    let request = await fetch(
        `${import.meta.env.VITE_API_BASE_PATH}/raids/${id}`,
        {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${localStorage.getItem("user")}`,
            },
        }
    );
    let response = await request.json();
    return response;
};

export const addRaid = async (raid: Raid): Promise<Raid> => {
    let request = await fetch(`${import.meta.env.VITE_API_BASE_PATH}/raids`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${localStorage.getItem("user")}`,
        },
        body: JSON.stringify(raid),
    });
    let response = await request.json();
    return response;
};

export const getApplications = async (): Promise<Application[]> => {
    let request = await fetch(
        `${import.meta.env.VITE_API_BASE_PATH}/applications`,
        {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${localStorage.getItem("user")}`,
            },
        }
    );
    let response = await request.json();
    return response;
};

export const login = async (code: string): Promise<string> => {
    var request = await fetch(`${import.meta.env.VITE_API_BASE_PATH}/login`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({ code }),
    });

    let response = await request.json();
    return response;
};

export const getWowProfile = async (): Promise<any> => {
    let request = await fetch(
        `${import.meta.env.VITE_API_BASE_PATH}/bnet-profile`,
        {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${localStorage.getItem("user")}`,
            },
        }
    );
    let response = await request.json();
    return response;
};
