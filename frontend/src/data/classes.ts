export interface WowClass {
    id: number;
    name: string;
    color: string;
}

export const wowClasses: WowClass[] = [
    { id: -1, name: "Unknown", color: "#808080" },
    { id: 0, name: "Druid", color: "#FF7C0A" },
    { id: 1, name: "Paladin", color: "#F48CBA" },
    { id: 2, name: "Warrior", color: "#C69B6D" },
    { id: 3, name: "Demon Hunter", color: "#A330C9" },
    { id: 4, name: "Hunter", color: "#AAD372" },
    { id: 5, name: "Mage", color: "#3FC7EB" },
    { id: 6, name: "Rogue", color: "#FFF468" },
    { id: 7, name: "Death Knight", color: "#C41E3A" },
    { id: 8, name: "Priest", color: "#FFFFFF" },
    { id: 9, name: "Warlock", color: "#8788EE" },
    { id: 10, name: "Shaman", color: "#0070DD" },
    { id: 11, name: "Monk", color: "#00FF98" },
    { id: 12, name: "Evoker", color: "#33937F" },
];

export function getClassById(id: number): WowClass {
    return wowClasses.find((c) => c.id === id) || wowClasses[0];
}

export function getClassName(id: number): string {
    return getClassById(id).name;
}

export function getClassColor(id: number): string {
    return getClassById(id).color;
}
