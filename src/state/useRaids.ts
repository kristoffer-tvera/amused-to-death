import { reactive } from "vue";
import moment from "moment";

interface Raid {
    id: Number,
    gold: Number,
    name: String,
    added_date: Date,
    change_date: Date,
    start_date: Date,
    start_time: Date
}

interface Raids {
    raids: Raid[],
    error: null,
    loaded: boolean,
    loading: boolean
}

const allRaidsState = reactive<Raids>({
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
        if (!allRaidsState.loaded && !allRaidsState.loading) {
            try {
                allRaidsState.loading = true;
                const res = await fetch("http://localhost:3000/api/raids.php");
                allRaidsState.raids = await res.json();
                allRaidsState.loaded = true;
            }
            catch (e) {
                allRaidsState.loaded = false;
                allRaidsState.loading = false;
                allRaidsState.error = e;
            }
        }
    };

    const getRaidById = async (id: Number) => {
        if (allRaidsState.raids.length > 0) {
            let raid = allRaidsState.raids.find(r => r.id == id);
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

    return { allRaidsState, getRaids, getRaidById, raidState };
}