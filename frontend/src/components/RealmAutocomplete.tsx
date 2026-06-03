import { Autocomplete, TextField } from "@mui/material";
import { realms, type Realm } from "../data/realms";

interface RealmAutocompleteProps {
    value: string;
    onChange: (value: string) => void;
    error?: boolean;
    helperText?: string;
}

export default function RealmAutocomplete({
    value,
    onChange,
    error,
    helperText,
}: RealmAutocompleteProps) {
    const selected = realms.find((r) => r.value === value) || null;

    return (
        <Autocomplete
            options={realms}
            getOptionLabel={(option: string | Realm) =>
                typeof option === "string" ? option : option.label
            }
            value={selected}
            onChange={(_e, newValue) => {
                if (typeof newValue === "string") {
                    onChange(newValue);
                } else {
                    onChange(newValue ? newValue.value : "");
                }
            }}
            freeSolo
            onInputChange={(_e, inputValue, reason) => {
                if (reason === "input") {
                    const match = realms.find(
                        (r) =>
                            r.label.toLowerCase() ===
                                inputValue.toLowerCase() ||
                            r.value === inputValue.toLowerCase(),
                    );
                    onChange(match ? match.value : inputValue.toLowerCase());
                }
            }}
            renderInput={(params) => (
                <TextField
                    {...params}
                    label="Server"
                    placeholder="Realm (e.g. stormscale)"
                    required
                    error={error}
                    helperText={helperText}
                />
            )}
        />
    );
}
