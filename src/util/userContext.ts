import React from "react";
import { User } from "../types/user";

export const UserContext = React.createContext(
    {} as {
        user?: User;
        setUser: React.Dispatch<React.SetStateAction<User | undefined>>;
    }
);
