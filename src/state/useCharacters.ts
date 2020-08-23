import {reactive} from "vue";

interface Character {
    id: Number,
    ilvl: Number,
    main: Number,
    name: String,
    class: Number,
    realm: String,
    role_tank: Number,
    role_heal: Number,
    role_dps: Number,
    hidden: 0,
    discord: String,
    added_date: Date,
    change_date: Date,
    user_id: Number
}

interface UserCharacters {
    user: String,
    characters: Character[],
    error: null,
    loaded: boolean,
    loading: boolean
}

interface Characters {
    characters: Character[],
    error: null,
    loaded: boolean,
    loading: boolean
}

const userCharsState = reactive({
    characters: [] as Character[],
    loading: false,
    loaded: false,
    error: ""
})

const allCharactersState = reactive({
    characters: [] as Character[],
    loading: false,
    loaded: false,
    error: ""
})

export default function useCharacters() {

    const getUserCharacters = async (userId: Number) => {

        if(!userCharsState.loaded && !userCharsState.loading) {
            try {
                userCharsState.loading = true;
                const res = await fetch("/api/characters.php?userId=" + userId);
                userCharsState.characters = await res.json();
                userCharsState.loaded = true;
                userCharsState.loading = false;
                console.log(userCharsState)
            }
            catch(e) {
                userCharsState.error = e;
                userCharsState.loaded = true;
                userCharsState.loading = false;
            }
        }

    }

    return {userCharsState, getUserCharacters}
}
