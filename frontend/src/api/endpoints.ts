// API client for the .NET 10 backend.
//
// The backend is a minimal API mounted under /api, replacing the old PHP
// endpoints. Requests send credentials so the session cookie (a2d_session)
// flows on same-origin calls. Reads are JSON GETs; writes are JSON bodies.
const BASE = "/api";

// Returns `any` to match the loose contract the pages relied on with the old
// `res.json()` calls (the pages store results in `any`/`any[]` state).
// eslint-disable-next-line @typescript-eslint/no-explicit-any
async function getJson(url: string): Promise<any> {
    const res = await fetch(`${BASE}${url}`, { credentials: "include" });
    return res.json();
}

async function get(url: string): Promise<Response> {
    return fetch(`${BASE}${url}`, { credentials: "include" });
}

async function postJson(url: string, body: unknown): Promise<Response> {
    return fetch(`${BASE}${url}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body ?? {}),
        credentials: "include",
    });
}

async function putJson(url: string, body: unknown): Promise<Response> {
    return fetch(`${BASE}${url}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body ?? {}),
        credentials: "include",
    });
}

async function del(url: string): Promise<Response> {
    return fetch(`${BASE}${url}`, { method: "DELETE", credentials: "include" });
}

// The pages pass string-valued form maps (a legacy of the form-encoded PHP API).
// Normalise those into the JSON types the .NET endpoints expect.
function num(v: string | number | undefined, fallback = 0): number {
    if (v === undefined || v === "") return fallback;
    const n = Number(v);
    return Number.isNaN(n) ? fallback : n;
}

function bool(v: string | number | boolean | undefined): boolean {
    return v === true || v === "1" || v === 1;
}

// ─── Auth ────────────────────────────────────────────────────────────────────

export async function getMe() {
    const res = await get(`/auth/me`);
    if (!res.ok) return null;
    return res.json() as Promise<{ user: string; admin: boolean } | null>;
}

export function getBattleNetLoginUrl() {
    return `${BASE}/auth/bnet/login`;
}

// Forces the character picker even for an account that already has characters
// (used by the Home "Re-import" button to add new characters / change main).
export function getBattleNetReimportUrl() {
    return `${BASE}/auth/bnet/login?reimport=true`;
}

export async function logout() {
    return postJson(`/auth/logout`, {});
}

// ─── Battle.net login / character import ───────────────────────────────────────

export interface BNetCharacter {
    name: string;
    realm: string;
    realm_slug: string;
    level: number;
    class_id: number;
    guild: string | null;
    in_guild: boolean;
}

export interface BNetCharacterList {
    battle_tag: string;
    max_level: number;
    characters: BNetCharacter[];
    has_guild_character: boolean;
}

// Reads the max-level characters for the login in progress (pending-login cookie).
export async function getBNetLoginCharacters(): Promise<BNetCharacterList | null> {
    const res = await get(`/auth/bnet/characters`);
    if (!res.ok) return null;
    return res.json() as Promise<BNetCharacterList>;
}

// Imports the chosen characters and finishes login. Picks are {name, realm};
// mainIndex points at which pick is the main.
export async function importBNetCharacters(
    picks: { name: string; realm: string }[],
    mainIndex: number,
): Promise<Response> {
    return postJson(`/auth/bnet/import`, {
        picks,
        main_index: mainIndex,
    });
}

// ─── Characters ──────────────────────────────────────────────────────────────

export async function getMyCharacters() {
    return getJson(`/characters/mine`);
}

export async function getCharacters() {
    return getJson(`/characters`);
}

export async function getCharacter(id: number) {
    return getJson(`/characters/${id}`);
}

export async function getAltsForCharacter(id: number) {
    return getJson(`/characters/${id}/alts`);
}

// Set a character's tank/heal/dps roles — the only editable character data.
// Owners may set their own; admins any.
export async function setCharacterRoles(
    id: number,
    roles: { role_tank: boolean; role_heal: boolean; role_dps: boolean },
) {
    return postJson(`/characters/${id}/roles`, roles);
}

// Show or hide a character. Owners may toggle their own; admins any.
export async function setCharacterVisibility(id: number, hidden: boolean) {
    return postJson(`/characters/${id}/visibility`, { hidden });
}

