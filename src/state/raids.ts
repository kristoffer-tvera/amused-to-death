import { ref, reactive, toRefs } from "vue";

interface Raid {
    id: Number,
    gold: Number,
    name: String,
    added_date: Date,
    change_date: Date,
    start_date: Date
}


const state = reactive({
    raids: [],
    error: null,
    loaded: false,
    loading: false
})

export default function useRaids() {
    
    const loadRaids = async () => {
        if (!state.loaded) {
            try {
            const res = await fetch("http://localhost:3000/api/raids.php");
            state.raids = await res.json();
            state.loaded = true;
            }
            catch(e) {
                state.error = e;
                console.log("catch");
            }
        }
    };
    return {...toRefs(state), loadRaids};
}