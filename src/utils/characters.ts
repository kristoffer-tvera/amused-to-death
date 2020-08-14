
// function getUserCharacters() : Promise<String[]> {
//     return fetch("http://localhost:3000/api/get/characters.php")
//     .then(res => res.json())
//     .then(data => data)
// }

// export {getUserCharacters}

import {ref} from "vue";

export default function useCharacters() {
    let characters = ref(null);

    const load = async() => {
        fetch("http://localhost:3000/api/get/characters.php")
            .then(res => res.json())
            .then(data => data)
    } 

    return {}
}