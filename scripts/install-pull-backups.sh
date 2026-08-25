#!/usr/bin/env bash
#
# Teach this Mac to fetch the library's backups every day, on its own.
#
# Run once:
#
#     ./scripts/install-pull-backups.sh
#
# It asks launchd to run pull-backups.sh at 14:00 daily. If the Mac is asleep
# at that hour, launchd runs it at the next opportunity instead of skipping the
# day, which is what makes this dependable on a laptop.
#
# To stop it again:
#
#     launchctl unload ~/Library/LaunchAgents/krd.uor.library.backup-pull.plist

set -euo pipefail

LABEL="krd.uor.library.backup-pull"
PLIST="$HOME/Library/LaunchAgents/$LABEL.plist"
SCRIPT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/pull-backups.sh"
LOG_DIR="$HOME/Library/Logs"

if [ ! -x "$SCRIPT" ]; then
    chmod +x "$SCRIPT"
fi

mkdir -p "$(dirname "$PLIST")" "$LOG_DIR"

cat > "$PLIST" <<PLIST_END
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN"
        "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>Label</key>
    <string>$LABEL</string>

    <key>ProgramArguments</key>
    <array>
        <string>/bin/bash</string>
        <string>$SCRIPT</string>
    </array>

    <key>StartCalendarInterval</key>
    <dict>
        <key>Hour</key><integer>14</integer>
        <key>Minute</key><integer>0</integer>
    </dict>

    <key>StandardOutPath</key>
    <string>$LOG_DIR/$LABEL.log</string>
    <key>StandardErrorPath</key>
    <string>$LOG_DIR/$LABEL.log</string>

    <key>RunAtLoad</key>
    <false/>
</dict>
</plist>
PLIST_END

launchctl unload "$PLIST" 2>/dev/null || true
launchctl load "$PLIST"

echo "Installed. It will run daily at 14:00."
echo "Log: $LOG_DIR/$LABEL.log"
echo
echo "To try it now:  bash $SCRIPT"
