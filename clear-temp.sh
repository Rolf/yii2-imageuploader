#!/bin/sh
if [ -d ../static/web/uploads/temp ]; then
    find ../static/web/uploads/temp -type f -mtime +3 | xargs rm
fi
