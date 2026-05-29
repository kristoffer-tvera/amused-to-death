import { useState, useEffect } from "react";
import { useLocation } from "wouter";
import {
    Box,
    Stepper,
    Step,
    StepLabel,
    MobileStepper,
    Button,
    TextField,
    Typography,
    Card,
    CardContent,
    Alert,
    Fade,
    Divider,
    useMediaQuery,
    useTheme,
    InputAdornment,
    Paper,
} from "@mui/material";
import KeyboardArrowLeft from "@mui/icons-material/KeyboardArrowLeft";
import KeyboardArrowRight from "@mui/icons-material/KeyboardArrowRight";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import PersonIcon from "@mui/icons-material/Person";
import LinkIcon from "@mui/icons-material/Link";
import InfoIcon from "@mui/icons-material/Info";
import GroupIcon from "@mui/icons-material/Group";
import PreviewIcon from "@mui/icons-material/Preview";
import RealmAutocomplete from "../components/RealmAutocomplete";
import { processApplication } from "../api/endpoints";

const STORAGE_KEY = "a2d_application_draft";

const steps = [
    { label: "Character Info", icon: <PersonIcon /> },
    { label: "Contact & Links", icon: <LinkIcon /> },
    { label: "About You", icon: <InfoIcon /> },
    { label: "Alts & Extras", icon: <GroupIcon /> },
    { label: "Review & Submit", icon: <PreviewIcon /> },
];

interface FormData {
    name: string;
    server: string;
    spec: string;
    btag: string;
    ui: string;
    reason: string;
    history: string;
    alts: string;
}

const emptyForm: FormData = {
    name: "",
    server: "",
    spec: "",
    btag: "",
    ui: "",
    reason: "",
    history: "",
    alts: "",
};

function loadDraft(): FormData {
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) return { ...emptyForm, ...JSON.parse(saved) };
    } catch {
        /* ignore */
    }
    return emptyForm;
}

function isImageUrl(url: string): boolean {
    return /\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i.test(url);
}

