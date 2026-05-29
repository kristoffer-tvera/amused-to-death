const BASE = "/backend/actions";

// Generic fetch helper that handles form-encoded POST (matching PHP $_POST expectations)
async function postForm(
    url: string,
    data: Record<string, string>,
): Promise<Response> {
    return fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams(data),
        credentials: "same-origin",
    });
}

async function get(url: string): Promise<Response> {
    return fetch(url, { credentials: "same-origin" });
}

// ─── Auth ────────────────────────────────────────────────────────────────────

export async function getMe() {
    const res = await get(`${BASE}/data.php?action=me`);
    if (!res.ok) return null;
    return res.json() as Promise<{ user: string; admin: boolean } | null>;
}

export function getDiscordLoginUrl() {
    return `${BASE}/auth.php?discord=true`;
}

export async function loginWithToken(token: string) {
    const res = await get(
        `${BASE}/auth.php?token=${encodeURIComponent(token)}`,
    );
    return res;
}

// ─── Characters ──────────────────────────────────────────────────────────────

export async function getMyCharacters() {
    const res = await get(`${BASE}/data.php?action=my_characters`);
    return res.json();
}

export async function getCharacters() {
    const res = await get(`${BASE}/data.php?action=characters`);
    return res.json();
}

export async function getCharacter(id: number) {
    const res = await get(`${BASE}/data.php?action=character&id=${id}`);
    return res.json();
}

export async function getAltsForCharacter(id: number) {
    const res = await get(`${BASE}/data.php?action=alts&id=${id}`);
    return res.json();
}

export async function addOrUpdateCharacter(data: Record<string, string>) {
    return postForm(`${BASE}/characters.php?action=save`, data);
}

export async function hideCharacter(id: number) {
    return get(`${BASE}/characters.php?action=hide&id=${id}`);
}

export async function updateCharacterFromBNet(id: number) {
    const res = await get(
        `${BASE}/bnet.php?action=update_character&id=${id}&ajax=true`,
    );
    return res.json();
}

// ─── Raids ───────────────────────────────────────────────────────────────────

export async function getRaids() {
    const res = await get(`${BASE}/data.php?action=raids`);
    return res.json();
}

export async function getRaid(id: number) {
    const res = await get(`${BASE}/data.php?action=raid&id=${id}`);
    return res.json();
}

export async function addOrUpdateRaid(data: Record<string, string>) {
    return postForm(`${BASE}/raids.php?action=save`, data);
}

export async function addAllRaiders(raidId: number) {
    return get(`${BASE}/raids.php?action=add_all_raiders&raidId=${raidId}`);
}

export async function removeAttendeesWithNoBosses(raidId: number) {
    return get(`${BASE}/raids.php?action=remove_zero_bosses&raidId=${raidId}`);
}

export async function setAllPaid(raidId: number) {
    const res = await get(
        `${BASE}/raids.php?action=set_all_paid&raidId=${raidId}`,
    );
    return res.json();
}

// ─── Attendance ──────────────────────────────────────────────────────────────

export async function getAttendanceForRaid(raidId: number) {
    const res = await get(
        `${BASE}/data.php?action=attendance_for_raid&raidId=${raidId}`,
    );
    return res.json();
}

export async function getAttendanceForCharacter(characterId: number) {
    const res = await get(
        `${BASE}/data.php?action=attendance_for_character&characterId=${characterId}`,
    );
    return res.json();
}

export async function addAttendance(data: Record<string, string>) {
    return postForm(`${BASE}/attendance.php?action=add`, data);
}

export async function updateAttendance(data: Record<string, string>) {
    const res = await postForm(`${BASE}/attendance.php?action=update`, data);
    return res.json();
}

export async function deleteAttendance(characterId: number, raidId: number) {
    return get(
        `${BASE}/attendance.php?action=delete&characterId=${characterId}&raidId=${raidId}`,
    );
}

// ─── Applications ────────────────────────────────────────────────────────────

export async function getApps() {
    const res = await get(`${BASE}/data.php?action=apps`);
    return res.json();
}

export async function getApp(id: number) {
    const res = await get(`${BASE}/data.php?action=app&id=${id}`);
    return res.json();
}

export async function processApplication(data: Record<string, string>) {
    return postForm(`${BASE}/applications.php`, data);
}

// ─── Battle.net ──────────────────────────────────────────────────────────────

export async function getBNetTokenStatus() {
    const res = await get(`${BASE}/data.php?action=bnet_status`);
    return res.json();
}

export function getBNetTokenUrl() {
    return `${BASE}/bnet.php?action=token&return=/bnet`;
}

export async function updateAllCharactersFromBNet(ids: number[]) {
    const results: {
        id: number;
        success: boolean;
        ilvl?: number;
        error?: string;
    }[] = [];
    for (const id of ids) {
        try {
            const res = await updateCharacterFromBNet(id);
            results.push({ id, success: true, ilvl: res.ilvl });
        } catch (e) {
            results.push({ id, success: false, error: String(e) });
        }
        // 1 second delay between requests to avoid rate limiting
        await new Promise((r) => setTimeout(r, 1000));
    }
    return results;
}

// ─── Log ─────────────────────────────────────────────────────────────────────

export async function getLog() {
    const res = await get(`${BASE}/data.php?action=log`);
    return res.json();
}
