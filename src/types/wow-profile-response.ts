interface BnetRealm {
    name: string;
    id: number;
    slug: string;
}
interface BnetPlayableClass {
    name: string;
    id: number;
}
interface BnetGender {
    type: string;
    name: string;
}

export interface BnetCharacter {
    name: string;
    id: number;
    realm: BnetRealm;
    playable_class: BnetPlayableClass;
    playable_race: BnetPlayableClass;
    gender: BnetGender;
    faction: BnetGender;
    level: number;
}

export interface BnetWowaccount {
    id: number;
    characters: BnetCharacter[];
}

export interface ProfileResponse {
    id: number;
    wow_accounts: BnetWowaccount[];
}
