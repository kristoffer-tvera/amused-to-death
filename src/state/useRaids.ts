import { reactive } from "vue";

interface Raid {
    id: Number,
    gold: Number,
    name: String,
    added_date: Date,
    change_date: Date,
    start_date: Date
}

interface Raids {
    raids: Raid[],
    error: null,
    loaded: boolean,
    loading: boolean
}

// Note: idk if these state names will be confusing for others. Feel free to change if you find it bothersome.

const raidsState = reactive<Raids>({
    raids: [] as Raid[],
    error: null,
    loaded: false,
    loading: false
})

const raidState = reactive({
    raid: {} as Raid,
    loading: false,
    error: ""
})

export default function useRaids() {

    const getRaids = async () => {
        if (!raidsState.loaded && !raidsState.loading) {
            try {
                raidsState.loading = true;
                const res = await fetch("http://localhost:3000/api/raids.php");
                raidsState.raids = await res.json();
                raidsState.loaded = true;
            }
            catch (e) {
                raidsState.loaded = false;
                raidsState.loading = false;
                raidsState.error = e;
                console.log("catch");
            }
        }
    };

    const getRaidById = async (id: Number) => {
        if (raidsState.raids.length > 0) {
            let raid = raidsState.raids.find(r => r.id == id);
            if (!raid) {
                console.log("no raid found");
                raidState.error = "Could not get raid, please try refreshing";
            }
            else {
                raidState.raid = raid;
                raidState.error = "";
            }
        }
        else {
            try {
                raidState.loading = true;
                const res = await fetch("http://localhost:3000/api/raid.php?id=" + id);
                raidState.raid = await res.json();
                if (!raidState.raid) {
                    throw "Raid not found";
                }
                raidState.loading = false;
            }
            catch (e) {
                raidState.error = e;
            }
        }
    }

    return { raidsState, getRaids, getRaidById, raidState };
}