export async function updateCharacterFromBNet(id: number) {
    const res = await postJson(`/characters/${id}/refresh-ilvl`, {});
    return res.json();
}

// ─── Raids ───────────────────────────────────────────────────────────────────

export async function getRaids() {
    return getJson(`/raids`);
}

export async function getRaid(id: number) {
    return getJson(`/raids/${id}`);
}

export async function addOrUpdateRaid(data: Record<string, string>) {
    const body = {
        id: num(data.id),
        name: data.name ?? "",
        gold: num(data.gold),
        paid: bool(data.paid),
        comment: data.comment ?? "",
    };
    return body.id > 0
        ? putJson(`/raids/${body.id}`, body)
        : postJson(`/raids`, body);
}

export async function addAllRaiders(raidId: number) {
    return postJson(`/raids/${raidId}/add-all-raiders`, {});
}

export async function removeAttendeesWithNoBosses(raidId: number) {
    return postJson(`/raids/${raidId}/remove-zero-bosses`, {});
}

export async function setAllPaid(raidId: number) {
    const res = await postJson(`/raids/${raidId}/set-all-paid`, {});
    return res.json();
}

// ─── Attendance ──────────────────────────────────────────────────────────────

export async function getAttendanceForRaid(raidId: number) {
    return getJson(`/raids/${raidId}/attendance`);
}

export async function getAttendanceForCharacter(characterId: number) {
    return getJson(`/characters/${characterId}/attendance`);
}

export async function addAttendance(data: Record<string, string>) {
    return postJson(`/attendance`, {
        character: num(data.character),
        raid: num(data.raid),
        bosses: num(data.bosses),
    });
}

export async function updateAttendance(data: Record<string, string>) {
    const res = await putJson(`/attendance`, {
        character_id: num(data.characterId),
        raid_id: num(data.raidId),
        bosses: num(data.bosses),
        paid: bool(data.paid),
    });
    return res.json();
}

export async function deleteAttendance(characterId: number, raidId: number) {
    return del(`/attendance?characterId=${characterId}&raidId=${raidId}`);
}

// ─── Applications ────────────────────────────────────────────────────────────

export async function getApps() {
    return getJson(`/applications`);
}

export async function getApp(id: number, auth?: string) {
    const query = auth ? `?auth=${encodeURIComponent(auth)}` : "";
    return getJson(`/applications/${id}${query}`);
}

// Submits/updates an application. The backend now returns JSON { id, auth }
// instead of a 302 redirect, so we synthesise a Response-like object whose
// `redirected`/`url` fields let the existing Apply/AppView pages keep working
// unchanged (they read res.ok / res.redirected / res.url).
export async function processApplication(data: Record<string, string>) {
    const res = await postJson(`/applications`, {
        id: num(data.id),
        auth: data.auth || undefined,
        name: data.name ?? "",
        server: data.server ?? "",
        btag: data.btag ?? "",
        spec: data.spec ?? "",
        ui: data.ui ?? "",
        reason: data.reason ?? "",
        history: data.history ?? "",
        alts: data.alts ?? "",
        pepe: data.pepe ?? "",
    });

    if (!res.ok) {
        return { ok: false, redirected: false, url: "" };
    }

    const result = (await res.json()) as { id?: number; auth?: string };
    const url =
        result.id && result.auth
            ? `${window.location.origin}/app/${result.id}?auth=${result.auth}`
            : "";

    return { ok: true, redirected: !!url, url };
}

// ─── Battle.net ──────────────────────────────────────────────────────────────
// The Blizzard token is acquired server-side on demand; there's no token UI.

// Hides characters no longer in the guild (admin, on-demand). Returns { hidden }.
export async function purgeNonGuildCharacters() {
    const res = await postJson(`/bnet/purge-non-guild`, {});
    return res.json() as Promise<
        { success: true; hidden: number } | { error: string }
    >;
}

// Refreshes every character's item level from Battle.net and hides duds (chars
// Blizzard returns nothing for) — but only if at least one refresh succeeded.
export async function refreshAllFromBNet() {
    const res = await postJson(`/bnet/refresh-all`, {});
    return res.json() as Promise<
        | {
              success: true;
              refreshed: number;
              failed: number;
              hidden: number;
              systemic_failure: boolean;
          }
        | { error: string }
    >;
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


