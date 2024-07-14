export interface User {
    unique_name: string;
    sid: number;
    access_token: string;
    role: "Member" | "Officer";
    addedDate: string;
    changedDate: string;
}
