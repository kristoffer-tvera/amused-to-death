import {
    Alert,
    Box,
    Card,
    CardContent,
    CircularProgress,
    Divider,
    Link,
    Typography,
} from "@mui/material";
import { useEffect, useState } from "react";
import { useRoute } from "wouter";
import { getAppVersion, type ApplicationVersion } from "../api/endpoints";
import { useAuth } from "../context/AuthContext";

// The editable fields, in display order, with human labels. version_no and
// version_date are metadata rather than content, so they are shown separately.
const FIELDS: { key: keyof ApplicationVersion; label: string }[] = [
    { key: "name", label: "Name" },
    { key: "server", label: "Server" },
    { key: "btag", label: "Discord" },
    { key: "spec", label: "Spec" },
    { key: "ui", label: "UI Screenshot URL" },
    { key: "reason", label: "Reason for applying" },
    { key: "history", label: "Guild History" },
    { key: "alts", label: "Alts" },
];

function norm(value: ApplicationVersion[keyof ApplicationVersion]): string {
    return value == null ? "" : String(value);
}

export default function AppDiff() {
    const [, params] = useRoute("/app/:id/diff");
    const id = params?.id;
    const searchParams = new URLSearchParams(window.location.search);
    const from = Number(searchParams.get("from"));
    const to = Number(searchParams.get("to"));
    const validRange =
        Number.isInteger(from) && from > 0 && Number.isInteger(to) && to > 0;

    const { isAdmin, loading: authLoading } = useAuth();

    const [fromVersion, setFromVersion] = useState<ApplicationVersion | null>(
        null,
    );
    const [toVersion, setToVersion] = useState<ApplicationVersion | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!id || !validRange) {
            setLoading(false);
            return;
        }
        setLoading(true);
        // Fetch both snapshots in parallel; the diff is computed client-side, as
        // the backend intentionally exposes only per-version reads (no /diff).
        Promise.all([
            getAppVersion(Number(id), from),
            getAppVersion(Number(id), to),
        ])
            .then(([a, b]) => {
                setFromVersion(a);
                setToVersion(b);
            })
            .finally(() => setLoading(false));
    }, [id, from, to, validRange]);

    if (!validRange) {
        return (
            <Alert severity="error">
                Invalid diff range. Expected ?from= and ?to= version numbers.
            </Alert>
        );
    }

    // The version endpoints are reviewer-only; make the gate explicit rather
    // than rendering an empty diff from failed fetches.
    if (!authLoading && !isAdmin) {
        return (
            <Alert severity="warning">
                Revision history is available to logged-in members only.
            </Alert>
        );
    }

    if (loading || authLoading) {
        return (
            <Box sx={{ display: "flex", justifyContent: "center", py: 8 }}>
                <CircularProgress />
            </Box>
        );
    }

    if (!fromVersion || !toVersion) {
        return (
            <Alert severity="error">
                One or both versions could not be found (from {from}, to {to}).
            </Alert>
        );
    }

    const changedCount = FIELDS.filter(
        (f) => norm(fromVersion[f.key]) !== norm(toVersion[f.key]),
    ).length;

    return (
        <Card sx={{ maxWidth: 900, mx: "auto", width: "100%" }}>
            <CardContent>
                <Typography variant="h5" sx={{ mb: 0.5 }}>
                    Comparing version {from} → version {to}
                </Typography>
                <Typography
                    variant="body2"
                    color="text.secondary"
                    sx={{ mb: 2 }}
                >
                    {changedCount === 0
                        ? "No field differences between these versions."
                        : `${changedCount} field${changedCount === 1 ? "" : "s"} changed.`}
                </Typography>
                <Divider sx={{ mb: 2 }} />

                {FIELDS.map((f) => {
                    const before = norm(fromVersion[f.key]);
                    const after = norm(toVersion[f.key]);
                    const changed = before !== after;
                    return (
                        <Box key={f.key as string} sx={{ mb: 2.5 }}>
                            <Typography
                                variant="caption"
                                color="text.secondary"
                                sx={{
                                    fontWeight: changed ? 700 : 400,
                                }}
                            >
                                {f.label}
                                {changed ? " (changed)" : ""}
                            </Typography>
                            <Box
                                sx={{
                                    display: "grid",
                                    gridTemplateColumns: {
                                        xs: "1fr",
                                        sm: "1fr 1fr",
                                    },
                                    gap: 1,
                                    mt: 0.5,
                                }}
                            >
                                <FieldPane
                                    heading={`v${from}`}
                                    value={before}
                                    tone={changed ? "removed" : "neutral"}
                                />
                                <FieldPane
                                    heading={`v${to}`}
                                    value={after}
                                    tone={changed ? "added" : "neutral"}
                                />
                            </Box>
                        </Box>
                    );
                })}

                <Divider sx={{ my: 2 }} />
                <Box sx={{ display: "flex", gap: 2, flexWrap: "wrap" }}>
                    <Link href={`/app/${id}?v=${from}`}>View v{from}</Link>
                    <Link href={`/app/${id}?v=${to}`}>View v{to}</Link>
                    <Link href={`/app/${id}`}>View latest</Link>
                </Box>
            </CardContent>
        </Card>
    );
}

function FieldPane({
    heading,
    value,
    tone,
}: {
    heading: string;
    value: string;
    tone: "added" | "removed" | "neutral";
}) {
    const bg =
        tone === "added"
            ? "success.light"
            : tone === "removed"
              ? "error.light"
              : "action.hover";
    return (
        <Box
            sx={{
                bgcolor: bg,
                borderRadius: 1,
                p: 1,
                opacity: tone === "neutral" ? 0.7 : 1,
            }}
        >
            <Typography
                variant="caption"
                sx={{ display: "block", fontWeight: 600, mb: 0.5 }}
            >
                {heading}
            </Typography>
            <Typography
                variant="body2"
                sx={{ whiteSpace: "pre-wrap", wordBreak: "break-word" }}
            >
                {value || <em>(empty)</em>}
            </Typography>
        </Box>
    );
}
