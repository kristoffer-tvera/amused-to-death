export interface RaidAttendance {
    id: number;
    raidId: number;
    characterId: number;
    bossesKilled: number;
    paid: boolean;
    addedDate: string;
    changedDate: string;
}
