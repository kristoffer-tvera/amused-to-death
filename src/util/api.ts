export const getCharacters = async () => {
    return await fetch(`${import.meta.env.VITE_API_BASE_PATH}/characters`, {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
    });
};

export const getRaids = async () => {
    return await fetch(`${import.meta.env.VITE_API_BASE_PATH}/raids`, {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
    });
};

export const getApplications = async () => {
    return await fetch(`${import.meta.env.VITE_API_BASE_PATH}/applications`, {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
    });
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
