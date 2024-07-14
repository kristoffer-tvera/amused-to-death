export interface Character {
    id: number;
    name: string;
    realm: string;
    class: string;
    level: number;
    itemLevel: number;
    ownerId: number;
    main?: Character;
    addedDate?: string;
    changedDate?: string;
}
