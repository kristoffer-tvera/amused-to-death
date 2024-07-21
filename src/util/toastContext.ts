import React from "react";
import { ToastProps } from "../types/toastProps";

export const ToastContext = React.createContext(
    {} as {
        toasts: ToastProps[];
        setToasts: React.Dispatch<React.SetStateAction<ToastProps[]>>;
    }
);