export default function Apply() {
    const [activeStep, setActiveStep] = useState(0);
    const [form, setForm] = useState<FormData>(loadDraft);
    const [errors, setErrors] = useState<
        Partial<Record<keyof FormData, string>>
    >({});
    const [submitted, setSubmitted] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [submitError, setSubmitError] = useState("");
    const [appLink, setAppLink] = useState("");
    const [pepe, setPepe] = useState("true");
    const [, navigate] = useLocation();
    const theme = useTheme();
    const isMobile = useMediaQuery(theme.breakpoints.down("sm"));

    // Anti-bot: set pepe after 6 seconds
    useEffect(() => {
        const timer = setTimeout(() => setPepe("meme"), 6000);
        return () => clearTimeout(timer);
    }, []);

    // Save draft to localStorage
    useEffect(() => {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(form));
    }, [form]);

    const updateField = (field: keyof FormData, value: string) => {
        setForm((prev) => ({ ...prev, [field]: value }));
        if (errors[field]) {
            setErrors((prev) => ({ ...prev, [field]: undefined }));
        }
    };

    const validateStep = (step: number): boolean => {
        const newErrors: Partial<Record<keyof FormData, string>> = {};

        switch (step) {
            case 0:
                if (!form.name.trim())
                    newErrors.name = "Character name is required";
                if (!form.server.trim())
                    newErrors.server = "Server is required";
                if (!form.spec.trim())
                    newErrors.spec = "Primary spec is required";
                break;
            case 1:
                if (!form.btag.trim())
                    newErrors.btag = "Discord tag is required";
                if (!form.ui.trim())
                    newErrors.ui = "Raid UI screenshot URL is required";
                break;
            case 2:
                if (!form.reason.trim())
                    newErrors.reason = "Please tell us why you want to join";
                if (!form.history.trim())
                    newErrors.history = "Please share your guild history";
                break;
            case 3:
                // Alts is optional-ish but the original form requires it
                if (!form.alts.trim())
                    newErrors.alts = 'Please list your alts (or write "none")';
                break;
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleNext = () => {
        if (validateStep(activeStep)) {
            setActiveStep((prev) => prev + 1);
        }
    };

    const handleBack = () => {
        setActiveStep((prev) => prev - 1);
    };

    const handleSubmit = async () => {
        if (pepe !== "meme") {
            setSubmitError("Please wait a moment before submitting.");
            return;
        }

        setSubmitting(true);
        setSubmitError("");

        try {
            const res = await processApplication({
                name: form.name,
                server: form.server,
                spec: form.spec,
                btag: form.btag,
                ui: form.ui,
                reason: form.reason,
                history: form.history,
                alts: form.alts,
                pepe,
                return: "/apply",
            });

            if (res.ok || res.redirected) {
                setSubmitted(true);
                localStorage.removeItem(STORAGE_KEY);
                // Try to extract the app link from the redirect URL
                if (res.redirected && res.url) {
                    setAppLink(res.url.replace(/.*\/app\//, "/app/"));
                }
            } else {
                setSubmitError(
                    "Failed to submit application. Please try again.",
                );
            }
        } catch {
            setSubmitError("Network error. Please try again.");
        } finally {
            setSubmitting(false);
        }
    };

    if (submitted) {
        return (
            <Fade in>
                <Card sx={{ maxWidth: 600, mx: "auto", mt: 4 }}>
                    <CardContent sx={{ textAlign: "center", py: 6 }}>
                        <CheckCircleIcon
                            sx={{ fontSize: 80, color: "success.main", mb: 2 }}
                        />
                        <Typography variant="h4" gutterBottom>
                            Application Submitted!
                        </Typography>
                        <Typography
                            variant="body1"
                            color="text.secondary"
                            sx={{ mb: 3 }}
                        >
                            Thank you for applying to Amused to Death. We'll
                            review your application and get back to you on
                            Discord.
                        </Typography>
                        {appLink && (
                            <Button
                                variant="outlined"
                                onClick={() => navigate(appLink)}
                            >
                                View / Update Your Application
                            </Button>
                        )}
                    </CardContent>
                </Card>
            </Fade>
        );
    }

    const renderStepContent = (step: number) => {
        switch (step) {
            case 0:
                return (
                    <Fade in key="step0">
                        <Box
                            sx={{
                                display: "flex",
                                flexDirection: "column",
                                gap: 3,
                            }}
                        >
                            <Typography variant="h6">
                                Tell us about your character
                            </Typography>
                            <TextField
                                label="Character Name"
                                value={form.name}
                                onChange={(e) =>
                                    updateField("name", e.target.value)
                                }
                                error={!!errors.name}
                                helperText={
                                    errors.name || "Your main character name"
                                }
                                required
                                autoFocus
                            />
                            <RealmAutocomplete
                                value={form.server}
                                onChange={(v) => updateField("server", v)}
                                error={!!errors.server}
                                helperText={errors.server}
                            />
                            <TextField
                                label="Primary Spec"
                                value={form.spec}
                                onChange={(e) =>
                                    updateField("spec", e.target.value)
                                }
                                error={!!errors.spec}
                                helperText={
                                    errors.spec ||
                                    "e.g. Restoration Druid, Arms Warrior"
                                }
                                required
                            />
                        </Box>
                    </Fade>
                );

            case 1:
                return (
                    <Fade in key="step1">
                        <Box
                            sx={{
                                display: "flex",
                                flexDirection: "column",
                                gap: 3,
                            }}
                        >
                            <Typography variant="h6">
                                How can we reach you?
                            </Typography>
                            <TextField
                                label="Discord Tag"
                                value={form.btag}
                                onChange={(e) =>
                                    updateField("btag", e.target.value)
                                }
                                error={!!errors.btag}
                                helperText={
                                    errors.btag ||
                                    "e.g. username#1234 or just username"
                                }
                                required
                                slotProps={{
                                    input: {
                                        startAdornment: (
                                            <InputAdornment position="start">
                                                #
                                            </InputAdornment>
                                        ),
                                    },
                                }}
                            />
                            <TextField
                                label="Raid UI Screenshot"
                                value={form.ui}
                                onChange={(e) =>
                                    updateField("ui", e.target.value)
                                }
                                error={!!errors.ui}
                                helperText={
                                    errors.ui ||
                                    "Link to a screenshot of your raid UI (imgur, etc.)"
                                }
                                required
                                placeholder="https://i.imgur.com/..."
                            />
                            {form.ui && isImageUrl(form.ui) && (
                                <Paper
                                    variant="outlined"
                                    sx={{
                                        p: 1,
                                        textAlign: "center",
                                        bgcolor: "background.default",
                                    }}
                                >
                                    <Typography
                                        variant="caption"
                                        color="text.secondary"
                                        sx={{ display: "block" }}
                                        gutterBottom
                                    >
                                        Preview
                                    </Typography>
                                    <Box
                                        component="img"
                                        src={form.ui}
                                        alt="UI Preview"
                                        sx={{
                                            maxWidth: "100%",
                                            maxHeight: 200,
                                            borderRadius: 1,
                                            objectFit: "contain",
                                        }}
                                        onError={(e) => {
                                            (
                                                e.target as HTMLImageElement
                                            ).style.display = "none";
                                        }}
                                    />
                                </Paper>
                            )}
                        </Box>
                    </Fade>
                );

            case 2:
                return (
                    <Fade in key="step2">
                        <Box
                            sx={{
                                display: "flex",
                                flexDirection: "column",
                                gap: 3,
                            }}
                        >
                            <Typography variant="h6">
                                Tell us about yourself
                            </Typography>
                            <TextField
                                label="Why do you want to join Amused to Death?"
                                value={form.reason}
                                onChange={(e) =>
                                    updateField("reason", e.target.value)
                                }
                                error={!!errors.reason}
                                helperText={
                                    errors.reason ||
                                    `${form.reason.length}/1000 — What are you looking for in a guild? What can you bring?`
                                }
                                required
                                multiline
                                rows={5}
                                slotProps={{ htmlInput: { maxLength: 1000 } }}
                            />
                            <TextField
                                label="Guild History & References"
                                value={form.history}
                                onChange={(e) =>
                                    updateField("history", e.target.value)
                                }
                                error={!!errors.history}
                                helperText={
                                    errors.history ||
                                    `${form.history.length}/1000 — Previous guilds, raid experience, and any references`
                                }
                                required
                                multiline
                                rows={5}
                                slotProps={{ htmlInput: { maxLength: 1000 } }}
                            />
                        </Box>
                    </Fade>
                );

            case 3:
                return (
                    <Fade in key="step3">
                        <Box
                            sx={{
                                display: "flex",
                                flexDirection: "column",
                                gap: 3,
                            }}
                        >
                            <Typography variant="h6">
                                Alts & Additional Info
                            </Typography>
                            <TextField
                                label="Alt Characters"
                                value={form.alts}
                                onChange={(e) =>
                                    updateField("alts", e.target.value)
                                }
                                error={!!errors.alts}
                                helperText={
                                    errors.alts ||
                                    `${form.alts.length}/500 — List your alts, their specs, and any relevant info`
                                }
                                required
                                multiline
                                rows={5}
                                slotProps={{ htmlInput: { maxLength: 500 } }}
                                placeholder='e.g. "Altname - Frost Mage - Stormscale" or "none"'
                            />
                        </Box>
                    </Fade>
                );

            case 4:
                return (
                    <Fade in key="step4">
                        <Box
                            sx={{
                                display: "flex",
                                flexDirection: "column",
                                gap: 2,
                            }}
                        >
                            <Typography variant="h6">
                                Review Your Application
                            </Typography>
                            <Typography variant="body2" color="text.secondary">
                                Please double-check everything before
                                submitting.
                            </Typography>

                            <Divider sx={{ my: 1 }} />

                            <ReviewSection
                                title="Character"
                                onEdit={() => setActiveStep(0)}
                                items={[
                                    { label: "Name", value: form.name },
                                    { label: "Server", value: form.server },
                                    { label: "Spec", value: form.spec },
                                ]}
                            />

                            <Divider />

                            <ReviewSection
                                title="Contact & Links"
                                onEdit={() => setActiveStep(1)}
                                items={[
                                    { label: "Discord", value: form.btag },
                                    { label: "UI Screenshot", value: form.ui },
                                ]}
                            />

                            <Divider />

                            <ReviewSection
                                title="About You"
                                onEdit={() => setActiveStep(2)}
                                items={[
                                    { label: "Reason", value: form.reason },
                                    { label: "History", value: form.history },
                                ]}
                            />

                            <Divider />

                            <ReviewSection
                                title="Alts"
                                onEdit={() => setActiveStep(3)}
                                items={[{ label: "Alts", value: form.alts }]}
                            />

                            {submitError && (
                                <Alert severity="error" sx={{ mt: 2 }}>
                                    {submitError}
                                </Alert>
                            )}
                        </Box>
                    </Fade>
                );

            default:
                return null;
        }
    };

    return (
        <Card sx={{ maxWidth: 720, mx: "auto", width: "100%" }}>
            <CardContent sx={{ p: { xs: 2, sm: 4 } }}>
                <Typography
                    variant="h4"
                    gutterBottom
                    align="center"
                    sx={{ mb: 3 }}
                >
                    Apply to Amused to Death
                </Typography>

                {!isMobile ? (
                    <Stepper
                        activeStep={activeStep}
                        alternativeLabel
                        sx={{ mb: 4 }}
                    >
                        {steps.map((s) => (
                            <Step key={s.label}>
                                <StepLabel>{s.label}</StepLabel>
                            </Step>
                        ))}
                    </Stepper>
                ) : (
                    <MobileStepper
                        variant="dots"
                        steps={steps.length}
                        position="static"
                        activeStep={activeStep}
                        sx={{
                            mb: 3,
                            bgcolor: "transparent",
                            justifyContent: "center",
                        }}
                        nextButton={<span />}
                        backButton={<span />}
                    />
                )}

                <Box sx={{ minHeight: 300 }}>
                    {renderStepContent(activeStep)}
                </Box>

                <Box
                    sx={{
                        display: "flex",
                        justifyContent: "space-between",
                        mt: 4,
                    }}
                >
                    <Button
                        disabled={activeStep === 0}
                        onClick={handleBack}
                        startIcon={<KeyboardArrowLeft />}
                    >
                        Back
                    </Button>

                    {activeStep < steps.length - 1 ? (
                        <Button
                            variant="contained"
                            onClick={handleNext}
                            endIcon={<KeyboardArrowRight />}
                        >
                            Next
                        </Button>
                    ) : (
                        <Button
                            variant="contained"
                            color="success"
                            onClick={handleSubmit}
                            disabled={submitting}
                        >
                            {submitting
                                ? "Submitting..."
                                : "Submit Application"}
                        </Button>
                    )}
                </Box>
            </CardContent>
        </Card>
    );
}

function ReviewSection({
    title,
    items,
    onEdit,
}: {
    title: string;
    items: { label: string; value: string }[];
    onEdit: () => void;
}) {
    return (
        <Box>
            <Box
                sx={{
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "space-between",
                    mb: 1,
                }}
            >
                <Typography variant="subtitle1" sx={{ fontWeight: 600 }}>
                    {title}
                </Typography>
                <Button size="small" onClick={onEdit}>
                    Edit
                </Button>
            </Box>
            {items.map((item) => (
                <Box key={item.label} sx={{ mb: 1 }}>
                    <Typography variant="caption" color="text.secondary">
                        {item.label}
                    </Typography>
                    <Typography
                        variant="body2"
                        sx={{
                            whiteSpace: "pre-wrap",
                            wordBreak: "break-word",
                            maxHeight: 120,
                            overflow: "auto",
                        }}
                    >
                        {item.value || "—"}
                    </Typography>
                </Box>
            ))}
        </Box>
    );
}
