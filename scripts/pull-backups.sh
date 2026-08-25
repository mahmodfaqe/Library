#!/usr/bin/env bash
#
# Copy the library's backups off the server and onto this machine.
#
# A backup that lives on the same disk as the database is not a backup: it is a
# second copy of a file that will be lost in the same instant. This is the
# copy that survives the server.
#
# Run it by hand:
#
#     ./scripts/pull-backups.sh
#
# or leave it to launchd, which is what install-pull-backups.sh sets up.
#
# It only ever reads from the server. Nothing here can change or delete
# anything on it.

set -euo pipefail

# --- what to copy, and to where -------------------------------------------

SERVER="${LIBRARY_SERVER:-root@dormitory-uor.online}"
REMOTE="${LIBRARY_REMOTE_BACKUPS:-/root/library/storage/backups/}"
LOCAL="${LIBRARY_LOCAL_BACKUPS:-$HOME/Library-backups}"

# A second home, if one is there. Anything that syncs to the cloud will do:
# Google Drive, Dropbox, iCloud. Leave it empty to keep the copy local only.
MIRROR="${LIBRARY_BACKUP_MIRROR:-}"

# How many days of copies to keep on this machine.
KEEP_DAYS="${LIBRARY_BACKUP_KEEP_DAYS:-60}"

say() { printf '%s  %s\n' "$(date '+%H:%M:%S')" "$*"; }

# --- the copy --------------------------------------------------------------

mkdir -p "$LOCAL"

say "Pulling from $SERVER"

# --archive keeps timestamps, which is how the age of a backup is judged.
# --ignore-existing so a file already here is never fetched twice.
rsync --archive --compress --ignore-existing --human-readable \
      --timeout=120 \
      -e "ssh -o BatchMode=yes -o ConnectTimeout=20" \
      "$SERVER:$REMOTE" "$LOCAL/"

count=$(find "$LOCAL" -name 'database-*' -type f | wc -l | tr -d ' ')
newest=$(find "$LOCAL" -name 'database-*' -type f -print0 \
         | xargs -0 ls -t 2>/dev/null | head -1 || true)

if [ -z "$newest" ]; then
    say "NOTHING WAS COPIED — there are no backups here."
    exit 1
fi

age_hours=$(( ( $(date +%s) - $(stat -f %m "$newest") ) / 3600 ))

say "$count copies here; the newest is ${age_hours}h old"

# A stale newest copy means the server stopped making them, or this stopped
# fetching them. Either way it is worth knowing before it is needed.
if [ "$age_hours" -gt 48 ]; then
    say "WARNING: the newest backup is more than two days old."
fi

# --- the second home -------------------------------------------------------

if [ -n "$MIRROR" ]; then
    if [ -d "$(dirname "$MIRROR")" ]; then
        mkdir -p "$MIRROR"
        rsync --archive --ignore-existing "$LOCAL/" "$MIRROR/"
        say "Mirrored to $MIRROR"
    else
        say "WARNING: $MIRROR is not reachable — the mirror was skipped."
    fi
fi

# --- old copies ------------------------------------------------------------

removed=$(find "$LOCAL" -name 'database-*' -type f -mtime +"$KEEP_DAYS" -print -delete | wc -l | tr -d ' ')

if [ "$removed" -gt 0 ]; then
    say "Removed $removed copies older than $KEEP_DAYS days"
fi

say "Done."
