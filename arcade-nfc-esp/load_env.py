Import("env")

import os
import shlex
from pathlib import Path


def read_env_value(line):
    key, separator, value = line.partition("=")
    if not separator:
        return None, None

    key = key.strip()
    value = value.strip()
    if not key or key.startswith("#"):
        return None, None

    if value.startswith(("'", '"')):
        parsed = shlex.split(value, comments=False, posix=True)
        value = parsed[0] if parsed else ""
    else:
        value = value.split(" #", 1)[0].rstrip()

    return key, value


env_file = Path(env["PROJECT_DIR"]) / ".env"
file_values = {}
if env_file.is_file():
    for line in env_file.read_text().splitlines():
        key, value = read_env_value(line)
        if key:
            file_values[key] = value

required_values = ("WIFI_SSID", "WIFI_PASS", "API_URL", "MACHINE_TOKEN")
missing_values = [
    key for key in required_values
    if not os.environ.get(key) and not file_values.get(key)
]
if missing_values:
    raise RuntimeError(
        "Missing firmware configuration values: "
        + ", ".join(missing_values)
        + ". Set them in esp32_nfc_display/.env."
    )

env.Append(
    CPPDEFINES=[
        (key, env.StringifyMacro(os.environ.get(key) or file_values[key]))
        for key in required_values
    ]
)
